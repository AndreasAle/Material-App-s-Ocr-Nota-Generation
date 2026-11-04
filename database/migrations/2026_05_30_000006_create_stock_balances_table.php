<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained('materials')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
            $table->decimal('qty_on_hand', 15, 2)->default(0);
            $table->decimal('avg_cost', 15, 2)->default(0);    // average cost per unit
            $table->decimal('total_value', 15, 2)->default(0); // qty_on_hand * avg_cost
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();

            $table->unique(['material_id', 'warehouse_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_balances');
    }
};
