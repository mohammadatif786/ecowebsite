<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Say Thank You": a Boost someone sent you stays on your thank-you list until you thank them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('uvibe_boosts', function (Blueprint $table) {
            $table->timestamp('thanked_at')->nullable()->after('coins');
        });
    }

    public function down(): void
    {
        Schema::table('uvibe_boosts', function (Blueprint $table) {
            $table->dropColumn('thanked_at');
        });
    }
};
