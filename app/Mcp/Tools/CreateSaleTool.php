<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use App\Tec\Actions\SaveSale;
use App\Models\Sma\Order\Payment;
use App\Models\Sma\Setting\Store;
use Illuminate\Support\Facades\DB;
use App\Models\Sma\People\Customer;
use App\Models\Sma\Product\Product;
use Illuminate\Support\Facades\URL;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[IsDestructive(true)]
class CreateSaleTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Create a new sales order for the authenticated user with pending payment.
        If the user doesn't have a customer account, you can create one by providing registration details.
        Validates stock availability and generates secure payment links for the customer to complete payment.
    MARKDOWN;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        // Require authentication for order creation
        if (! $request->user()) {
            return Response::error('Authentication required. Please provide a valid Sanctum token to create orders.');
        }

        try {
            $user = $request->user();
            $customer = null;
            $customerCreated = false;

            // Check if user has a customer account
            if ($user->customer_id) {
                $customer = Customer::find($user->customer_id);
            }

            // If no customer, create one
            if (! $customer) {
                $validated = $request->validate([
                    'customer_name'    => 'required|string|max:255',
                    'customer_email'   => 'nullable|email|max:255',
                    'customer_phone'   => 'nullable|string|max:50',
                    'customer_company' => 'nullable|string|max:255',
                ], [
                    'customer_name.required' => 'You must provide a customer name because this user does not have a linked customer account.',
                ]);

                $customer = Customer::create([
                    'name'            => $validated['customer_name'],
                    'company'         => $validated['customer_company'] ?? $validated['customer_name'],
                    'email'           => $validated['customer_email'] ?? $user->email,
                    'phone'           => $validated['customer_phone'] ?? $user->phone,
                    'opening_balance' => 0,
                    'user_id'         => $user->id,
                ]);

                // Link customer to user
                $user->customer_id = $customer->id;
                $user->save();

                // Assign Customer role if not already assigned
                if (! $user->hasRole('Customer')) {
                    $user->assignRole('Customer');
                }

                $customerCreated = true;
            }

            // Build coupon validation rule conditionally based on Shop module
            $couponRules = class_exists(\Modules\Shop\Models\ShopCoupon::class)
                ? 'nullable|string|exists:shop_coupons,code'
                : 'nullable|string';

            // Validate order data
            $validated = $request->validate([
                'details'              => 'nullable|string',
                'coupon_code'          => $couponRules,
                'items'                => 'required|array|min:1',
                'items.*.product_id'   => 'required_without:items.*.product_code|integer|exists:products,id',
                'items.*.product_code' => 'required_without:items.*.product_id|string|exists:products,code',
                'items.*.quantity'     => 'required|numeric|min:0.01',
                'items.*.comment'      => 'nullable|string|max:190',
            ], [
                'items.required'                        => 'You must provide at least one item. Each item needs a product_id or product_code and a quantity.',
                'items.*.product_id.required_without'   => 'Each item must have either a product_id or a product_code.',
                'items.*.product_code.required_without' => 'Each item must have either a product_id or a product_code.',
                'items.*.quantity.required'             => 'Each item must have a quantity greater than 0.',
                'items.*.quantity.min'                  => 'Item quantity must be at least 0.01.',
            ]);

            $validated['customer_id'] = $customer->id;
            $validated['date'] = now()->toDateString(); // Always use today
            $validated['store_id'] = get_settings('default_store') ?? session('selected_store_id', Store::first()?->id ?? 1);
            $validated['user_id'] = $user->id;
            // Auto-generate reference - never allow user-provided reference for security
            unset($validated['reference']);

            // Validate and process coupon if provided
            $coupon = null;
            $couponDiscount = 0;
            if (! empty($validated['coupon_code'])) {
                if (! class_exists(\Modules\Shop\Models\ShopCoupon::class)) {
                    return Response::error('Coupon codes are not supported. The Shop module is not installed.');
                }

                $coupon = \Modules\Shop\Models\ShopCoupon::where('code', $validated['coupon_code'])
                    ->where('active', 1)
                    ->first();

                if (! $coupon) {
                    return Response::error('Coupon code is not valid or has been deactivated.');
                }

                // Check if coupon has expired
                if ($coupon->expiry_date && now()->parse($coupon->expiry_date)->isPast()) {
                    return Response::error('Coupon has expired.');
                }

                // Check if coupon usage limit reached
                if ($coupon->allowed && $coupon->used >= $coupon->allowed) {
                    return Response::error('Coupon usage limit has been reached.');
                }

                $couponDiscount = $coupon->discount; // Percentage discount
            }

            // Process and validate items
            $processedItems = [];
            $totalAmount = 0;
            $stockIssues = [];

            foreach ($validated['items'] as $itemData) {
                // Find product by ID or code (bypass active global scope to give clear error messages)
                $query = Product::withoutGlobalScope('active')->with(['stocks' => function ($query) use ($validated) {
                    $query->where('store_id', $validated['store_id']);
                }]);

                if (isset($itemData['product_id'])) {
                    $product = $query->find($itemData['product_id']);
                } else {
                    $product = $query->where('code', $itemData['product_code'])->first();
                }

                if (! $product) {
                    $identifier = $itemData['product_id'] ?? $itemData['product_code'];

                    return Response::error('Product not found: ' . $identifier);
                }

                // Check if product is active
                if (! $product->active) {
                    return Response::error("Product '{$product->name}' (Code: {$product->code}) is not active.");
                }

                // Validate stock availability (unless stock tracking is disabled)
                if (! $product->dont_track_stock) {
                    $availableStock = $product->stocks->sum('balance');
                    if ($availableStock < $itemData['quantity']) {
                        $stockIssues[] = "- {$product->name}: Requested {$itemData['quantity']}, Available {$availableStock}";
                    }
                }

                // SECURITY: Always use product price from database, never user-provided
                $price = $product->price;
                $quantity = $itemData['quantity'];
                $subtotal = $price * $quantity;
                $total = $subtotal;

                $processedItems[] = [
                    'product_id'    => $product->id,
                    'quantity'      => $quantity,
                    'base_quantity' => $quantity,
                    'price'         => $price,
                    'net_price'     => $price,
                    'unit_price'    => $price,
                    'cost'          => $product->cost,
                    'unit_id'       => $product->unit_id,
                    'discount'      => 0,
                    'subtotal'      => $subtotal,
                    'total'         => $total,
                    'comment'       => $itemData['comment'] ?? null,
                    'store_id'      => $validated['store_id'],
                ];

                $totalAmount += $total;
            }

            // Return error if stock issues found
            if (! empty($stockIssues)) {
                return Response::error("Insufficient stock for the following items:\n\n" . implode("\n", $stockIssues));
            }

            $validated['items'] = $processedItems;
            $validated['subtotal'] = $totalAmount;
            $validated['total'] = $totalAmount;

            // Apply coupon discount if available
            $discountAmount = 0;
            if ($couponDiscount > 0) {
                $discountAmount = ($totalAmount * $couponDiscount) / 100;
                $validated['total'] = $totalAmount - $discountAmount;
            }

            $validated['grand_total'] = $validated['total'];

            // Remove any payment data - will create pending payment instead
            unset($validated['payments']);
            $validated['paid'] = 0;

            // Wrap all mutations in a transaction for data consistency
            $result = DB::transaction(function () use ($validated, $coupon, $customer, $user) {
                // Create the sale
                $sale = (new SaveSale)->execute($validated);

                // Increment coupon usage count if coupon was used
                if ($coupon) {
                    $coupon->increment('used');
                }

                // Create pending payment record with final amount (after coupon)
                $payment = Payment::create([
                    'date'        => $validated['date'],
                    'amount'      => $validated['grand_total'],
                    'received'    => false,
                    'method'      => 'Pending',
                    'payment_for' => 'Customer',
                    'reference'   => 'PAY-' . $sale->reference,
                    'sale_id'     => $sale->id,
                    'customer_id' => $customer->id,
                    'store_id'    => $validated['store_id'],
                    'user_id'     => $user->id,
                ]);

                return compact('sale', 'payment');
            });

            $sale = $result['sale'];
            $payment = $result['payment'];

            // Build response
            $output = "**Sale Order Created Successfully!**\n\n";

            if ($customerCreated) {
                $output .= "✓ New customer account created\n";
                $output .= "- Customer ID: {$customer->id}\n";
                $output .= "- Name: {$customer->name}\n\n";
            }

            $output .= "**Order Details:**\n";
            $output .= "- Order ID: {$sale->id}\n";
            $output .= "- Reference: {$sale->reference}\n";
            $output .= "- Customer: {$customer->name}\n";
            $output .= "- Date: {$sale->date}\n";
            if ($sale->details) {
                $output .= "- Notes: {$sale->details}\n";
            }

            $output .= "\n**Financial Summary:**\n";
            $output .= '- Subtotal: ' . number_format($sale->subtotal, 2) . "\n";
            if ($coupon) {
                $output .= "- Coupon ({$coupon->code}): -{$coupon->discount}%" . ' (-' . number_format($discountAmount, 2) . ")\n";
            }
            $output .= '- Total: ' . number_format($sale->grand_total, 2) . "\n";
            $output .= '- Paid: ' . number_format($sale->paid, 2) . "\n";
            $output .= '- Balance: ' . number_format($sale->grand_total - $sale->paid, 2) . "\n";

            $output .= "\n**Order Items:**\n";
            foreach ($sale->items as $item) {
                $itemOutput = "- {$item->product->name}";
                $itemOutput .= " (Code: {$item->product->code})";
                $itemOutput .= " x {$item->quantity}";
                $itemOutput .= ' @ ' . number_format($item->price, 2);
                $itemOutput .= ' = ' . number_format($item->total, 2);
                if ($item->comment) {
                    $itemOutput .= "\n  Note: {$item->comment}";
                }
                $output .= $itemOutput . "\n";
            }

            // Payment links section
            $output .= "\n**💳 Payment Required:**\n";
            $output .= 'Amount Due: ' . number_format($payment->amount, 2) . "\n";
            $output .= "Payment ID: {$payment->id}\n\n";

            // Generate payment links
            $publicPaymentUrl = URL::signedRoute('payment.show', ['id' => $payment->id]);
            $publicPaymentUrlWithHash = route('payment.url', ['id' => $payment->id, 'hash' => $payment->hash]);

            $output .= "**Payment Links:**\n";
            $output .= "- Public Payment Page (Signed): {$publicPaymentUrl}\n";
            $output .= "- Public Payment Page (Hash): {$publicPaymentUrlWithHash}\n";

            // Check if Shop module is available
            if (file_exists(base_path('modules/Shop/routes/web.php'))) {
                $shopPaymentUrl = URL::signedRoute('shop.payment.guest', [
                    'id'   => $payment->id,
                    'hash' => $payment->hash,
                ]);
                $output .= "- Shop Payment Page: {$shopPaymentUrl}\n";
            }

            $output .= "\n📌 **Next Steps:**\n";
            $output .= "1. Share one of the payment links with the customer\n";
            $output .= "2. Customer selects payment method (PayPal, Card, etc.)\n";
            $output .= "3. Payment is processed securely through the gateway\n";
            $output .= "4. Order status updates automatically upon successful payment\n";

            if ($customerCreated) {
                $output .= "\n💡 **Tip:** Customer can update billing details via the shop profile section.";
            }

            return Response::text($output);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return Response::error('Validation failed: ' . json_encode($e->errors()));
        } catch (\Exception $e) {
            return Response::error('Order creation failed: ' . $e->getMessage());
        }
    }

    /**
     * Get the tool's input schema.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            // Customer creation fields (only needed if user has no customer account)
            'customer_name' => $schema->string()
                ->description('Customer name (required only if user has no customer account)'),
            'customer_email' => $schema->string()
                ->description('Customer email (optional, defaults to user email)'),
            'customer_phone' => $schema->string()
                ->description('Customer phone number (optional)'),
            'customer_company' => $schema->string()
                ->description('Customer company name (optional, defaults to customer name)'),

            // Order fields
            'coupon_code' => $schema->string()
                ->description('Optional coupon code for discount. Only available if Shop module is installed.'),
            'details' => $schema->string()
                ->description('Optional order notes or details'),
            'items' => $schema->array()
                ->description('Array of order items. Each item needs product_id or product_code, quantity, and optional comment.')
                ->items(
                    $schema->object([
                        'product_id'   => $schema->integer()->description('Product ID (provide this or product_code)'),
                        'product_code' => $schema->string()->description('Product code (provide this or product_id)'),
                        'quantity'     => $schema->number()->description('Quantity to order (min 0.01)')->required(),
                        'comment'      => $schema->string()->description('Optional note for this item'),
                    ])
                )
                ->required(),
        ];
    }
}
