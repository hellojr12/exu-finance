<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revenue_entries', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('reference_number')->unique()->nullable();
            $table->string('event_name');
            $table->foreignId('event_category_id')->constrained('event_categories');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method'); // cash, bank_transfer, paymongo, check, gcash, maya
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_posted')->default(true); // auto-post to bank transactions
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revenue_entries');
    }
};
