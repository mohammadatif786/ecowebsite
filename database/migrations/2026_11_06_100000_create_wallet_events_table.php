<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per wallet action (by a member or an admin), successful or not.
 * The money itself stays in the wallet ledger; this is the "who did what, when, from where" trail.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('wallet_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->index();            // whose wallet
            $table->foreignId('actor_id')->nullable()->index();           // who did it (admin for reviews/approvals)
            $table->string('actor_type', 20)->default('user');            // user | admin | system
            $table->string('type', 60)->index();                          // send_money, topup_completed, kyc_reviewed, ...
            $table->string('status', 20)->default('success')->index();    // success | failed
            $table->decimal('amount', 14, 2)->nullable();
            $table->string('currency', 10)->nullable();
            $table->foreignId('counterparty_id')->nullable()->index();    // the other member, when there is one
            $table->string('reference', 120)->nullable();                 // e.g. withdrawal #, request #, stream #
            $table->string('message', 255)->nullable();                   // failure reason or short note
            $table->json('meta')->nullable();                             // safe extra fields (never PINs or account numbers)
            $table->string('channel', 20)->nullable();                    // web | mobile | admin
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_events');
    }
};
