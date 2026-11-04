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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_no', 50)->unique();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            // Status alur: draft → submitted → approved/rejected → converted
            $table->enum('status', ['draft','submitted','approved','rejected','converted'])->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->date('expected_date')->nullable();  // tanggal diharapkan tiba
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->text('notes')->nullable();

            // Link ke transaksi jika sudah dikonversi
            $table->foreignId('converted_transaction_id')->nullable()
                  ->constrained('purchase_transactions')->nullOnDelete();

            $table->timestamps();
            $table->index(['project_id', 'status']);
            $table->index(['supplier_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
