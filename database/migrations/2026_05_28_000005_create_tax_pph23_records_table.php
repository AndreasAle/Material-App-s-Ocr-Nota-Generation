<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_pph23_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_transaction_id')->nullable()
                  ->constrained('purchase_transactions')->nullOnDelete()
                  ->comment('Link ke transaksi pembelian jasa');
            $table->string('nama_penerima')->comment('Nama subkontraktor / vendor jasa');
            $table->string('npwp_penerima', 20)->nullable()
                  ->comment('Jika kosong, tarif menjadi 4% (double tarif)');
            $table->string('jenis_penghasilan')
                  ->comment('Misal: Jasa Konstruksi, Jasa Teknik, Jasa Manajemen, dll');
            $table->decimal('bruto', 15, 2)->default(0)->comment('Nilai jasa sebelum dipotong PPh');
            $table->decimal('tarif', 5, 2)->default(2)
                  ->comment('2% jika punya NPWP, 4% jika tidak - UU PPh Pasal 23');
            $table->decimal('pph_dipotong', 15, 2)->default(0)->comment('bruto x tarif/100');
            $table->string('no_bukti_potong')->nullable();
            $table->date('tanggal_bukti_potong')->nullable();
            $table->string('periode', 7)->comment('Format: YYYY-MM');
            $table->enum('status', ['belum_disetor', 'sudah_disetor'])->default('belum_disetor');
            $table->date('tanggal_setor')->nullable();
            $table->string('ntpn', 20)->nullable()->comment('Nomor Transaksi Penerimaan Negara dari Coretax');
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_pph23_records');
    }
};
