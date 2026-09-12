<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tax_remittance_centers', function (Blueprint $table) {
            $table->text('api_key')->nullable()->change();
            $table->text('api_secret')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('tax_remittance_centers', function (Blueprint $table) {
            $table->string('api_key', 255)->nullable()->change();
            $table->string('api_secret', 255)->nullable()->change();
        });
    }
};
