<?php

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
        try {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('points');
            });
        } catch (Exception $e) {
            // Do nothing if column does not exist
        }
    }
};
