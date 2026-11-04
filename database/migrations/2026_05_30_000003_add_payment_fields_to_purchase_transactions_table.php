<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_transactions', function (Blueprint $table) {
            $table->decimal('subtotal', 15, 2)->default(0)->after('grand_total');
            $table->decimal('discount_amount', 15, 2)->default(0)->after('subtotal');
            $table->decimal('dpp', 15, 2)->default(0)->after('discount_amount');         // Dasar Pengenaan Pajak
            $table->decimal('ppn_rate', 5, 2)->default(0)->after('dpp');                 // % PPN (11%)
            $table->decimal('ppn_amount', 15, 2)->default(0)->after('ppn_rate');
            $table->date('due_date')->nullable()->after('transaction_date');
            $table->decimal('paid_amount', 15, 2)->default(0)->after('ppn_amount');
            $table->decimal('remaining_amount', 15, 2)->default(0)->after('paid_amount');
            $table->string('payment_status', 20)->default('unpaid')->after('remaining_amount'); // unpaid/partial/paid
            $table->timestamp('posted_at')->nullable()->after('updated_at');
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete()->after('posted_at');
            $table->foreignId('journal_entry_id')->nullable()->after('posted_by');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_transactions', function (Blueprint $table) {
            $table->dropColumn([
                'subtotal', 'discount_amount', 'dpp', 'ppn_rate', 'ppn_amount',
                'due_date', 'paid_amount', 'remaining_amount', 'payment_status',
                'posted_at', 'posted_by', 'journal_entry_id',
            ]);
        });
    }
};
