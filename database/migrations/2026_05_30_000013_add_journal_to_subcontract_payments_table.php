<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subcontract_payments', function (Blueprint $table) {
            $table->foreignId('journal_entry_id')->nullable()->after('created_by');
        });
    }

    public function down(): void
    {
        Schema::table('subcontract_payments', function (Blueprint $table) {
            $table->dropColumn('journal_entry_id');
        });
    }
};
