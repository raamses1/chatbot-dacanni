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
            $table->string('last_product_link')->nullable()->after('last_product');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_chat', function (Blueprint $table) {
             $table->dropColumn('last_product_link');
        });
    }
};
