<?php

use App\Http\Controllers\Sma;
use App\Http\Controllers\Auth;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->get('/forgot-password', [Auth\AuthController::class, 'forgotPassword'])->name('password.request');

Route::middleware([
    'language', 'auth:sanctum', config('jetstream.auth_session'),
])->group(function () {
    Route::redirect('/', '/admin/dashboard');
    Route::get('/alerts', [Sma\AjaxController::class, 'alerts'])->name('alerts');
    Route::get('/dashboard', [Sma\HomeController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [Sma\HomeController::class, 'data'])->name('dashboard.data');
    Route::get('/language/{language}', [Sma\AjaxController::class, 'language'])->name('language');
    Route::post('/dashboard/chart', [Sma\HomeController::class, 'chart'])->name('dashboard.chart');
    Route::post('/select/{store}', [Sma\AjaxController::class, 'selectStore'])->name('stores.select');

    // Search Routes
    Route::prefix('search')->group(base_path('routes/groups/search.php'));

    Route::get('/products/labels', [Sma\Product\LabelController::class, 'index'])->name('labels.index');
    Route::get('/products/{product}/track', Sma\Product\StockTrackController::class)->name('products.track');

    Route::get('/impersonate/stop', [Sma\ImpersonateController::class, 'stop'])->name('impersonate.stop');
    Route::get('/impersonate/as/{user}', [Sma\ImpersonateController::class, 'start'])->name('impersonate');

    Route::get('customers/{customer}/statement', [Sma\People\CustomerController::class, 'statement'])->name('customers.statement');
    Route::get('suppliers/{supplier}/statement', [Sma\People\SupplierController::class, 'statement'])->name('suppliers.statement');

    Route::get('gift_cards/{gift_card}/logs', [Sma\Order\GiftCardController::class, 'logs'])->name('gift_cards.logs');
    Route::post('gift_cards/{gift_card}/topup', [Sma\Order\GiftCardController::class, 'topup'])->name('gift_cards.topup');
    Route::get('gift_cards/{number}/details', [Sma\Order\GiftCardController::class, 'details'])->name('gift_cards.details');
    Route::get('stock_counts/{stock_count}/export', [Sma\Product\StockCountController::class, 'export'])->name('stock_counts.export');
    Route::post('stock_counts/{stock_count}/adjust', [Sma\Product\StockCountController::class, 'adjust'])->name('stock_counts.adjust');
    Route::get('stock_counts/{stock_count}/download', [Sma\Product\StockCountController::class, 'download'])->name('stock_counts.download');

    Route::resources([
        'products/units'         => Sma\Product\UnitController::class,
        'people/price_groups'    => Sma\People\PriceGroupController::class,
        'people/customer_groups' => Sma\People\CustomerGroupController::class,
    ]);

    Route::extendedResources([
        'products/brands'     => Sma\Product\BrandController::class,
        'products/categories' => Sma\Product\CategoryController::class,
        'products/promotions' => Sma\Product\PromotionController::class,
        'products'            => Sma\Product\ProductController::class,

        'people/roles'     => Sma\People\RoleController::class,
        'people/users'     => Sma\People\UserController::class,
        'people/customers' => Sma\People\CustomerController::class,
        'people/suppliers' => Sma\People\SupplierController::class,
        'people/addresses' => Sma\People\AddressController::class,

        'gift_cards' => Sma\Order\GiftCardController::class,
    ]);

    Route::portResources([
        'brands'     => Sma\Product\BrandPortController::class,
        'products'   => Sma\Product\ProductPortController::class,
        'categories' => Sma\Product\CategoryPortController::class,

        'customers' => Sma\People\CustomerPortController::class,
        'suppliers' => Sma\People\SupplierPortController::class,
    ]);

    // Order routes require store middleware
    Route::middleware('store')->group(function () {
        // Credits: exclude create/store — conversion uses credits.convert instead
        Route::extendedResource('credits', Sma\Order\CreditController::class, ['except' => ['create', 'store']]);

        Route::extendedResources([
            'sales'         => Sma\Order\SaleController::class,
            'expenses'      => Sma\Order\ExpenseController::class,
            'incomes'       => Sma\Order\IncomeController::class,
            'payments'      => Sma\Order\PaymentController::class,
            'deliveries'    => Sma\Order\DeliveryController::class,
            'purchases'     => Sma\Order\PurchaseController::class,
            'quotations'    => Sma\Order\QuotationController::class,
            'transfers'     => Sma\Product\TransferController::class,
            'adjustments'   => Sma\Product\AdjustmentController::class,
            'return_orders' => Sma\Order\ReturnOrderController::class,
            'stock_counts'  => Sma\Product\StockCountController::class,
            'service-types' => Sma\Repair\ServiceTypeController::class,
            'technicians'   => Sma\Repair\TechnicianController::class,
            'repair-orders' => Sma\Repair\RepairOrderController::class,
        ]);

        // Credit (Deyn) installment payment
        Route::post('credits/{sale}/pay/{installment}', [Sma\Order\CreditController::class, 'recordPayment'])
            ->name('credits.pay');
        Route::post('credits/{sale}/convert', [Sma\Order\CreditController::class, 'store'])
            ->name('credits.convert');

        // Repair Module Routes
        Route::post('repair-orders/{repair_order}/generate-invoice', [Sma\Repair\RepairOrderController::class, 'generateInvoice'])
            ->name('repair-orders.generate-invoice');
        Route::get('repair-order-attachments/{attachment}', [Sma\Repair\RepairOrderController::class, 'downloadAttachment'])
            ->name('repair-order-attachments.download');
        Route::delete('repair-order-attachments/{attachment}', [Sma\Repair\RepairOrderController::class, 'deleteAttachment'])
            ->name('repair-order-attachments.destroy');
    });

    // Email Routes
    Route::prefix('emails')->group(function () {
        Route::post('/email/sale/{sale}', [Sma\Order\EmailController::class, 'sale'])->name('email.sale');
        Route::post('/email/payment/{payment}', [Sma\Order\EmailController::class, 'payment'])->name('email.payment');
        Route::post('/email/purchase/{purchase}', [Sma\Order\EmailController::class, 'purchase'])->name('email.purchase');
        Route::post('/email/quotation/{quotation}', [Sma\Order\EmailController::class, 'quotation'])->name('email.quotation');
        Route::post('/email/return_order/{return_order}', [Sma\Order\EmailController::class, 'returnOrder'])->name('email.return_order');

        Route::post('/email/transfer/{transfer}', [Sma\Product\EmailController::class, 'transfer'])->name('email.transfer');
    });

    // Reports Routes
    Route::prefix('reports')->group(base_path('routes/groups/reports.php'));

    // Settings Routes
    Route::prefix('settings')->group(base_path('routes/groups/settings.php'));

    // Accounting Routes
    Route::prefix('accounting')->group(base_path('routes/groups/accounting.php'));

    // HR Routes
    Route::prefix('hr')->group(base_path('routes/groups/hr.php'));

    // Attachments Routes
    Route::get('/attachments/{attachment:uuid}', [Sma\AttachmentController::class, 'download'])->name('sma.attachments.download');
    Route::delete('/attachments/{attachment}', [Sma\AttachmentController::class, 'destroy'])->name('sma.attachments.destroy');
});
