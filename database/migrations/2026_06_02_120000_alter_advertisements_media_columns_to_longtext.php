<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(
            'ALTER TABLE `advertisements`
                MODIFY `image` LONGTEXT NULL,
                MODIFY `video` LONGTEXT NULL,
                MODIFY `thumbnail` LONGTEXT NULL'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement(
            'ALTER TABLE `advertisements`
                MODIFY `image` VARCHAR(255) NULL,
                MODIFY `video` VARCHAR(255) NULL,
                MODIFY `thumbnail` VARCHAR(255) NULL'
        );
    }
};
