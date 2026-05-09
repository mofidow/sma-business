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
        Schema::create('repair_orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('hash')->index()->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('technician_id')->nullable()->constrained('technicians')->nullOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Service details
            $table->string('serial_no')->nullable()->index();
            $table->text('problem_description');
            $table->string('device_password')->nullable();
            $table->string('device_pattern')->nullable();
            $table->enum('device_condition', ['excellent', 'good', 'fair', 'poor'])->default('good');
            $table->schemalessAttributes('extra_attributes');

            // Assignment & costs
            $table->text('technician_comment')->nullable();
            $table->decimal('price', 15, 4)->default(0);
            $table->decimal('actual_cost', 15, 4)->default(0)->nullable();

            // Dates & status
            $table->date('received_date');
            $table->date('due_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'waiting_parts', 'completed', 'delivered', 'cancelled'])->default('pending');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');

            // Invoice & notes
            $table->foreignId('invoice_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->text('internal_notes')->nullable();
            $table->text('customer_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_orders');
    }
};
