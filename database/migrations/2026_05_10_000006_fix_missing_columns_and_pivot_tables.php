<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // store_user pivot table (User belongsToMany Store)
        if (! Schema::hasTable('store_user')) {
            Schema::create('store_user', function (Blueprint $t) {
                $t->unsignedBigInteger('store_id');
                $t->unsignedBigInteger('user_id');
                $t->primary(['store_id', 'user_id']);
                $t->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
                $t->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // accounts.reference — all models with $hasReference need this column
        $tablesNeedingReference = [
            'accounts', 'account_transactions', 'account_transfers',
            'adjustments', 'claims', 'deliveries',
            'expenses', 'incomes', 'leaves', 'orders',
            'payments', 'payrolls', 'purchases', 'quotations',
            'repair_orders', 'return_orders', 'sales',
            'stock_counts', 'transfers',
        ];

        foreach ($tablesNeedingReference as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'reference')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->string('reference')->nullable();
                });
            }
        }

        // stores.references (json) for reference number tracking
        if (Schema::hasTable('stores') && ! Schema::hasColumn('stores', 'references')) {
            Schema::table('stores', function (Blueprint $t) {
                $t->json('references')->nullable();
            });
        }

        // stores.active boolean (may be missing)
        if (Schema::hasTable('stores') && ! Schema::hasColumn('stores', 'active')) {
            Schema::table('stores', function (Blueprint $t) {
                $t->boolean('active')->default(1);
            });
        }
    }

    public function down(): void {}
};
