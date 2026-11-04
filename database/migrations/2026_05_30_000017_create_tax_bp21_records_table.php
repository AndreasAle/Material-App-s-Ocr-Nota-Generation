<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel Bukti Pemotongan PPh Pasal 21 (BP21).
 * Format referensi: Template "BP21 Excel to XML v.4.xlsx" dari DJP.
 * Regulasi: PMK No. 168/PMK.03/2023 — tarif TER berlaku mulai 1 Jan 2024.
 *
 * Mencakup:
 * - Pegawai Tetap (tarif TER / PS17)
 * - Pegawai Tidak Tetap Harian
 * - Pegawai Tidak Tetap Bulanan
 * - Pesangon
 * - Pensiun
 * - Bukan Pegawai (honorarium, jasa, dll)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_bp21_records', function (Blueprint $table) {
            $table->id();

            // ── Nomor & periode bukti potong ─────────────────────────────────
            $table->string('no_bukti_potong')->nullable()
                  ->comment('Nomor Bukti Potong, generated atau input manual');
            $table->string('masa_pajak', 7)
                  ->comment('Masa pajak format YYYY-MM');
            $table->unsignedSmallInteger('tahun_pajak')
                  ->comment('Tahun pajak, misal 2025');
            $table->unsignedTinyInteger('pembetulan')->default(0)
                  ->comment('0 = normal, 1+ = pembetulan ke-N');

            // ── Identitas penerima penghasilan ───────────────────────────────
            $table->string('npwp_penerima', 16)->nullable()
                  ->comment('NPWP 16-digit (format Coretax)');
            $table->string('nik_penerima', 16)->nullable()
                  ->comment('NIK untuk orang pribadi');
            $table->string('nama_penerima')
                  ->comment('Nama lengkap penerima penghasilan');
            $table->string('id_tku_penerima')->nullable()
                  ->comment('ID Tempat Kegiatan Usaha Penerima');

            // ── Status PTKP (untuk pegawai) ──────────────────────────────────
            $table->string('status_ptkp', 10)->nullable()
                  ->comment('TK/0, TK/1, K/0, K/1, K/2, K/3, HB/0, dll');

            // ── Kode Objek Pajak ─────────────────────────────────────────────
            $table->string('kode_objek_pajak', 20)
                  ->comment('Kode Objek Pajak, mis: 21-100-01 (Pegawai Tetap)');
            $table->string('nama_objek_pajak')
                  ->comment('Nama objek pajak sesuai KOP');

            // ── Jenis tarif & penghasilan ────────────────────────────────────
            $table->enum('jenis_tarif', ['TER', 'PS17', 'HARIAN', 'PESANGON', 'PENSIUN', 'LAINNYA'])
                  ->default('TER')
                  ->comment('Jenis metode tarif PPh 21');
            $table->decimal('penghasilan_bruto', 15, 2)->default(0)
                  ->comment('Penghasilan bruto sebelum dipotong PPh');
            $table->decimal('deemed_percent', 5, 2)->nullable()
                  ->comment('Persentase deemed income jika berlaku (Bukan Pegawai)');
            $table->decimal('tarif', 5, 2)->default(0)
                  ->comment('Tarif PPh 21 yang berlaku (%)');
            $table->decimal('pph_dipotong', 15, 2)->default(0)
                  ->comment('PPh 21 yang dipotong');

            // ── Referensi dokumen ────────────────────────────────────────────
            $table->string('jenis_dok_referensi')->nullable()
                  ->comment('Jenis dokumen referensi: KONTRAK, INVOICE, SK, dll');
            $table->string('no_dok_referensi')->nullable()
                  ->comment('Nomor dokumen referensi');
            $table->date('tanggal_dok_referensi')->nullable()
                  ->comment('Tanggal dokumen referensi');

            // ── Data pemotong ────────────────────────────────────────────────
            $table->string('id_tku_pemotong')->nullable()
                  ->comment('ID Tempat Kegiatan Usaha Pemotong');
            $table->date('tanggal_pemotongan')->nullable()
                  ->comment('Tanggal dilakukan pemotongan PPh 21');

            // ── Link ke data internal ────────────────────────────────────────
            $table->foreignId('project_id')->nullable()
                  ->constrained('projects')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()
                  ->constrained('invoices')->nullOnDelete();

            // ── Status & setoran ─────────────────────────────────────────────
            $table->enum('status', ['belum_disetor', 'sudah_disetor'])->default('belum_disetor');
            $table->date('tanggal_setor')->nullable();
            $table->string('ntpn', 20)->nullable()
                  ->comment('Nomor Transaksi Penerimaan Negara dari Coretax');

            // ── Fasilitas & keterangan ───────────────────────────────────────
            $table->string('fasilitas')->nullable()
                  ->comment('Kode fasilitas pajak jika ada (SKB/PPh, dll)');
            $table->text('keterangan')->nullable();

            // ── Tracking ─────────────────────────────────────────────────────
            $table->enum('sumber', ['manual', 'import_excel'])->default('manual')
                  ->comment('Sumber data: manual atau import Excel DJP');
            $table->foreignId('import_batch_id')->nullable()
                  ->constrained('tax_import_batches')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Indexes
            $table->index('masa_pajak');
            $table->index('kode_objek_pajak');
            $table->index('npwp_penerima');
            $table->unique('no_bukti_potong', 'uniq_bp21_bukpot');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_bp21_records');
    }
};
