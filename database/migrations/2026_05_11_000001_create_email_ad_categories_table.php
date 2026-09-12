<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_ad_categories', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('short_label');
            $table->string('label');
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->string('preview_title')->nullable();
            $table->string('preview_subtitle')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_ad_categories');
    }
};
