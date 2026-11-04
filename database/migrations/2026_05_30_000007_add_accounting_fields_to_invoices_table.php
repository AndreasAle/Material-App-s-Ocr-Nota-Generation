<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Dasar Pengenaan Pajak (setelah diskon, sebelum PPN)
            $table->decimal('dpp', 15, 2)->default(0)->after('discount_amount');
            // PPH Final (untuk jasa konstruksi, biasanya 2.65% atau 3%)
            $table->decimal('pph_final_rate', 5, 2)->default(0)->after('tax_amount');
            $table->decimal('pph_final_amount', 15, 2)->default(0)->after('pph_final_rate');
            // Accounting / posting fields
            $table->string('doc_status', 20)->default('draft')->after('payment_status'); // draft/posted/void
            $table->timestamp('posted_at')->nullable()->after('updated_at');
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete()->after('posted_at');
            $table->foreignId('journal_entry_id')->nullable()->after('posted_by');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'dpp', 'pph_final_rate', 'pph_final_amount',
                'doc_status', 'posted_at', 'posted_by', 'journal_entry_id',
            ]);
        });
    }
};
