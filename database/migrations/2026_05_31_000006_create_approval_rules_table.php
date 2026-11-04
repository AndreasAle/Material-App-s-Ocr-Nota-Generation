<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_rules', function (Blueprint $table) {
            $table->id();

            // Tipe dokumen: purchase_order, purchase_transaction, material_request, rfq, return
            $table->string('document_type');

            // Threshold nilai (rupiah)
            $table->decimal('amount_min', 15, 2)->default(0);
            $table->decimal('amount_max', 15, 2)->nullable();   // null = unlimited

            // Role yang bisa approve
            $table->string('approver_role');    // admin, pimpinan, direktur, komisaris
            $table->string('approver_label');   // Label untuk UI: "Kepala Proyek", "Direktur Operasional"

            $table->unsignedTinyInteger('level')->default(1);   // Level 1, 2, 3
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Seed default rules
        DB::table('approval_rules')->insert([
            // Purchase Order
            ['document_type' => 'purchase_order', 'amount_min' => 0,          'amount_max' => 5000000,    'approver_role' => 'admin',    'approver_label' => 'Admin / Kepala Proyek',    'level' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['document_type' => 'purchase_order', 'amount_min' => 5000001,    'amount_max' => 50000000,   'approver_role' => 'pimpinan', 'approver_label' => 'Manajer Operasional',       'level' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['document_type' => 'purchase_order', 'amount_min' => 50000001,   'amount_max' => 500000000,  'approver_role' => 'pimpinan', 'approver_label' => 'Direktur',                  'level' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['document_type' => 'purchase_order', 'amount_min' => 500000001,  'amount_max' => null,       'approver_role' => 'pimpinan', 'approver_label' => 'Komisaris / BOD',           'level' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            // Purchase Transaction
            ['document_type' => 'purchase_transaction', 'amount_min' => 0,    'amount_max' => 10000000,   'approver_role' => 'admin',    'approver_label' => 'Admin',                    'level' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['document_type' => 'purchase_transaction', 'amount_min' => 10000001, 'amount_max' => null,   'approver_role' => 'pimpinan', 'approver_label' => 'Pimpinan',                  'level' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            // Material Request
            ['document_type' => 'material_request', 'amount_min' => 0,        'amount_max' => null,       'approver_role' => 'admin',    'approver_label' => 'Admin / Kepala Proyek',    'level' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            // RFQ
            ['document_type' => 'rfq',             'amount_min' => 0,         'amount_max' => null,       'approver_role' => 'admin',    'approver_label' => 'Admin',                    'level' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            // Purchase Return
            ['document_type' => 'purchase_return', 'amount_min' => 0,         'amount_max' => null,       'approver_role' => 'admin',    'approver_label' => 'Admin',                    'level' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_rules');
    }
};
