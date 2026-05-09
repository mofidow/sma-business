<?php

use App\Models\User;
use App\Models\Sma\Pos\Register;
use App\Models\Sma\Setting\Store;
use App\Models\Sma\People\Customer;
use App\Models\Sma\Accounting\Account;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('incomes')) {
            return;
        }
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->string('reference', 36)->unique()->index();
            $table->decimal('amount', 25, 4)->nullable();
            $table->text('details')->nullable();
            $table->foreignIdFor(User::class)->constrained();
            $table->foreignIdFor(Store::class)->constrained();
            $table->foreignIdFor(User::class, 'reviewed_by')->nullable();
            $table->foreignIdFor(Customer::class)->nullable()->constrained();
            $table->foreignIdFor(Register::class)->nullable();
            $table->foreignIdFor(Account::class)->nullable()->constrained();
            $table->schemalessAttributes('extra_attributes');
            $table->string('hash')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
