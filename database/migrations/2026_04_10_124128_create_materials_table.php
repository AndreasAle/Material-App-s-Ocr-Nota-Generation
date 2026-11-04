<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained()->restrictOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->decimal('default_price', 15, 2)->default(0);
            $table->text('specification')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};