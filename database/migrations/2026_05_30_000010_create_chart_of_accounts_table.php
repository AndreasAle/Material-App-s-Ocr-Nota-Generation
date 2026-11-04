<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 255);
            $table->string('account_type', 30);    // asset/liability/equity/revenue/expense
            $table->string('account_subtype', 50)->nullable(); // current_asset/fixed_asset/current_liability/etc
            $table->string('normal_balance', 6)->default('debit'); // debit / credit
            $table->foreignId('parent_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->tinyInteger('level')->default(1);           // 1=group, 2=sub, 3=detail
            $table->boolean('is_header')->default(false);       // header = tidak bisa di-posting
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
