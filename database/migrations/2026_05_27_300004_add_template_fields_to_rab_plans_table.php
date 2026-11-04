<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rab_plans', function (Blueprint $table) {
            $table->boolean('is_template')->default(false)->after('is_latest_version');
            $table->string('template_name')->nullable()->after('is_template');
        });
    }

    public function down(): void
    {
        Schema::table('rab_plans', function (Blueprint $table) {
            $table->dropColumn(['is_template', 'template_name']);
        });
    }
};
