<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('goods_receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_receipt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('purchase_transaction_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('material_name_snapshot');
            $table->string('unit_name_snapshot');
            $table->decimal('qty_ordered', 15, 2);
            $table->decimal('qty_received', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('goods_receipt_items');
    }
};
