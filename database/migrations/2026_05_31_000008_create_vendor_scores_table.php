<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();
            $table->year('year');
            $table->unsignedTinyInteger('month');   // 1-12

            // Metrics dari GR & Transactions bulan ini
            $table->unsignedSmallInteger('total_orders')->default(0);
            $table->unsignedSmallInteger('on_time_deliveries')->default(0);
            $table->unsignedSmallInteger('late_deliveries')->default(0);
            $table->unsignedSmallInteger('partial_deliveries')->default(0);
            $table->decimal('total_qty_ordered', 15, 2)->default(0);
            $table->decimal('total_qty_received', 15, 2)->default(0);
            $table->decimal('total_qty_returned', 15, 2)->default(0);

            // Calculated scores (0-100)
            $table->decimal('delivery_score', 5, 2)->default(0);    // On-time %
            $table->decimal('quantity_score', 5, 2)->default(0);    // Qty accuracy %
            $table->decimal('quality_score', 5, 2)->default(0);     // 100 - return %
            $table->decimal('price_score', 5, 2)->default(0);       // vs market price
            $table->decimal('overall_score', 5, 2)->default(0);     // Weighted average

            // Grade
            $table->enum('grade', ['A', 'B', 'C', 'D', 'F'])->nullable();

            $table->timestamps();
            $table->unique(['supplier_id', 'year', 'month']);
        });

        // Tambah kolom ke goods_receipt_items untuk tracking delivery date vs expected
        Schema::table('goods_receipt_items', function (Blueprint $table) {
            $table->boolean('is_returned')->default(false)->after('notes');
            $table->decimal('qty_returned', 15, 2)->default(0)->after('is_returned');
            $table->enum('quality_status', ['ok', 'partial_reject', 'full_reject'])
                  ->default('ok')->after('qty_returned');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_scores');
        Schema::table('goods_receipt_items', function (Blueprint $table) {
            $table->dropColumn(['is_returned', 'qty_returned', 'quality_status']);
        });
    }
};
