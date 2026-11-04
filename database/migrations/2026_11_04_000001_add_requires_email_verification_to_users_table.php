<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Marks the members who must confirm their email before using LinkUp.
     *
     * Only accounts created through the new sign-up are set to true (by RegisterMemberAction).
     * Every existing member, and anyone created by the mobile app's register endpoint, keeps the
     * default false, so nobody who is already in gets locked out.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('requires_email_verification')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('requires_email_verification');
        });
    }
};
