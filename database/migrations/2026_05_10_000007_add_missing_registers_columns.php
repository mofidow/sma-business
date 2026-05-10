<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $columns = [
            'cash_in_hand'           => fn (Blueprint $t) => $t->decimal('cash_in_hand', 25, 4)->nullable(),
            'note'                   => fn (Blueprint $t) => $t->text('note')->nullable(),
            'cash_amount'            => fn (Blueprint $t) => $t->decimal('cash_amount', 25, 4)->nullable(),
            'cc_payments_amount'     => fn (Blueprint $t) => $t->decimal('cc_payments_amount', 25, 4)->nullable(),
            'total_sales'            => fn (Blueprint $t) => $t->decimal('total_sales', 25, 4)->nullable(),
            'total_expenses'         => fn (Blueprint $t) => $t->decimal('total_expenses', 25, 4)->nullable(),
            'gift_card_amount'       => fn (Blueprint $t) => $t->decimal('gift_card_amount', 25, 4)->nullable(),
            'other_payments_amount'  => fn (Blueprint $t) => $t->decimal('other_payments_amount', 25, 4)->nullable(),
            'stripe_payments_amount' => fn (Blueprint $t) => $t->decimal('stripe_payments_amount', 25, 4)->nullable(),
            'total_purchases'        => fn (Blueprint $t) => $t->decimal('total_purchases', 25, 4)->nullable(),
        ];

        foreach ($columns as $column => $definition) {
            if (Schema::hasTable('registers') && ! Schema::hasColumn('registers', $column)) {
                Schema::table('registers', $definition);
            }
        }
    }

    public function down(): void {}
};
