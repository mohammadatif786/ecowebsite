<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_sponsor_ads', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->foreignId('email_ad_category_id')->nullable()->constrained()->onDelete('set null');
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('priority')->default(3);
            $table->enum('status', ['active', 'paused', 'draft'])->default('draft');
            $table->string('headline');
            $table->text('message');
            $table->string('cta_text');
            $table->text('cta_url');
            $table->longText('image')->nullable();
            $table->timestamps();

            $table->index(['email_ad_category_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_sponsor_ads');
    }
};
