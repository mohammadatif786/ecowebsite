<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration { public function up(): void { Schema::table('products', fn (Blueprint $table) => $table->boolean('collect_tax')->default(false)->after('price')); } public function down(): void { Schema::table('products', fn (Blueprint $table) => $table->dropColumn('collect_tax')); } };
