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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->enum('status', ['draft', 'processed', 'paid'])->default('draft');
            $table->decimal('total_amount', 15, 4)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['store_id', 'month', 'year']);
        });

        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->decimal('basic_salary', 15, 4)->default(0);
            $table->unsignedSmallInteger('working_days')->default(0);
            $table->unsignedSmallInteger('present_days')->default(0);
            $table->unsignedSmallInteger('absent_days')->default(0);
            $table->unsignedSmallInteger('on_leave_days_paid')->default(0);
            $table->unsignedSmallInteger('on_leave_days_unpaid')->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->decimal('overtime_amount', 15, 4)->default(0);
            $table->decimal('gross_salary', 15, 4)->default(0);
            $table->decimal('overtime_rate', 15, 4)->nullable()->default(0);
            $table->decimal('absent_deduction', 15, 4)->nullable()->default(0);
            $table->decimal('unpaid_leave_deduction', 15, 4)->nullable()->default(0);
            $table->decimal('total_deductions', 15, 4)->default(0);
            $table->decimal('total_allowances', 15, 4)->default(0);
            $table->decimal('net_salary', 15, 4)->default(0);
            $table->enum('status', ['draft', 'paid'])->default('draft');
            $table->date('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('payslip_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payslip_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['allowance', 'deduction']);
            $table->string('description');
            $table->decimal('amount', 15, 4)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payslip_items');
        Schema::dropIfExists('payslips');
        Schema::dropIfExists('payrolls');
    }
};
