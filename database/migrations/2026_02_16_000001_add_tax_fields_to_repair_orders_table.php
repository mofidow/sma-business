<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('repair_orders')) {
            return;
        }
        Schema::table('repair_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('repair_orders', 'tax_amount')) {
                $table->decimal('tax_amount', 15, 4)->default(0)->after('actual_cost');
            }
            if (!Schema::hasColumn('repair_orders', 'tax_included')) {
                $table->boolean('tax_included')->default(false)->after('tax_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('repair_orders', function (Blueprint $table) {
            $table->dropColumn(['tax_amount', 'tax_included']);
        });
    }
};
