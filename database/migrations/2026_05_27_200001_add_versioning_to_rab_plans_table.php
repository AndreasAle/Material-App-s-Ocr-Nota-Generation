<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rab_plans', function (Blueprint $table) {
            $table->unsignedInteger('version')->default(1)->after('status');
            $table->foreignId('original_plan_id')
                  ->nullable()
                  ->after('version')
                  ->constrained('rab_plans')
                  ->nullOnDelete();
            $table->boolean('is_latest_version')->default(true)->after('original_plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('rab_plans', function (Blueprint $table) {
            $table->dropForeign(['original_plan_id']);
            $table->dropColumn(['version', 'original_plan_id', 'is_latest_version']);
        });
    }
};
