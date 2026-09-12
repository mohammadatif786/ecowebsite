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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('firebase_id')->unique()->nullable(); // maps from "id"
            $table->string('name');
            $table->string('title')->nullable();
            $table->boolean('is_free')->default(false);
            $table->decimal('price', 8, 2)->nullable();
            $table->decimal('price_economy', 8, 2)->nullable();
            $table->decimal('price_vip', 8, 2)->nullable();
            $table->decimal('early_bird_economy_price', 8, 2)->nullable();
            $table->decimal('early_bird_vip_price', 8, 2)->nullable();

            $table->unsignedBigInteger('no_of_early_bird_economy')->nullable();
            $table->unsignedBigInteger('no_of_tickets_available_economy')->nullable();

            $table->unsignedBigInteger('no_of_early_bird_vip')->nullable();
            $table->unsignedBigInteger('no_of_tickets_available_vip')->nullable();

            $table->date('available_to')->nullable();
            $table->date('available_from')->nullable();
            $table->text('description')->nullable();
            $table->string('type')->default('free');
            $table->foreignId('link_up_event_id')->nullable()->constrained();
            $table->integer('qty')->default(0);
            $table->integer('qty_available')->default(0);
            $table->integer('qty_sold')->default(0);
            $table->integer('min_qty_per_order')->default(1);
            $table->integer('max_qty_per_order')->default(1);
            $table->bigInteger('qrcode')->nullable();
            $table->datetime('sales_start');
            $table->datetime('sales_end');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
