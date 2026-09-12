<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('merchants', function (Blueprint $table) {
            $table->boolean('offers_pickup')->default(true)->after('pickup_locations');
            $table->boolean('offers_delivery')->default(false)->after('offers_pickup');
        });
    }

    public function down(): void
    {
        Schema::table('merchants', function (Blueprint $table) {
            $table->dropColumn(['offers_pickup', 'offers_delivery']);
        });
    }
};
