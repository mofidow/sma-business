<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Sales: common filter columns
        Schema::table('sales', function (Blueprint $table) {
            if (! $this->hasIndex('sales', 'sales_customer_id_date_index')) {
                $table->index(['customer_id', 'date'], 'sales_customer_id_date_index');
            }
            if (! $this->hasIndex('sales', 'sales_is_credit_credit_status_index')) {
                $table->index(['is_credit', 'credit_status'], 'sales_is_credit_credit_status_index');
            }
        });

        // Payments: date-based queries
        Schema::table('payments', function (Blueprint $table) {
            if (! $this->hasIndex('payments', 'payments_date_received_index')) {
                $table->index(['date', 'received'], 'payments_date_received_index');
            }
            if (! $this->hasIndex('payments', 'payments_customer_id_index')) {
                $table->index('customer_id', 'payments_customer_id_index');
            }
        });

        // Products: barcode and code lookups
        Schema::table('products', function (Blueprint $table) {
            if (! $this->hasIndex('products', 'products_barcode_index')) {
                $table->index('barcode', 'products_barcode_index');
            }
        });

        // Activity log: common polymorphic filter
        Schema::table('activity_log', function (Blueprint $table) {
            if (! $this->hasIndex('activity_log', 'activity_log_created_at_index')) {
                $table->index('created_at', 'activity_log_created_at_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndexIfExists('sales_customer_id_date_index');
            $table->dropIndexIfExists('sales_is_credit_credit_status_index');
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndexIfExists('payments_date_received_index');
            $table->dropIndexIfExists('payments_customer_id_index');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndexIfExists('products_barcode_index');
        });
        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropIndexIfExists('activity_log_created_at_index');
        });
    }

    private function hasIndex(string $table, string $index): bool
    {
        return collect(Schema::getIndexes($table))->contains(fn ($i) => $i['name'] === $index);
    }
};
