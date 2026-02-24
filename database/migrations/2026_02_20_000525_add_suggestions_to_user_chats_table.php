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
        Schema::table('users_chat', function (Blueprint $table) {
            $table->json('suggested_products')->nullable();
            $table->boolean('awaiting_selection')->default(false);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_chat', function (Blueprint $table) {
            $table->dropColumn(['suggested_products', 'awaiting_selection']);
        });
    }
};
