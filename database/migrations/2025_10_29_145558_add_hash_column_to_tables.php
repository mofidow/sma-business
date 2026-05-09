<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['sales', 'payments', 'purchases', 'quotations', 'deliveries', 'expenses', 'return_orders', 'transfers'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && ! Schema::hasColumn($tableName, 'hash')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->string('hash')->nullable();
                });
            }
        }
    }
};
