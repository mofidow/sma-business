<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The base schema created unit_prices with morphs('priceable') → priceable_id / priceable_type.
 * Product::unitPrices() uses morphMany(UnitPrice::class, 'subject') → needs subject_id / subject_type.
 * UnitPrice extends Model which uses SoftDeletes → needs deleted_at.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unit_prices', function (Blueprint $table) {
            if (! Schema::hasColumn('unit_prices', 'subject_type')) {
                $table->string('subject_type')->nullable()->after('unit_id');
            }
            if (! Schema::hasColumn('unit_prices', 'subject_id')) {
                $table->unsignedBigInteger('subject_id')->nullable()->after('subject_type');
            }
            if (! Schema::hasColumn('unit_prices', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Add index if it doesn't exist yet
        try {
            Schema::table('unit_prices', function (Blueprint $table) {
                $table->index(['subject_type', 'subject_id'], 'unit_prices_subject_index');
            });
        } catch (\Throwable) {
            // index already exists
        }
    }

    public function down(): void
    {
        Schema::table('unit_prices', function (Blueprint $table) {
            $table->dropIndex('unit_prices_subject_index');
            $table->dropColumn(['subject_type', 'subject_id', 'deleted_at']);
        });
    }
};
