<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('printers')) {
            return;
        }

        Schema::create('printers', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('store_id')->nullable();
            $t->string('name');
            $t->string('type')->default('receipt');
            $t->string('driver')->nullable();
            $t->json('settings')->nullable();
            $t->boolean('active')->default(1);
            $t->timestamps();
            $t->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('printers');
    }
};
