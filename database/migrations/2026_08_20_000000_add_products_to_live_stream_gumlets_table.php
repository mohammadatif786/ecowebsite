<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_stream_gumlets', function (Blueprint $table) {
            $table->json('products')->nullable()->after('guests');
        });
    }

    public function down(): void
    {
        Schema::table('live_stream_gumlets', function (Blueprint $table) {
            $table->dropColumn('products');
        });
    }
};
