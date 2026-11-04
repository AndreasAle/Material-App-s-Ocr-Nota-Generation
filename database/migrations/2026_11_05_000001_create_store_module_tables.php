<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Modul Toko Material: harga jual, satuan ganda, pelanggan, penjualan (kasir), bon & retur penjualan.
 * Stok selalu disimpan dalam satuan dasar material (materials.unit_id).
 */
return new class extends Migration {
    public function up(): void
    {
        // reference_type dulu enum terbatas; retur pembelian, penjualan & retur penjualan butuh nilai baru
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->string('reference_type', 50)->default('manual_in')->change();
        });

        Schema::table('materials', function (Blueprint $table) {
            $table->decimal('sell_price', 15, 2)->default(0)->after('default_price');       // harga eceran / satuan dasar
            $table->decimal('wholesale_price', 15, 2)->nullable()->after('sell_price');     // harga grosir / satuan dasar
            $table->decimal('wholesale_min_qty', 15, 2)->nullable()->after('wholesale_price');
            $table->decimal('last_purchase_price', 15, 2)->nullable()->after('wholesale_min_qty'); // per satuan dasar
            $table->string('barcode')->nullable()->index()->after('code');
        });

        // Satuan tambahan: 1 <unit_name> = <factor> satuan dasar
        Schema::create('material_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->string('unit_name');
            $table->decimal('factor', 15, 4);
            $table->decimal('sell_price', 15, 2)->nullable(); // harga jual per satuan ini (kosong = factor × harga dasar)
            $table->timestamps();

            $table->unique(['material_id', 'unit_name']);
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 30)->nullable();
            $table->string('address')->nullable();
            $table->decimal('credit_limit', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('sale_no')->unique();
            $table->dateTime('sale_date');
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->decimal('cost_total', 15, 2)->default(0);      // HPP saat dijual
            $table->decimal('returned_total', 15, 2)->default(0);  // nilai yang sudah diretur
            $table->decimal('paid_amount', 15, 2)->default(0);     // pembayaran yang diakui (tanpa kembalian)
            $table->decimal('cash_received', 15, 2)->default(0);   // uang yang diserahkan pembeli
            $table->decimal('change_amount', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2)->default(0);
            $table->string('payment_method', 20)->default('cash'); // cash | transfer | bon
            $table->string('payment_status', 20)->default('paid'); // paid | partial | unpaid
            $table->date('due_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('sale_date');
            $table->index('payment_status');
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->nullable()->constrained()->nullOnDelete();
            $table->string('material_name_snapshot');
            $table->string('unit_name');
            $table->decimal('factor', 15, 4)->default(1);
            $table->decimal('qty', 15, 2);
            $table->decimal('qty_base', 15, 4);
            $table->decimal('price', 15, 2);
            $table->decimal('cost_price', 15, 2)->default(0); // HPP per satuan dasar
            $table->decimal('subtotal', 15, 2);
            $table->decimal('qty_returned', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('sale_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method', 20)->default('cash');
            $table->string('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('payment_date');
        });

        Schema::create('sale_returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_no')->unique();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->date('return_date');
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('cost_total', 15, 2)->default(0);
            $table->decimal('refund_amount', 15, 2)->default(0); // uang dikembalikan ke pembeli
            $table->string('reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('sale_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sale_item_id')->constrained()->cascadeOnDelete();
            $table->decimal('qty', 15, 2);
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_return_items');
        Schema::dropIfExists('sale_returns');
        Schema::dropIfExists('sale_payments');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('material_units');

        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn(['sell_price', 'wholesale_price', 'wholesale_min_qty', 'last_purchase_price', 'barcode']);
        });
    }
};
