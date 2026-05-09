<?php

use App\Models\Sma\Accounting\Account;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('expenses', 'account_id')) {
            return;
        }
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignIdFor(Account::class)->nullable()->after('register_id')->constrained();
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignIdFor(Account::class);
        });
    }
};
