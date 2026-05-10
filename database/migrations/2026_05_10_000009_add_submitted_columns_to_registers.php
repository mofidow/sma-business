<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $columns = [
            'cash_in_register'       => fn (Blueprint $t) => $t->decimal('cash_in_register', 25, 4)->nullable(),
            'cash_submitted'         => fn (Blueprint $t) => $t->decimal('cash_submitted', 25, 4)->nullable(),
            'cc_payments_submitted'  => fn (Blueprint $t) => $t->decimal('cc_payments_submitted', 25, 4)->nullable(),
            'stripe_payments_submitted' => fn (Blueprint $t) => $t->decimal('stripe_payments_submitted', 25, 4)->nullable(),
            'other_payments_submitted'  => fn (Blueprint $t) => $t->decimal('other_payments_submitted', 25, 4)->nullable(),
        ];

        foreach ($columns as $column => $definition) {
            if (Schema::hasTable('registers') && ! Schema::hasColumn('registers', $column)) {
                Schema::table('registers', $definition);
            }
        }
    }

    public function down(): void {}
};
