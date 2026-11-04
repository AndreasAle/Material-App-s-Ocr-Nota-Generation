<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Material Request — tambah budget check result
        Schema::table('material_requests', function (Blueprint $table) {
            $table->decimal('total_estimated', 15, 2)->default(0)->after('notes');
            $table->enum('budget_status', ['not_checked', 'within_budget', 'over_budget', 'no_rab'])
                  ->default('not_checked')->after('total_estimated');
            $table->decimal('budget_available', 15, 2)->nullable()->after('budget_status');
            $table->boolean('budget_override')->default(false)->after('budget_available');
            $table->text('budget_override_reason')->nullable()->after('budget_override');
        });

        // Purchase Order — tambah budget check
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->enum('budget_status', ['not_checked', 'within_budget', 'over_budget', 'no_rab'])
                  ->default('not_checked')->after('grand_total');
            $table->decimal('budget_available', 15, 2)->nullable()->after('budget_status');
            $table->boolean('budget_override')->default(false)->after('budget_available');
            $table->text('budget_override_reason')->nullable()->after('budget_override');

            // Link ke MR asal (jika dibuat dari MR)
            $table->foreignId('material_request_id')
                  ->nullable()->after('created_by')
                  ->constrained('material_requests')->nullOnDelete();

            // Link ke RFQ asal (jika dibuat dari RFQ award)
            $table->foreignId('rfq_id')
                  ->nullable()->after('material_request_id')
                  ->constrained('rfqs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('material_requests', function (Blueprint $table) {
            $table->dropColumn(['total_estimated', 'budget_status', 'budget_available',
                                'budget_override', 'budget_override_reason']);
        });
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['budget_status', 'budget_available', 'budget_override', 'budget_override_reason']);
            $table->dropConstrainedForeignId('material_request_id');
            $table->dropConstrainedForeignId('rfq_id');
        });
    }
};
