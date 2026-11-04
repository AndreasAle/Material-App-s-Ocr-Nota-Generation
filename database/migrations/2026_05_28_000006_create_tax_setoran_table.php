<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_setoran', function (Blueprint $table) {
            $table->id();
            $table->string('periode', 7)->comment('Format: YYYY-MM');
            $table->enum('jenis_pajak', ['ppn', 'pph21', 'pph23', 'pph_final_4_2']);
            $table->string('kap', 10)->comment('Kode Akun Pajak resmi DJP');
            $table->string('kjs', 10)->comment('Kode Jenis Setoran resmi DJP');
            $table->string('uraian')->nullable()->comment('Deskripsi pembayaran');
            $table->decimal('jumlah', 15, 2)->default(0)->comment('Jumlah yang harus disetor');
            $table->date('jatuh_tempo')->comment('Batas akhir penyetoran');
            $table->enum('status', ['belum_bayar', 'sudah_bayar'])->default('belum_bayar');
            $table->date('tanggal_setor')->nullable();
            $table->string('ntpn', 20)->nullable()
                  ->comment('Nomor Transaksi Penerimaan Negara dari bukti setor Coretax');
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_setoran');
    }
};
