<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('suppliers', 'vat_no')) {
            return;
        }
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('vat_no')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('vat_no');
        });
    }
};
