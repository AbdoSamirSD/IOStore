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
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->foreignId('wallet_id')->constrained('wallets')->onDelete('cascade')->after('id');
            $table->enum('type', ['order_earning', 'order_refund', 'order_cancellation', 'order_chargeback', 'order_payment', 'withdraw'])->after('wallet_id');
            $table->decimal('amount', 10, 2)->after('type');
            $table->string('description')->nullable()->after('amount');
            $table->enum('direction', ['credit', 'debit'])->after('description');
            $table->unsignedBigInteger('related_order_id')->nullable()->after('direction');
            $table->foreign('related_order_id')->references('id')->on('orders')->onDelete('cascade')->after('direction');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->after('related_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            //
        });
    }
};
