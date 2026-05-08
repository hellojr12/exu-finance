<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_loans', function (Blueprint $table) {
            $table->id();
            $table->string('employee_name');
            $table->string('employee_id')->nullable();
            $table->string('department')->nullable();
            $table->string('position')->nullable();
            $table->decimal('loan_amount', 15, 2);
            $table->date('date_issued');
            $table->decimal('outstanding_balance', 15, 2);
            $table->string('deduction_type')->default('monthly'); // monthly, manual, one_time
            $table->decimal('monthly_deduction', 15, 2)->default(0);
            $table->string('status')->default('active'); // active, fully_paid, written_off
            $table->text('purpose')->nullable();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_loans');
    }
};
