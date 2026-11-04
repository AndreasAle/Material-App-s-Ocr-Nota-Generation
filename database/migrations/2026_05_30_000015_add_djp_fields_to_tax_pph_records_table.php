<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom DJP Coretax ke tabel PPh Final (4(2)) dan PPh 23.
 * Diperlukan untuk import/export BPPU (Bukti Pemotongan PPh Unifikasi).
 * Regulasi: PMK No. 168/PMK.03/2023 — format Coretax.
 *
 * Depends on: 2026_05_30_000014_create_tax_import_batches_table.php
 */
return new class extends Migration
{
    public function up(): void
    {
        // ─── tax_pph_final_records ───────────────────────────────────────────
        Schema::table('tax_pph_final_records', function (Blueprint $table) {
            // Identitas penerima penghasilan
            $table->string('npwp_penerima', 16)->nullable()->after('npwp_pemotong')
                  ->comment('NPWP 16-digit penerima (format Coretax)');
            $table->string('nik_penerima', 16)->nullable()->after('npwp_penerima')
                  ->comment('NIK jika penerima orang pribadi tanpa NPWP');
            $table->string('nama_penerima')->nullable()->after('nik_penerima')
                  ->comment('Nama penerima penghasilan (biasanya nama perusahaan kita)');
            $table->string('id_tku_pemotong')->nullable()->after('nama_penerima')
                  ->comment('ID Tempat Kegiatan Usaha Pemotong');
            $table->string('id_tku_penerima')->nullable()->after('id_tku_pemotong')
                  ->comment('ID Tempat Kegiatan Usaha Penerima');

            // Kode Objek Pajak DJP
            $table->string('kode_objek_pajak', 20)->nullable()->after('kualifikasi')
                  ->comment('Kode Objek Pajak DJP, mis: 28-409-25 (Konstruksi)');
            $table->string('nama_objek_pajak')->nullable()->after('kode_objek_pajak')
                  ->comment('Nama objek pajak sesuai KOP, auto-fill dari referensi');

            // Data pemotongan tambahan
            $table->string('fasilitas')->nullable()->after('tarif')
                  ->comment('Kode fasilitas pajak (SKB/PPh, Bebas Dipungut, dll)');
            $table->decimal('deemed_percent', 5, 2)->nullable()->after('fasilitas')
                  ->comment('Persentase deemed income jika berlaku');
            $table->date('tanggal_pemotongan')->nullable()->after('tanggal_bukti_potong')
                  ->comment('Tanggal dilakukan pemotongan PPh (untuk BPPU)');

            // Referensi dokumen (kontrak / invoice / SPK)
            $table->string('jenis_dok_referensi')->nullable()->after('keterangan')
                  ->comment('Jenis dok referensi: KONTRAK, INVOICE, SPK, FAKTUR_PAJAK, dll');
            $table->string('no_dok_referensi')->nullable()->after('jenis_dok_referensi')
                  ->comment('Nomor dokumen referensi');
            $table->date('tanggal_dok_referensi')->nullable()->after('no_dok_referensi')
                  ->comment('Tanggal dokumen referensi');

            // Tracking impor
            $table->enum('sumber', ['manual', 'import_excel'])->default('manual')->after('created_by')
                  ->comment('Sumber data: manual input atau import dari Excel DJP');
            $table->foreignId('import_batch_id')->nullable()->after('sumber')
                  ->constrained('tax_import_batches')->nullOnDelete()
                  ->comment('FK ke batch import jika data dari Excel DJP');
        });

        // ─── tax_pph23_records ───────────────────────────────────────────────
        Schema::table('tax_pph23_records', function (Blueprint $table) {
            // Kode Objek Pajak DJP
            $table->string('kode_objek_pajak', 20)->nullable()->after('jenis_penghasilan')
                  ->comment('Kode Objek Pajak DJP, mis: 24-100-99, 24-104-01');
            $table->string('nama_objek_pajak')->nullable()->after('kode_objek_pajak')
                  ->comment('Nama objek pajak sesuai KOP');

            // Data BPPU tambahan
            $table->string('id_tku_pemotong')->nullable()->after('npwp_penerima')
                  ->comment('ID Tempat Kegiatan Usaha Pemotong');
            $table->string('id_tku_penerima')->nullable()->after('id_tku_pemotong')
                  ->comment('ID Tempat Kegiatan Usaha Penerima');
            $table->string('fasilitas')->nullable()->after('tarif')
                  ->comment('Kode fasilitas pajak');
            $table->decimal('deemed_percent', 5, 2)->nullable()->after('fasilitas')
                  ->comment('Persentase deemed income jika berlaku');
            $table->date('tanggal_pemotongan')->nullable()->after('tanggal_bukti_potong')
                  ->comment('Tanggal dilakukan pemotongan PPh');

            // Referensi dokumen
            $table->string('jenis_dok_referensi')->nullable()->after('keterangan')
                  ->comment('Jenis dokumen referensi');
            $table->string('no_dok_referensi')->nullable()->after('jenis_dok_referensi');
            $table->date('tanggal_dok_referensi')->nullable()->after('no_dok_referensi');

            // Tracking impor
            $table->enum('sumber', ['manual', 'import_excel'])->default('manual')->after('created_by');
            $table->foreignId('import_batch_id')->nullable()->after('sumber')
                  ->constrained('tax_import_batches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tax_pph_final_records', function (Blueprint $table) {
            $table->dropConstrainedForeignId('import_batch_id');
            $table->dropColumn([
                'npwp_penerima', 'nik_penerima', 'nama_penerima',
                'id_tku_pemotong', 'id_tku_penerima',
                'kode_objek_pajak', 'nama_objek_pajak',
                'fasilitas', 'deemed_percent', 'tanggal_pemotongan',
                'jenis_dok_referensi', 'no_dok_referensi', 'tanggal_dok_referensi',
                'sumber',
            ]);
        });

        Schema::table('tax_pph23_records', function (Blueprint $table) {
            $table->dropConstrainedForeignId('import_batch_id');
            $table->dropColumn([
                'kode_objek_pajak', 'nama_objek_pajak',
                'id_tku_pemotong', 'id_tku_penerima',
                'fasilitas', 'deemed_percent', 'tanggal_pemotongan',
                'jenis_dok_referensi', 'no_dok_referensi', 'tanggal_dok_referensi',
                'sumber',
            ]);
        });
    }
};
