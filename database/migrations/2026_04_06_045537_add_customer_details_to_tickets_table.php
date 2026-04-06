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
        Schema::table('tickets', function (Blueprint $table) {
            // 1. Guest customer ki details store karne ke liye
            $table->string('customer_name')->after('subject')->nullable();
            $table->string('customer_email')->after('customer_name')->nullable();

            // 2. user_id ko nullable banayein (kyunki guest ke paas user_id nahi hoti)
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            //
        });
    }
};
