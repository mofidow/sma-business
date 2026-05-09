<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('asset_maintenances')) {
            return;
        }
        Schema::create('asset_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained();
            $table->string('title');
            $table->string('type')->default('Scheduled');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('cost', 25, 4)->nullable();
            $table->text('note')->nullable();
            $table->string('status')->default('Scheduled');
            $table->foreignId('user_id')->nullable()->constrained();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_maintenances');
    }
};
