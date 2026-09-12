<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->renameColumn('title', 'name');
            $table->renameColumn('duration', 'duration_days');
        });

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->string('emoji', 8)->nullable()->after('name');
            $table->string('tagline')->nullable()->after('emoji');
            $table->string('stripe_product_id')->nullable()->after('firebase_id');
            $table->string('billing_cycle')->default('monthly')->after('price');
            $table->json('features')->nullable()->after('description');
            $table->json('perks')->nullable()->after('features');
        });

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->index('stripe_product_id');
            $table->unique('stripe_price_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropIndex(['stripe_product_id']);
            $table->dropUnique(['stripe_price_id']);
            $table->dropIndex(['status']);
            $table->dropColumn(['emoji', 'tagline', 'stripe_product_id', 'billing_cycle', 'features', 'perks']);
        });

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->renameColumn('name', 'title');
            $table->renameColumn('duration_days', 'duration');
        });
    }
};
