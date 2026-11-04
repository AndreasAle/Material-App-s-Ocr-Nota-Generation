<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->string('invoice_no', 50)->unique();

            $table->foreignId('project_id')
                  ->nullable()
                  ->constrained('projects')
                  ->nullOnDelete();

            // Klien — nama & alamat disimpan sebagai snapshot (tidak harus user sistem)
            $table->string('client_name');
            $table->text('client_address')->nullable();
            $table->string('client_phone', 30)->nullable();

            $table->date('invoice_date');
            $table->date('due_date')->nullable();

            // Status pembayaran
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');

            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('tax_percent', 5, 2)->default(0);   // PPN misal 11
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);  // total sudah dibayar

            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();

            $table->index(['project_id', 'payment_status']);
            $table->index('invoice_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
