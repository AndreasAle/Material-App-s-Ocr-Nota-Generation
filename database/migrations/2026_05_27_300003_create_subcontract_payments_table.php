<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subcontract_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subcontract_id')->constrained('subcontracts')->cascadeOnDelete();
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method')->nullable(); // transfer, tunai, cek
            $table->string('reference_no')->nullable(); // no. kwitansi/bukti
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subcontract_payments');
    }
};
