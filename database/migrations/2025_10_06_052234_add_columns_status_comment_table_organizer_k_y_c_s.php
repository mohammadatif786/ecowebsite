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
        Schema::table('organizer_k_y_c_s', function (Blueprint $table) {
            $table->enum('p_front_status', ['pending', 'approved','canceled'])->default('pending');
            $table->enum('p_back_status', ['pending', 'approved','canceled'])->default('pending');
            $table->enum('p_o_add_status', ['pending', 'approved','canceled'])->default('pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizer_k_y_c_s', function (Blueprint $table) {
            $table->dropColumn('p_front_status');
            $table->dropColumn('b_front_status');
            $table->dropColumn('p_o_add_front_status');
        });
    }
};
