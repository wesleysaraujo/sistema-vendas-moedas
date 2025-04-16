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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('currency_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 4)->comment('Amount in currency');
            $table->decimal('amount_paid', 15, 4)->comment('Amount paid in local currency');
            $table->decimal('foreign_amount', 10, 2)->comment('Amount paid in local currency');
            $table->decimal('exchange_rate', 15, 8)->comment('Exchange rate at transaction time');
            $table->decimal('fee_percentage', 5, 2)->comment('Service fee percentage applied');
            $table->decimal('fee_amount', 15, 4)->comment('Service fee amount in local currency');
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->json('metadata')->nullable()->comment('Additional transaction data');
            $table->text('notes')->nullable();
            $table->string('reference_id')->nullable()->unique()->comment('External reference ID');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'created_at']);
            $table->index(['currency_id', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
