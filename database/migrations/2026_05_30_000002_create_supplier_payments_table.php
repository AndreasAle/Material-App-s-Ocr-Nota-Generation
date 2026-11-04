<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_transaction_id')->constrained('purchase_transactions')->cascadeOnDelete();
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method', 50)->default('transfer'); // transfer, cash, giro, cek
            $table->string('reference_no', 100)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_payments');
    }
};
