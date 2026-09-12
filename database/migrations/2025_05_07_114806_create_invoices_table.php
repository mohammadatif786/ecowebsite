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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('firebase_id')->unique()->nullable(); // corresponds to "id"
            $table->string('doc_id')->nullable();
            $table->string('from_email')->nullable();
            $table->string('from_name')->nullable();
            $table->string('from_phone')->nullable();
            $table->string('from_address')->nullable();
            $table->string('for_name')->nullable();
            $table->string('for_email')->nullable();
            $table->string('for_phone')->nullable();
            $table->string('for_address')->nullable();
            $table->text('notes')->nullable();
            $table->integer('sequence')->nullable();
            $table->string('collection_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
