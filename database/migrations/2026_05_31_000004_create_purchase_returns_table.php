<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_no')->unique();                               // RTN-202506-0001
            $table->foreignId('purchase_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('goods_receipt_id')->nullable()
                  ->constrained('goods_receipts')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();

            $table->date('return_date');
            $table->enum('reason', [
                'wrong_item',
                'damaged',
                'quantity_excess',
                'quality_reject',
                'spec_mismatch',
                'other',
            ]);
            $table->text('reason_detail')->nullable();

            $table->enum('status', ['draft', 'submitted', 'approved', 'returned', 'credited'])
                  ->default('draft');

            // Credit note from supplier
            $table->string('credit_note_no')->nullable();
            $table->date('credit_note_date')->nullable();
            $table->decimal('credit_amount', 15, 2)->default(0);

            // Approval
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_transaction_item_id')->nullable()
                  ->constrained('purchase_transaction_items')->nullOnDelete();
            $table->string('material_name_snapshot');
            $table->string('unit_name_snapshot');
            $table->decimal('qty_returned', 15, 2);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_return_items');
        Schema::dropIfExists('purchase_returns');
    }
};
