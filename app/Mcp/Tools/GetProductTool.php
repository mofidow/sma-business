<?php

namespace App\Mcp\Tools;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use App\Models\Sma\Product\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[IsReadOnly(true)]
class GetProductTool extends Tool
{
    /**
     * The tool's description.
     */
    protected string $description = <<<'MARKDOWN'
        Get detailed information about a specific product by its ID or code.
        Returns comprehensive product details including pricing, stock levels, variations, and related information.
        Use SearchProductsTool to browse and find products first.
    MARKDOWN;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'product_id'   => 'required_without:product_code|nullable|integer',
            'product_code' => 'required_without:product_id|nullable|string',
        ], [
            'product_id.required_without'   => 'Provide either a product_id (integer) or a product_code (string) to look up the product.',
            'product_code.required_without' => 'Provide either a product_id (integer) or a product_code (string) to look up the product.',
        ]);

        $query = Product::query()
            ->with([
                'brand:id,name',
                'category:id,name',
                'subcategory:id,name',
                'unit:id,name,code',
                'taxes:id,name,rate',
                'stocks',
                'variations',
            ]);

        if ($validated['product_id'] ?? null) {
            $product = $query->find($validated['product_id']);
        } else {
            $product = $query->where('code', $validated['product_code'])->first();
        }

        if (! $product) {
            return Response::error('Product not found.');
        }

        $output = "**{$product->name}**\n\n";
        $output .= "**Basic Information:**\n";
        $output .= "- ID: {$product->id}\n";
        $output .= "- Code: {$product->code}\n";
        $output .= "- Type: {$product->type}\n";
        $output .= '- SKU: ' . ($product->sku ?? 'N/A') . "\n";

        if ($product->description) {
            $output .= "\n**Description:**\n{$product->description}\n";
        }

        $output .= "\n**Pricing:**\n";
        $output .= '- Price: ' . number_format($product->price, 2) . "\n";
        $output .= '- Unit: ' . ($product->unit?->name ?? 'N/A') . " ({$product->unit?->code})\n";

        $output .= "\n**Classification:**\n";
        $output .= '- Brand: ' . ($product->brand?->name ?? 'N/A') . "\n";
        $output .= '- Category: ' . ($product->category?->name ?? 'N/A') . "\n";
        if ($product->subcategory) {
            $output .= "- Subcategory: {$product->subcategory->name}\n";
        }

        if ($product->taxes->count() > 0) {
            $output .= "\n**Taxes:**\n";
            foreach ($product->taxes as $tax) {
                $output .= "- {$tax->name}: {$tax->rate}%\n";
            }
        }

        $totalStock = $product->stocks->sum('balance');
        $output .= "\n**Stock Information:**\n";
        $output .= "- Total Stock: {$totalStock}\n";

        if ($product->stocks->count() > 0) {
            $output .= "\n**Stock by Store:**\n";
            foreach ($product->stocks as $stock) {
                $storeName = $stock->store?->name ?? "Store {$stock->store_id}";
                $output .= "- {$storeName}: {$stock->balance}" . "\n";
            }
        }

        if ($product->variations->count() > 0) {
            $output .= "\n**Variations:**\n";
            foreach ($product->variations as $variation) {
                $output .= "- {$variation->name} (SKU: {$variation->sku})";
                if ($variation->price) {
                    $output .= ' - Price: ' . number_format($variation->price, 2);
                }
                $output .= "\n";
            }
        }

        if ($product->details) {
            $output .= "\n**Additional Details:**\n{$product->details}\n";
        }

        return Response::text($output);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'product_id' => $schema->integer()
                ->description('The product ID (required if product_code is not provided)'),
            'product_code' => $schema->string()
                ->description('The product code (required if product_id is not provided)'),
        ];
    }
}
