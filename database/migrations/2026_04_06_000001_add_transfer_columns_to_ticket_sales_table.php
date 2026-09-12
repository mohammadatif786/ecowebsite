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
        if (!Schema::hasColumn('ticket_sales', 'transferred_from_user_id')) {
            Schema::table('ticket_sales', function (Blueprint $table) {
                $table->foreignId('transferred_from_user_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('ticket_sales', 'transferred_at')) {
            Schema::table('ticket_sales', function (Blueprint $table) {
                $table->timestamp('transferred_at')->nullable()->after('transferred_from_user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('ticket_sales', 'transferred_from_user_id')) {
            Schema::table('ticket_sales', function (Blueprint $table) {
                $table->dropConstrainedForeignId('transferred_from_user_id');
            });
        }

        if (Schema::hasColumn('ticket_sales', 'transferred_at')) {
            Schema::table('ticket_sales', function (Blueprint $table) {
                $table->dropColumn('transferred_at');
            });
        }
    }
};
