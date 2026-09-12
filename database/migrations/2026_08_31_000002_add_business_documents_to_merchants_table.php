<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('merchants', function (Blueprint $table) {
            $table->string('business_license')->nullable()->after('offers_delivery');
            $table->string('vat_certificate')->nullable()->after('business_license');
            $table->string('business_license_file')->nullable()->after('vat_certificate');
            $table->string('vat_certificate_file')->nullable()->after('business_license_file');
        });
    }

    public function down(): void
    {
        Schema::table('merchants', function (Blueprint $table) {
            $table->dropColumn(['business_license', 'vat_certificate', 'business_license_file', 'vat_certificate_file']);
        });
    }
};
