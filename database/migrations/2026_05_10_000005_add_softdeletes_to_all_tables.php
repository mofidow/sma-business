<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $tables = [
            'accounts', 'account_transactions', 'account_transfers', 'account_types',
            'adjustments', 'adjustment_items',
            'assets', 'asset_allocations', 'asset_categories', 'asset_maintenances',
            'attendances', 'award_points',
            'brands',
            'claims', 'cost_allocations', 'credit_installments', 'custom_fields',
            'customers', 'customer_groups',
            'deliveries', 'employees', 'expenses', 'gift_cards',
            'halls', 'incomes',
            'leave_types', 'leaves',
            'orders', 'payments', 'payrolls', 'payslips', 'payslip_items',
            'price_groups', 'printers', 'products', 'promotions',
            'purchase_items', 'purchases',
            'qr_orders', 'quotation_items', 'quotations',
            'registers', 'repair_order_attachments', 'repair_orders',
            'return_order_items', 'return_orders',
            'sale_items', 'sales', 'serials', 'service_types',
            'stock_count_items', 'stock_counts', 'stocks',
            'stores', 'suppliers',
            'tables', 'taxes', 'technicians', 'tracks', 'transfer_items', 'transfers',
            'units', 'variations',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->softDeletes();
                });
            }
        }
    }

    public function down(): void {}
};
