<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rfqs', function (Blueprint $table) {
            $table->id();
            $table->string('rfq_no')->unique();                             // RFQ-202506-0001
            $table->foreignId('material_request_id')
                  ->nullable()->constrained()->nullOnDelete();              // Asal dari MR
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->date('deadline');                                       // Batas penawaran masuk
            $table->text('notes')->nullable();
            $table->text('terms_and_conditions')->nullable();

            $table->enum('status', ['draft', 'sent', 'closed', 'awarded', 'cancelled'])
                  ->default('draft');

            // Pemenang yang dipilih
            $table->foreignId('awarded_supplier_id')
                  ->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('awarded_response_id')
                  ->nullable();                                             // FK ke rfq_responses
            $table->timestamp('awarded_at')->nullable();
            $table->foreignId('awarded_by')->nullable()->constrained('users')->nullOnDelete();

            // Konversi ke PO
            $table->foreignId('converted_po_id')
                  ->nullable()->constrained('purchase_orders')->nullOnDelete();

            $table->timestamps();
        });

        Schema::create('rfq_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->nullable()->constrained()->nullOnDelete();
            $table->string('material_name_snapshot');
            $table->string('unit_name_snapshot');
            $table->decimal('qty_required', 15, 2);
            $table->decimal('budget_price', 15, 2)->nullable();             // Harga acuan dari RAB
            $table->text('specification')->nullable();
            $table->timestamps();
        });

        Schema::create('rfq_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_id')->constrained()->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();

            $table->enum('status', ['pending', 'submitted', 'accepted', 'rejected'])
                  ->default('pending');

            $table->date('valid_until')->nullable();
            $table->string('supplier_ref_no')->nullable();
            $table->enum('payment_terms', ['cash', 'net7', 'net14', 'net30', 'net45', 'net60', 'dp50', 'dp30', 'custom'])
                  ->default('net30');
            $table->unsignedSmallInteger('delivery_days')->nullable();      // Estimasi hari pengiriman
            $table->text('notes')->nullable();
            $table->decimal('total_price', 15, 2)->default(0);

            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('rfq_response_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rfq_response_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rfq_item_id')->constrained()->cascadeOnDelete();
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // FK awarded_response_id yang defer (setelah rfq_responses terbuat)
        Schema::table('rfqs', function (Blueprint $table) {
            $table->foreign('awarded_response_id')
                  ->references('id')->on('rfq_responses')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rfqs', function (Blueprint $table) {
            $table->dropForeign(['awarded_response_id']);
        });
        Schema::dropIfExists('rfq_response_items');
        Schema::dropIfExists('rfq_responses');
        Schema::dropIfExists('rfq_items');
        Schema::dropIfExists('rfqs');
    }
};
