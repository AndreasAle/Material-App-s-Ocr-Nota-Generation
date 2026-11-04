<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Melengkapi tabel Scan Nota. Kolom & tabel ini dulu hanya ada di database laptop
 * (dibuat manual), sehingga instalasi baru (server) gagal saat upload nota.
 * Semua langkah dilewati bila sudah ada.
 */
return new class extends Migration {
    public function up(): void
    {
        // ── purchase_scan_drafts ─────────────────────────────────────────
        Schema::table('purchase_scan_drafts', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_scan_drafts', 'progress')) {
                $table->unsignedTinyInteger('progress')->default(0)->after('status');
            }
            if (! Schema::hasColumn('purchase_scan_drafts', 'progress_message')) {
                $table->string('progress_message')->nullable()->after('progress');
            }
            if (! Schema::hasColumn('purchase_scan_drafts', 'error_message')) {
                $table->text('error_message')->nullable()->after('progress_message');
            }
            if (! Schema::hasColumn('purchase_scan_drafts', 'result_ready')) {
                $table->boolean('result_ready')->default(false)->after('error_message');
            }
        });

        // Status dulu enum 5 nilai, padahal kode memakai juga "processing"
        Schema::table('purchase_scan_drafts', function (Blueprint $table) {
            $table->string('status', 30)->default('uploaded')->change();
        });

        // ── purchase_scan_batch_files ────────────────────────────────────
        if (! Schema::hasTable('purchase_scan_batch_files')) {
            Schema::create('purchase_scan_batch_files', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_scan_batch_id')->constrained('purchase_scan_batches')->cascadeOnDelete();
                $table->string('file_path');
                $table->string('file_name')->nullable();
                $table->unsignedBigInteger('file_size')->default(0);
                $table->string('mime_type')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->string('status', 30)->default('uploaded');
                $table->longText('raw_text')->nullable();
                $table->json('parsed_json')->nullable();
                $table->string('detected_note_number')->nullable();
                $table->date('detected_transaction_date')->nullable();
                $table->string('detected_supplier_name')->nullable();
                $table->string('detected_supplier_name_normalized')->nullable();
                $table->bigInteger('grand_total')->default(0);
                $table->text('error_message')->nullable();
                $table->timestamps();

                $table->index(['purchase_scan_batch_id', 'sort_order'], 'psbf_batch_sort_idx');
            });
        }
    }

    public function down(): void
    {
        // Sengaja kosong: kolom/tabel ini dipakai fitur Scan Nota.
    }
};
