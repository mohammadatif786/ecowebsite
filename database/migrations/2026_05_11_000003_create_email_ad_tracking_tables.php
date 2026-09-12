<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Update countries table to include code
        if (!Schema::hasColumn('countries', 'code')) {
            Schema::table('countries', function (Blueprint $table) {
                $table->string('code', 10)->after('id')->unique()->nullable();
            });
        }

        // Sponsor Country Pivot Table
        Schema::create('email_sponsor_ad_country', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_sponsor_ad_id')->constrained()->onDelete('cascade');
            $table->foreignId('country_id')->constrained()->onDelete('cascade');
        });

        // Sponsor Impressions
        Schema::create('sponsor_impressions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_sponsor_ad_id')->constrained('email_sponsor_ads')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('email_category');
            $table->string('country_code')->nullable();
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();
        });

        // Sponsor Clicks
        Schema::create('sponsor_clicks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_sponsor_ad_id')->constrained('email_sponsor_ads')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('clicked_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sponsor_clicks');
        Schema::dropIfExists('sponsor_impressions');
        Schema::dropIfExists('email_sponsor_ad_country');
        
        if (Schema::hasColumn('countries', 'code')) {
            Schema::table('countries', function (Blueprint $table) {
                $table->dropColumn('code');
            });
        }
    }
};
