<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'is_credit')) {
                $table->boolean('is_credit')->default(false)->after('shop');
                $table->index('is_credit');
            }
            if (!Schema::hasColumn('sales', 'credit_status')) {
                $table->string('credit_status', 20)->nullable()->after('is_credit');
                $table->index('credit_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['is_credit']);
            $table->dropIndex(['credit_status']);
            $table->dropColumn(['is_credit', 'credit_status']);
        });
    }
};
