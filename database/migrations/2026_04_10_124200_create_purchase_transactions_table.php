<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Migration ini sebelumnya hilang: tabel purchase_transactions hanya ada di database lama.
 * Kolom tambahan (scan, approval, pembayaran, PO) dibuat oleh migration-migration berikutnya.
 * Di database yang tabelnya sudah ada, migration ini dilewati.
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('purchase_transactions')) {
            return;
        }

        Schema::create('purchase_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_no')->unique();
            $table->date('transaction_date');
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            // Tabel projects dibuat setelah migration ini, jadi tanpa foreign key constraint
            $table->unsignedBigInteger('project_id')->nullable()->index();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('note_number')->nullable();
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->unsignedInteger('attachment_count')->default(0);
            $table->string('status')->default('draft'); // draft|submitted|posted
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('transaction_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_transactions');
    }
};
