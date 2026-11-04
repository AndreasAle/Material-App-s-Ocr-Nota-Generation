<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('material_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_no', 50)->unique();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'converted'])->default('draft');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('converted_po_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            $table->enum('urgency', ['low', 'normal', 'high', 'critical'])->default('normal');
            $table->date('needed_by')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_auto_reorder')->default(false);
            $table->timestamps();

            $table->index(['project_id', 'status']);
            $table->index(['requested_by', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_requests');
    }
};
