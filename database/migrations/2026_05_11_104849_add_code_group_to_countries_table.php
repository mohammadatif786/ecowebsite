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
        Schema::table('countries', function (Blueprint $table) {
            if (!Schema::hasColumn('countries', 'subregion')) {
                $table->string('subregion')->nullable()->after('name'); // Caribbean, Central America, South America
            }
            if (!Schema::hasColumn('countries', 'flag')) {
                $table->string('flag')->nullable()->after('subregion');
            }
            if (!Schema::hasColumn('countries', 'currency')) {
                $table->string('currency')->nullable()->after('flag');
            }
            if (!Schema::hasColumn('countries', 'support_email')) {
                $table->string('support_email')->nullable()->after('currency');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn(['subregion', 'flag', 'currency', 'support_email']);
        });
    }
};
