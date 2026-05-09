<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('asset_allocations')) {
            return;
        }
        Schema::create('asset_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained();
            $table->foreignId('allocated_to')->constrained('users');
            $table->foreignId('allocated_by')->constrained('users');
            $table->date('allocated_date');
            $table->date('return_date')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_allocations');
    }
};
