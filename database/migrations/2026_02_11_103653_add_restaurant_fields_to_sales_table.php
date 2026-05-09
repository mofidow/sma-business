<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'hall_id')) {
                $table->foreignId('hall_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('sales', 'table_id')) {
                $table->foreignId('table_id')->nullable()->after('hall_id')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('sales', 'reference_number')) {
                $table->string('reference_number')->nullable()->after('reference');
                $table->index('reference_number');
            }
            if (!Schema::hasColumn('sales', 'guests')) {
                $table->integer('guests')->nullable()->default(1)->after('reference_number');
            }
            if (!Schema::hasColumn('sales', 'notes')) {
                $table->text('notes')->nullable()->after('guests');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['hall_id']);
            $table->dropForeign(['table_id']);
            $table->dropIndex(['reference_number']);
            $table->dropColumn(['hall_id', 'table_id', 'reference_number', 'guests', 'notes']);
        });
    }
};
