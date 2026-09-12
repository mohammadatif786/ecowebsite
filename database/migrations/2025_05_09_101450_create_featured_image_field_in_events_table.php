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
        Schema::table('link_up_events', function (Blueprint $table) {
            $table->string('featured_image')->nullable()->before('image_object');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('link_up_events', function (Blueprint $table) {
            $table->dropColumn(['featured_image']);
        });
    }
};
