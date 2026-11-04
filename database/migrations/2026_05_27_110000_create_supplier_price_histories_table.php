<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('supplier_price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->nullable()->constrained()->nullOnDelete();
            $table->string('material_name_snapshot');
            $table->string('unit_name_snapshot');
            $table->decimal('price', 15, 2);
            $table->date('recorded_at');
            $table->foreignId('purchase_transaction_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['supplier_id', 'material_id', 'recorded_at'], 'sph_supplier_material_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_price_histories');
    }
};
