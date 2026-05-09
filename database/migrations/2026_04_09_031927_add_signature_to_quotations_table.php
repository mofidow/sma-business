<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            if (!Schema::hasColumn('quotations', 'signature')) {
                $table->longText('signature')->nullable()->after('hash');
            }
            if (!Schema::hasColumn('quotations', 'signed_by_name')) {
                $table->string('signed_by_name')->nullable()->after('signature');
            }
            if (!Schema::hasColumn('quotations', 'signed_at')) {
                $table->timestamp('signed_at')->nullable()->after('signed_by_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn(['signature', 'signed_by_name', 'signed_at']);
        });
    }
};
