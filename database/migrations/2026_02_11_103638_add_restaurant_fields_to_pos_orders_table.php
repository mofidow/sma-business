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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('hall_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();
            $table->foreignId('table_id')->nullable()->after('hall_id')->constrained()->nullOnDelete();
            $table->string('reference_number')->nullable()->after('reference');
            $table->integer('guests')->nullable()->default(1)->after('reference_number');
            $table->text('notes')->nullable()->after('guests');

            $table->index('reference_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['hall_id']);
            $table->dropForeign(['table_id']);
            $table->dropIndex(['reference_number']);
            $table->dropColumn(['hall_id', 'table_id', 'reference_number', 'guests', 'notes']);
        });
    }
};
