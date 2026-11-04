<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fiscal_years', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('year');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status', 20)->default('open'); // open / closed
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fiscal_years');
    }
};
