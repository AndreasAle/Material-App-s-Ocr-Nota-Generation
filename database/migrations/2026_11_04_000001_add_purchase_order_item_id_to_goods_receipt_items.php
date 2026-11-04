<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Surat jalan dari PO perlu tahu item PO-nya, agar stok masuk tercatat per material
 * dan qty yang sudah diterima bisa dikoreksi bila surat jalan dihapus.
 */
return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('goods_receipt_items', 'purchase_order_item_id')) {
            return;
        }

        Schema::table('goods_receipt_items', function (Blueprint $table) {
            $table->foreignId('purchase_order_item_id')
                ->nullable()
                ->after('purchase_transaction_item_id')
                ->constrained('purchase_order_items')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('goods_receipt_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('purchase_order_item_id');
        });
    }
};
