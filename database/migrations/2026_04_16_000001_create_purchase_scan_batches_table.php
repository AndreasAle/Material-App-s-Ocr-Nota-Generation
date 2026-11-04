<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_scan_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_code')->unique();
            $table->string('scan_mode')->default('multiple'); // single|multiple
            $table->string('status')->default('uploaded'); // uploaded|processing|processed|failed|reviewed|imported|supplier_mismatch
            $table->string('supplier_name')->nullable();
            $table->string('supplier_name_normalized')->nullable();
            $table->date('detected_transaction_date')->nullable();
            $table->unsignedInteger('total_files')->default(0);
            $table->unsignedInteger('total_items')->default(0);
            $table->bigInteger('grand_total')->default(0);
            $table->unsignedTinyInteger('progress')->default(0);
            $table->string('progress_message')->nullable();
            $table->text('error_message')->nullable();
            $table->boolean('result_ready')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->json('parsed_json')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_by']);
            $table->index('supplier_name_normalized');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_scan_batches');
    }
};