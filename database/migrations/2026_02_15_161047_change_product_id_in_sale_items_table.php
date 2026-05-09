<?php

use App\Models\Sma\Repair\RepairOrder;
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
        Schema::table('sale_items', function (Blueprint $table) {
            $table->foreignId('product_id')->nullable()->change();
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->foreignIdFor(RepairOrder::class)->nullable();
        });
    }
};
