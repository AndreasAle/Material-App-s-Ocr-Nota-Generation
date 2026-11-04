<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiscal_year_id')->constrained('fiscal_years')->cascadeOnDelete();
            $table->tinyInteger('period_month'); // 1-12
            $table->smallInteger('period_year');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 20)->default('open'); // open / closed
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['fiscal_year_id', 'period_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_periods');
    }
};
