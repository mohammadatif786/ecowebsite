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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('firebase_id')->unique()->nullable(); // "id" from Firestore
            $table->string('name');
            $table->foreignId('product_category_id')->constrained()->onDelete('cascade');
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->integer('qty')->default(0);
            $table->decimal('price', 10, 2)->default(0.00);
            $table->boolean('status')->default(true);
            $table->json('images');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
