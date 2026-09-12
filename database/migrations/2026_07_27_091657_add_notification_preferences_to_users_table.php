<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->time('quiet_hours_from')->nullable()->after('promotion_notification');
            $table->time('quiet_hours_to')->nullable()->after('quiet_hours_from');
            $table->boolean('allow_priority_notification')->default(true)->after('quiet_hours_to');
            $table->boolean('mute_payment_notification')->default(false)->after('allow_priority_notification');
            $table->boolean('mute_gift_notification')->default(false)->after('mute_payment_notification');
            $table->boolean('mute_system_notification')->default(false)->after('mute_gift_notification');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'quiet_hours_from',
                'quiet_hours_to',
                'allow_priority_notification',
                'mute_payment_notification',
                'mute_gift_notification',
                'mute_system_notification',
            ]);
        });
    }
};
