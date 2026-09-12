<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_sponsor_ads', function (Blueprint $table) {
            if (Schema::hasColumn('email_sponsor_ads', 'email_category')) {
                $table->dropColumn('email_category');
            }
            if (Schema::hasColumn('email_sponsor_ads', 'regions')) {
                $table->dropColumn('regions');
            }
            if (!Schema::hasColumn('email_sponsor_ads', 'email_ad_category_id')) {
                $table->foreignId('email_ad_category_id')->nullable()->after('company_name')->constrained()->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('email_sponsor_ads', function (Blueprint $table) {
            $table->dropForeign(['email_ad_category_id']);
            $table->dropColumn('email_ad_category_id');
            $table->string('email_category')->after('company_name');
            $table->json('regions')->nullable()->after('email_category');
        });
    }
};
