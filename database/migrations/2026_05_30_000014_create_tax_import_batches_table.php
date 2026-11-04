<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel tracking untuk setiap batch import file Excel DJP.
 * Mendukung: BPPU (PPh Unifikasi), Dok Lain PPN, dan BP21.
 * Menyimpan hasil validasi dan error sehingga user bisa review sebelum commit.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_import_batches', function (Blueprint $table) {
            $table->id();

            // Jenis dokumen yang diimport
            $table->enum('jenis', ['bppu', 'dok_lain_masukan', 'dok_lain_keluaran', 'bp21'])
                  ->comment('Jenis dokumen pajak yang diimport');

            // Identitas pemotong/PKP dari header file DJP
            $table->string('npwp_pemotong', 20)->nullable()
                  ->comment('NPWP Pemotong dari header file Excel DJP');
            $table->string('nama_pemotong')->nullable();

            // Info file
            $table->string('filename')->comment('Nama file asli yang diupload');
            $table->string('masa_pajak', 7)->nullable()
                  ->comment('Masa pajak dari file (YYYY-MM) jika ada di header');

            // Statistik import
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('success_rows')->default(0);
            $table->unsignedInteger('error_rows')->default(0);
            $table->unsignedInteger('duplicate_rows')->default(0);

            // Status
            $table->enum('status', ['preview', 'committed', 'failed'])
                  ->default('preview')
                  ->comment('preview = belum commit, committed = sudah disimpan ke DB');

            // Error details (JSON array of {row, field, message})
            $table->json('errors')->nullable()
                  ->comment('Detail error per baris, max 100 error pertama');

            // Preview data (JSON) sebelum commit — untuk tampil ke user
            $table->longText('preview_data')->nullable()
                  ->comment('JSON preview rows sebelum commit, dihapus setelah commit');

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['jenis', 'status']);
            $table->index('masa_pajak');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_import_batches');
    }
};
