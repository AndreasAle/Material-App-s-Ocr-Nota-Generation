<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('email', 255)->nullable()->after('phone');
            $table->string('npwp', 30)->nullable()->after('email');
            $table->boolean('is_pkp')->default(false)->after('npwp'); // Pengusaha Kena Pajak
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn(['email', 'npwp', 'is_pkp']);
        });
    }
};
