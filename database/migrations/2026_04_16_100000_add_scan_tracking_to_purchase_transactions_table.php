<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_transactions', function (Blueprint $table) {
            $table->string('input_source')->default('manual')->after('attachment_count'); // manual|scanner
            $table->string('scan_source_type')->nullable()->after('input_source'); // single|multiple
            $table->unsignedBigInteger('scan_reference_id')->nullable()->after('scan_source_type'); // id draft/batch
        });
    }

    public function down(): void
    {
        Schema::table('purchase_transactions', function (Blueprint $table) {
            $table->dropColumn([
                'input_source',
                'scan_source_type',
                'scan_reference_id',
            ]);
        });
    }
};