<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('bank_account_id')->constrained('bank_accounts');
            $table->string('transaction_type'); // deposit, withdrawal, transfer_in, transfer_out, revenue, expense, loan_deduction
            $table->string('description');
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('credit', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->default(0);
            $table->string('reference_number')->nullable();
            // Polymorphic relation to source (revenue, expense, bill payment, etc.)
            $table->nullableMorphs('transactionable');
            // For bank transfers
            $table->foreignId('transfer_to_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->boolean('is_manual')->default(false);
            $table->foreignId('created_by')->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
