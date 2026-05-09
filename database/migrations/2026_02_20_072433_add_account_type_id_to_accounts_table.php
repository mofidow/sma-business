<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->string('type')->nullable()->change();
            if (!Schema::hasColumn('accounts', 'account_type_id')) {
                $table->foreignId('account_type_id')->nullable()->constrained('account_types');
            }
        });
    }

    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropForeign(['account_type_id']);
            $table->dropColumn('account_type_id');
        });
    }
};
