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
        Schema::table('users', function (Blueprint $table) {
            $table->text('about_me')->nullable();
            $table->text('address_proof')->nullable();
            $table->unsignedInteger('age')->nullable();

            $table->string('avatar')->nullable();
            $table->text('back_side')->nullable();
            $table->decimal('balance', 10, 2)->default(0);
            $table->date('birthday')->nullable();
            $table->string('country')->nullable();
            $table->string('country_code')->nullable();

            $table->string('distanceinMK')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('fcmToken')->nullable();
            $table->text('front_side')->nullable();
            $table->string('gender')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_club')->default(false);
            $table->boolean('is_ghost')->default(false);
            $table->boolean('is_live_streaming')->default(false);
            $table->boolean('is_restaurant')->default(false);
            $table->boolean('is_top_shelf')->default(false);
            $table->string('job')->nullable();
            $table->string('kyc_status')->nullable();
            $table->string('language')->nullable();
            $table->unsignedBigInteger('last_active')->nullable();
            $table->string('latitude')->nullable();
            $table->string('link_me_with')->nullable();
            $table->string('link_me_with_country_code')->nullable();
            $table->string('link_me_with_country_name')->nullable();
            $table->string('link_with_me_phone_code')->nullable();
            $table->string('longitude')->nullable();
            $table->string('city')->nullable();
            $table->string('new_city')->nullable();
            $table->string('new_country')->nullable();
            $table->boolean('new_match_notification')->default(true);
            $table->boolean('new_message_notification')->default(true);
            $table->string('state')->nullable();
            $table->string('new_state')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('phone_number_dial_code')->nullable();
            $table->boolean('promotion_notification')->default(true);
            $table->boolean('show_age')->default(true);
            $table->boolean('status')->default(true);
            $table->string('stripe_customer_id')->nullable();
            $table->uuid('uid')->unique()->nullable();
            $table->string('university')->nullable();
            $table->string('video')->nullable();
            $table->string('which_latin_country_you_linked_with')->nullable();
            $table->string('whyare')->nullable();
            $table->text('caribbean_interest')->nullable();
            $table->json('age_filter')->nullable();     //
            $table->json('distance_filter')->nullable();    //
            $table->json('interests')->nullable();      //
            $table->json('subscription')->nullable();   //
            $table->json('more_photos')->nullable();    //
            $table->string('type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
