<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Pivot antara PO dan Transactions — satu PO bisa punya banyak transaction (partial delivery)
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('purchase_order_transactions')) {
            Schema::create('purchase_order_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('purchase_transaction_id')->constrained()->cascadeOnDelete();
                $table->string('note')->nullable();
                $table->timestamps();

                $table->unique(['purchase_order_id', 'purchase_transaction_id'], 'po_tx_unique');
            });
        }

        // Tambah po_id ke purchase_transactions untuk back-reference
        Schema::table('purchase_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_transactions', 'purchase_order_id')) {
                $table->foreignId('purchase_order_id')
                      ->nullable()
                      ->after('project_id')
                      ->constrained('purchase_orders')
                      ->nullOnDelete();
            }
            if (!Schema::hasColumn('purchase_transactions', 'goods_receipt_status')) {
                $table->string('goods_receipt_status')
                      ->default('not_required')
                      ->after('purchase_order_id');
            }
            if (!Schema::hasColumn('purchase_transactions', 'supplier_invoice_no')) {
                $table->string('supplier_invoice_no')->nullable()->after('note_number');
            }
            if (!Schema::hasColumn('purchase_transactions', 'supplier_invoice_date')) {
                $table->date('supplier_invoice_date')->nullable()->after('supplier_invoice_no');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_transactions');
        Schema::table('purchase_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('purchase_order_id');
            $table->dropColumn(['goods_receipt_status', 'supplier_invoice_no', 'supplier_invoice_date']);
        });
    }
};
