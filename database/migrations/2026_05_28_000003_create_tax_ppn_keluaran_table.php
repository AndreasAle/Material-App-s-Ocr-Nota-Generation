<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_ppn_keluaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->nullable()
                  ->constrained('invoices')->nullOnDelete()
                  ->comment('Opsional: link ke invoice');
            $table->string('no_faktur', 32)->nullable()
                  ->comment('NSFP (Nomor Seri Faktur Pajak) dari DJP via Coretax');
            $table->date('tanggal_faktur');
            $table->string('npwp_pembeli', 20)->nullable();
            $table->string('nama_pembeli');
            $table->string('alamat_pembeli')->nullable();
            $table->decimal('dpp', 15, 2)->default(0)->comment('Dasar Pengenaan Pajak');
            $table->decimal('ppn', 15, 2)->default(0)->comment('PPN 11% dari DPP');
            $table->string('periode', 7)->comment('Format: YYYY-MM');
            $table->enum('status', ['normal', 'retur'])->default('normal');
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_ppn_keluaran');
    }
};
