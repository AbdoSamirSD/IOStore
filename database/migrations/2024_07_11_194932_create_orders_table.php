<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('order_number')->unique();
            $table->decimal('sub_total', 10, 2);
            $table->decimal('delivery_fee', 10, 2);
            $table->decimal('discount', 10, 2)->nullable();
            $table->decimal('total_cost', 10, 2);
            $table->foreignId('promo_code_id')->nullable()->constrained()->onDelete('set null');

            $table->enum('status', ['preparing', 'on the way', 'delivered', 'canceled'])->default('preparing');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
