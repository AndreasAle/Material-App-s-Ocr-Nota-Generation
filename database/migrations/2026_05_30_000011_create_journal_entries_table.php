<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_no', 30)->unique();              // JE-2026-00001
            $table->date('entry_date');
            $table->string('reference_type', 50)->nullable();      // invoice/purchase_transaction/payment/manual
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('description');
            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);
            $table->string('status', 20)->default('draft');        // draft / posted / void
            $table->string('currency', 5)->default('IDR');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->index(['reference_type', 'reference_id']);
            $table->index('entry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
