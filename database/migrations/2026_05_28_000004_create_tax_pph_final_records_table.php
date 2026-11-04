<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_pph_final_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->nullable()
                  ->constrained('invoices')->nullOnDelete()
                  ->comment('Link ke invoice tagihan ke klien');
            $table->foreignId('project_id')->nullable()
                  ->constrained('projects')->nullOnDelete();
            $table->string('nama_pemotong')->comment('Nama klien / pemberi kerja yang memotong');
            $table->string('npwp_pemotong', 20)->nullable()->comment('NPWP klien/pemberi kerja');
            $table->string('no_bukti_potong')->nullable()->comment('Nomor bukti potong dari klien');
            $table->date('tanggal_bukti_potong')->nullable();
            // Jenis usaha & kualifikasi saat transaksi (bisa beda dari setting perusahaan)
            $table->enum('jenis_usaha', ['pelaksana', 'perencana', 'pengawas', 'konsultansi'])
                  ->default('pelaksana');
            $table->enum('kualifikasi', ['kecil', 'menengah', 'besar', 'tidak_bersertifikat'])
                  ->default('kecil');
            $table->decimal('nilai_kontrak', 15, 2)->default(0)
                  ->comment('DPP = nilai bruto kontrak');
            $table->decimal('tarif', 5, 2)->default(0)
                  ->comment('Tarif % sesuai PP No. 9 Tahun 2022');
            $table->decimal('pph_dipotong', 15, 2)->default(0)
                  ->comment('PPh yang dipotong klien = nilai_kontrak x tarif');
            $table->string('periode', 7)->comment('Format: YYYY-MM');
            $table->enum('status', ['belum_terima_bukpot', 'sudah_terima_bukpot'])
                  ->default('belum_terima_bukpot');
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_pph_final_records');
    }
};
