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
        Schema::table('commission_plans', function (Blueprint $table) {
            $table->foreignId('vendor_id')->constrained('vendors')->onDelete('cascade')->after('id');
            $table->foreignId('product_category_id')->constrained('main_categories')->onDelete('cascade')->after('vendor_id');
            $table->string('plan_name')->nullable()->after('product_category_id');
            $table->unique(['vendor_id', 'product_category_id'], 'unique_vendor_category_commission_plan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('commission_plans', function (Blueprint $table) {
            //
        });
    }
};
