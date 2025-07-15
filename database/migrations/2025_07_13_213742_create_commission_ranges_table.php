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
        Schema::create('commission_ranges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commission_plan_id')
                ->constrained('commission_plans')
                ->onDelete('cascade');
            $table->decimal('min_value', 8, 2)->default(0.00);
            $table->decimal('max_value', 8, 2)->default(0.00);
            $table->decimal('percentage', 5, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_ranges');
    }
};
