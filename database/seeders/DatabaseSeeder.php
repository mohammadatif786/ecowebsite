<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use ReflectionClass;
use Throwable;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Seeders that must run first
        |--------------------------------------------------------------------------
        |
        | Put dependency-sensitive seeders here.
        |
        */

        $prioritySeeders = [
            InitialDatabaseSeeder::class,

            RolesAndPermissionsSeeder::class,
            OrganizerPermissionsSeeder::class,

            SettingsSeeder::class,
            WalletSettingsSeeder::class,

            CountrySeeder::class,
            CountryPhoneCodeSeeder::class,
            NationalitySeeder::class,
            CurrencySeeder::class,

            UsersSeeder::class,
            AssignUserRoletoAllUsers::class,
        ];

        /*
        |--------------------------------------------------------------------------
        | Run priority seeders
        |--------------------------------------------------------------------------
        */

        foreach ($prioritySeeders as $seeder) {
            if (class_exists($seeder)) {
                $this->command?->info("Running: {$seeder}");

                $this->call($seeder);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Automatically discover ALL remaining seeders
        |--------------------------------------------------------------------------
        */

        $files = glob(database_path('seeders/*Seeder.php'));

        sort($files);

        foreach ($files as $file) {

            $className = pathinfo($file, PATHINFO_FILENAME);

            /*
             * Do not execute DatabaseSeeder again.
             */
            if ($className === 'DatabaseSeeder') {
                continue;
            }

            $class = "Database\\Seeders\\{$className}";

            /*
             * Skip priority seeders because they already ran above.
             */
            if (in_array($class, $prioritySeeders, true)) {
                continue;
            }

            /*
             * Make sure the class exists.
             */
            if (! class_exists($class)) {
                $this->command?->warn(
                    "Seeder class not found: {$class}"
                );

                continue;
            }

            /*
             * Skip abstract classes.
             */
            $reflection = new ReflectionClass($class);

            if ($reflection->isAbstract()) {
                continue;
            }

            /*
             * Make sure it is actually a Laravel Seeder.
             */
            if (! $reflection->isSubclassOf(Seeder::class)) {
                $this->command?->warn(
                    "Skipped because it is not a Seeder: {$class}"
                );

                continue;
            }

            /*
             * Run seeder.
             */
            $this->command?->info(
                "Running: {$class}"
            );

            try {
                $this->call($class);
            } catch (Throwable $e) {

                $this->command?->error(
                    "Seeder failed: {$class}"
                );

                throw $e;
            }
        }

        $this->command?->info(
            'All database seeders completed successfully.'
        );
    }
}
