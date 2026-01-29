<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('users_chat', function (Blueprint $table) {
            $table->string('last_product')->nullable()->after('last_topic');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_chat', function (Blueprint $table) {
            $table->dropColumn('last_product');
        });
    }
};
