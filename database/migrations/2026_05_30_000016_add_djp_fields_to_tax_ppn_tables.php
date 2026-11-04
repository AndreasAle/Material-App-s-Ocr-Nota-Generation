<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom DJP Coretax ke tabel PPN Masukan dan Keluaran.
 * Diperlukan untuk import/export Dokumen Lain PPN ke format Coretax.
 * Format referensi: Template "Create Dok Lain Masukan/Keluaran" DJP.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ─── tax_ppn_masukan ─────────────────────────────────────────────────
        Schema::table('tax_ppn_masukan', function (Blueprint $table) {
            // Jenis transaksi untuk Dok Lain (FM = Faktur Masukan, DL = Dokumen Lain, etc.)
            $table->string('kode_transaksi', 5)->nullable()->after('no_faktur')
                  ->comment('Kode transaksi DJP: 01-07 untuk Faktur, DL untuk Dok Lain');
            $table->string('kode_dokumen_lain', 10)->nullable()->after('kode_transaksi')
                  ->comment('Kode jenis dokumen lain DJP jika bukan faktur standar');

            // Alamat penjual (untuk Dok Lain)
            $table->string('alamat_penjual')->nullable()->after('nama_penjual')
                  ->comment('Alamat penjual sesuai dokumen');

            // PPnBM
            $table->decimal('ppnbm', 15, 2)->default(0)->after('ppn')
                  ->comment('PPnBM jika ada');

            // Nomor dokumen pengganti (untuk retur/pengganti)
            $table->string('no_dokumen_pengganti', 32)->nullable()->after('status')
                  ->comment('No faktur/dok pengganti jika status = diganti');

            // Tracking impor
            $table->enum('sumber', ['manual', 'import_excel'])->default('manual')->after('created_by');
            $table->foreignId('import_batch_id')->nullable()->after('sumber')
                  ->constrained('tax_import_batches')->nullOnDelete();
        });

        // ─── tax_ppn_keluaran ────────────────────────────────────────────────
        Schema::table('tax_ppn_keluaran', function (Blueprint $table) {
            // Kode transaksi
            $table->string('kode_transaksi', 5)->nullable()->after('no_faktur')
                  ->comment('Kode transaksi DJP: 01=Dalam Negeri, 02=Pemungut, 07=DPP Nilai Lain, dll');
            $table->string('kode_dokumen_lain', 10)->nullable()->after('kode_transaksi')
                  ->comment('Kode jenis dokumen lain DJP');

            // Info tambahan (keterangan tambahan untuk dok lain)
            $table->string('keterangan_tambahan')->nullable()->after('keterangan')
                  ->comment('Info tambahan sesuai kolom Add Info di template DJP');

            // PPnBM
            $table->decimal('ppnbm', 15, 2)->default(0)->after('ppn')
                  ->comment('PPnBM jika ada');

            // Nomor dokumen pengganti
            $table->string('no_dokumen_pengganti', 32)->nullable()->after('status');

            // Tracking impor
            $table->enum('sumber', ['manual', 'import_excel'])->default('manual')->after('created_by');
            $table->foreignId('import_batch_id')->nullable()->after('sumber')
                  ->constrained('tax_import_batches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tax_ppn_masukan', function (Blueprint $table) {
            $table->dropConstrainedForeignId('import_batch_id');
            $table->dropColumn([
                'kode_transaksi', 'kode_dokumen_lain', 'alamat_penjual',
                'ppnbm', 'no_dokumen_pengganti', 'sumber',
            ]);
        });

        Schema::table('tax_ppn_keluaran', function (Blueprint $table) {
            $table->dropConstrainedForeignId('import_batch_id');
            $table->dropColumn([
                'kode_transaksi', 'kode_dokumen_lain', 'keterangan_tambahan',
                'ppnbm', 'no_dokumen_pengganti', 'sumber',
            ]);
        });
    }
};
