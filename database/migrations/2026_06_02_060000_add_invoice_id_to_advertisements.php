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
        if (!Schema::hasColumn('advertisements', 'invoice_id')) {
            Schema::table('advertisements', function (Blueprint $table) {
                $table->foreignId('invoice_id')
                    ->nullable()
                    ->constrained('invoices')
                    ->nullOnDelete()
                    ->after('firebase_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('advertisements', 'invoice_id')) {
            Schema::table('advertisements', function (Blueprint $table) {
                $table->dropForeign(['invoice_id']);
                $table->dropColumn('invoice_id');
            });
        }
    }
};
