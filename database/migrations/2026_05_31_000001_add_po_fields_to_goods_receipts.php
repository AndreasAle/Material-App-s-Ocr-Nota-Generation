<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            // GR sekarang bisa dibuat dari PO langsung (sebelum ada transaction)
            $table->foreignId('purchase_order_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('purchase_orders')
                  ->nullOnDelete();

            // purchase_transaction_id sekarang nullable — bisa null saat GR dari PO belum invoice
            $table->foreignId('purchase_transaction_id')
                  ->nullable()
                  ->change();

            // Inspector & QC fields
            $table->string('inspector_name')->nullable()->after('received_by');
            $table->enum('inspection_status', ['pending', 'passed', 'failed', 'partial_pass'])
                  ->default('pending')
                  ->after('inspector_name');
            $table->text('inspection_notes')->nullable()->after('inspection_status');

            // Supplier's delivery note reference
            $table->string('supplier_do_no')->nullable()->after('receipt_no'); // Delivery Order supplier
            $table->date('supplier_do_date')->nullable()->after('supplier_do_no');

            // Linked to supplier invoice (3-way match)
            $table->foreignId('supplier_invoice_id')
                  ->nullable()
                  ->after('purchase_transaction_id')
                  ->constrained('purchase_transactions')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('goods_receipts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('purchase_order_id');
            $table->dropConstrainedForeignId('supplier_invoice_id');
            $table->dropColumn(['inspector_name', 'inspection_status', 'inspection_notes',
                                'supplier_do_no', 'supplier_do_date']);
        });
    }
};
