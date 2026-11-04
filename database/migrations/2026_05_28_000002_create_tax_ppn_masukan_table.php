<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_ppn_masukan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_transaction_id')->nullable()
                  ->constrained('purchase_transactions')->nullOnDelete()
                  ->comment('Opsional: link ke transaksi pembelian');
            $table->foreignId('supplier_id')->nullable()
                  ->constrained('suppliers')->nullOnDelete();
            $table->string('no_faktur', 32)->nullable()->comment('Nomor seri faktur pajak dari penjual');
            $table->date('tanggal_faktur');
            $table->string('npwp_penjual', 20)->nullable();
            $table->string('nama_penjual');
            $table->decimal('dpp', 15, 2)->default(0)->comment('Dasar Pengenaan Pajak');
            $table->decimal('ppn', 15, 2)->default(0)->comment('PPN 11% dari DPP');
            $table->string('periode', 7)->comment('Format: YYYY-MM, misal 2026-05');
            $table->enum('status', ['normal', 'retur'])->default('normal');
            $table->boolean('dapat_dikreditkan')->default(true)->comment('Dapat dikreditkan sebagai PPN Masukan');
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_ppn_masukan');
    }
};
