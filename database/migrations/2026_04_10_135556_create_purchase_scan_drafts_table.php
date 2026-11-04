<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_scan_drafts', function (Blueprint $table) {
            $table->id();
            $table->string('file_path');
            $table->string('file_name')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->string('mime_type')->nullable();

            $table->longText('raw_text')->nullable();
            $table->json('parsed_json')->nullable();

            $table->string('detected_note_number')->nullable();
            $table->date('detected_transaction_date')->nullable();

            $table->enum('status', ['uploaded', 'processed', 'reviewed', 'imported', 'failed'])->default('uploaded');

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_scan_drafts');
    }
};