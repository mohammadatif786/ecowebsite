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
        Schema::table('ticket_sales', function (Blueprint $table) {
            $table->string('stripe_id')->nullable()->default(null)->after('total');
            $table->string('stripe_price')->nullable()->default(null)->after('stripe_id');
            $table->string('stripe_status')->nullable()->default(null)->after('stripe_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_sales', function (Blueprint $table) {
            $table->dropColumn('stripe_id');
            $table->dropColumn('stripe_price');
            $table->dropColumn('stripe_status');
        });
    }
};
