<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchase_transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->nullable()->constrained()->nullOnDelete();
            $table->string('material_name_snapshot');
            $table->string('unit_name_snapshot');
            $table->decimal('qty', 15, 2);
            $table->decimal('price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_transaction_items');
    }
};