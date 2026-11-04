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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('material_id')
                  ->nullable()
                  ->constrained('materials')
                  ->nullOnDelete();

            $table->string('material_name_snapshot');
            $table->string('unit_name_snapshot');

            // in = masuk, out = keluar, adjustment = koreksi manual
            $table->enum('movement_type', ['in', 'out', 'adjustment'])->default('in');

            $table->decimal('qty', 15, 2);            // selalu positif
            $table->decimal('unit_price', 15, 2)->nullable(); // untuk valuasi

            // referensi sumber: goods_receipt / purchase_transaction / manual
            $table->enum('reference_type', ['goods_receipt', 'purchase_transaction', 'manual_in', 'manual_out', 'adjustment'])->default('manual_in');
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->foreignId('project_id')
                  ->nullable()
                  ->constrained('projects')
                  ->nullOnDelete();

            $table->string('warehouse')->nullable(); // lokasi/gudang

            $table->text('notes')->nullable();
            $table->date('movement_date');

            $table->foreignId('created_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamps();

            $table->index(['material_id', 'movement_date']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
