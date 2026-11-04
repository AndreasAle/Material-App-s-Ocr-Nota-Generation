<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_order_items', 'qty_received')) {
                $table->decimal('qty_received', 15, 2)->default(0)->after('subtotal');
            }
            if (!Schema::hasColumn('purchase_order_items', 'qty_pending')) {
                $table->decimal('qty_pending', 15, 2)->default(0)->after('qty_received');
            }
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_orders', 'delivery_status')) {
                $table->enum('delivery_status', ['not_started', 'partial', 'complete'])
                      ->default('not_started')->after('status');
            }
            if (!Schema::hasColumn('purchase_orders', 'supplier_ref_no')) {
                $table->string('supplier_ref_no')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('purchase_orders', 'supplier_confirmed_at')) {
                $table->timestamp('supplier_confirmed_at')->nullable()->after('supplier_ref_no');
            }
            if (!Schema::hasColumn('purchase_orders', 'sent_at')) {
                $table->timestamp('sent_at')->nullable()->after('supplier_confirmed_at');
            }
            if (!Schema::hasColumn('purchase_orders', 'payment_terms')) {
                $table->enum('payment_terms', ['cash', 'net7', 'net14', 'net30', 'net45', 'net60', 'dp50', 'dp30', 'custom'])
                      ->default('net30')->after('supplier_confirmed_at');
            }
            if (!Schema::hasColumn('purchase_orders', 'dp_percent')) {
                $table->unsignedTinyInteger('dp_percent')->default(0)->after('payment_terms');
            }
            if (!Schema::hasColumn('purchase_orders', 'payment_due_days')) {
                $table->unsignedSmallInteger('payment_due_days')->default(30)->after('dp_percent');
            }
            if (!Schema::hasColumn('purchase_orders', 'delivery_terms')) {
                $table->text('delivery_terms')->nullable()->after('payment_due_days');
            }
            if (!Schema::hasColumn('purchase_orders', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()->after('delivery_terms');
            }
            if (!Schema::hasColumn('purchase_orders', 'closed_by')) {
                $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('purchase_orders', 'close_reason')) {
                $table->string('close_reason')->nullable()->after('closed_by');
            }
            if (Schema::hasColumn('purchase_orders', 'converted_transaction_id')) {
                $table->dropConstrainedForeignId('converted_transaction_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->dropColumn(['qty_received', 'qty_pending']);
        });
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_status', 'supplier_ref_no', 'supplier_confirmed_at', 'sent_at',
                                'payment_terms', 'dp_percent', 'payment_due_days', 'delivery_terms',
                                'closed_at', 'close_reason']);
            $table->dropConstrainedForeignId('closed_by');
            $table->foreignId('converted_transaction_id')->nullable()
                  ->constrained('purchase_transactions')->nullOnDelete();
        });
    }
};
