<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('return_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('return_orders', 'return_payment_amount')) {
                $table->decimal('return_payment_amount', 25, 4)->nullable()->after('grand_total');
            }
            if (!Schema::hasColumn('return_orders', 'return_payment_method')) {
                $table->string('return_payment_method', 25)->nullable()->after('return_payment_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('return_orders', function (Blueprint $table) {
            $table->dropColumn(['return_payment_amount', 'return_payment_method']);
        });
    }
};
