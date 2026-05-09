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
        Schema::table('quotations', function (Blueprint $table) {
            $table->longText('signature')->nullable()->after('hash');
            $table->string('signed_by_name')->nullable()->after('signature');
            $table->timestamp('signed_at')->nullable()->after('signed_by_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn(['signature', 'signed_by_name', 'signed_at']);
        });
    }
};
