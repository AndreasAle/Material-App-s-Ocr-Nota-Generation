<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('chart_of_accounts')->restrictOnDelete();
            $table->string('account_code', 20);
            $table->string('account_name', 255);
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete(); // cost center
            $table->integer('line_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entry_lines');
    }
};
