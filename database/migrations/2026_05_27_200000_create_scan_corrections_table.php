<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scan_corrections', function (Blueprint $table) {
            $table->id();
            $table->string('source_text');
            $table->string('source_text_normalized', 500);
            $table->foreignId('material_id')->nullable()->nullOnDelete();
            $table->string('corrected_material_name');
            $table->string('corrected_unit_name')->nullable();
            $table->enum('correction_type', ['match_confirmed', 'match_override', 'deleted'])->default('match_override');
            $table->foreignId('corrected_by')->nullable()->nullOnDelete()->references('id')->on('users');
            $table->unsignedInteger('usage_count')->default(1);
            $table->timestamps();

            $table->index('source_text_normalized');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_corrections');
    }
};
