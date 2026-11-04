<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subcontracts', function (Blueprint $table) {
            $table->id();
            $table->string('spk_no')->unique(); // nomor SPK
            $table->foreignId('subcontractor_id')->constrained('subcontractors')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('work_name'); // nama pekerjaan
            $table->text('scope_of_work')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('contract_value', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->tinyInteger('progress_pct')->default(0); // 0-100
            $table->enum('status', ['draft','active','completed','cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subcontracts');
    }
};
