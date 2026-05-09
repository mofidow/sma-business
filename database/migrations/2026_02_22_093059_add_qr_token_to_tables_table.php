<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->uuid('qr_token')->unique()->nullable()->after('sort_order');
        });

        DB::table('tables')->whereNull('qr_token')->orderBy('id')->each(function ($table) {
            DB::table('tables')->where('id', $table->id)->update(['qr_token' => Str::uuid()->toString()]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tables', function (Blueprint $table) {
            $table->dropColumn('qr_token');
        });
    }
};
