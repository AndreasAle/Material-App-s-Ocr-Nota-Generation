<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_pkp')->default(false)->comment('Status PKP - wajib jika omzet > 4,8M/tahun');
            $table->enum('kualifikasi_konstruksi', ['kecil', 'menengah', 'besar', 'tidak_bersertifikat'])
                  ->default('tidak_bersertifikat')->comment('Kualifikasi SBU sesuai PP 9/2022');
            $table->enum('jenis_usaha', ['pelaksana', 'perencana', 'pengawas', 'konsultansi'])
                  ->default('pelaksana')->comment('Jenis usaha jasa konstruksi');
            $table->string('npwp', 20)->nullable()->comment('Format: XX.XXX.XXX.X-XXX.XXX');
            $table->string('nama_wp')->nullable()->comment('Nama Wajib Pajak');
            $table->text('alamat_wp')->nullable();
            $table->string('klu', 10)->nullable()->comment('Klasifikasi Lapangan Usaha DJP');
            $table->string('kpp')->nullable()->comment('Kode KPP terdaftar, contoh: KPP Pratama Jakarta Selatan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_settings');
    }
};
