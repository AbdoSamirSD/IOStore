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
        Schema::create('wallet', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade');
            $table->decimal('balance', 10, 2)->default(0.00);
            $table->decimal('pending_balance', 10, 2)->default(0.00);
            $table->decimal('withdrawn_amount', 10, 2)->default(0.00);
            $table->decimal('total_earnings', 10, 2)->default(0.00);
            $table->decimal('total_withdrawn', 10, 2)->default(0.00);
            $table->decimal('total_refunded', 10, 2)->default(0.00);
            $table->decimal('total_commission', 10, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet');
    }
};
