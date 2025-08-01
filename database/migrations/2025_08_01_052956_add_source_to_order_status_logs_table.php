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
        Schema::table('order_status_logs', function (Blueprint $table) {
            $table->string('source')->nullable()->after('order_id');
            $table->foreignId('updated_by')
                ->nullable()
                ->onDelete('SET NULL')
                ->after('source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_status_logs', function (Blueprint $table) {
            //
        });
    }
};
