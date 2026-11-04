<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('client_name', 255)->nullable()->after('description');
            $table->date('start_date')->nullable()->after('client_name');
            $table->date('end_date')->nullable()->after('start_date');
            $table->decimal('contract_value', 15, 2)->default(0)->after('end_date');
            $table->string('status', 20)->default('active')->after('contract_value'); // active/completed/suspended
            $table->tinyInteger('progress_pct')->default(0)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn(['client_name', 'start_date', 'end_date', 'contract_value', 'status', 'progress_pct']);
        });
    }
};
