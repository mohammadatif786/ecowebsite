<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InitialDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');
        $this->call([
            UsersSeeder::class,
            EventsSeeder::class,
            EventCategorySeeder::class,
            FlaggedUsersSeeder::class,
            FriendRequestSeeder::class,
            ActivityLogSeeder::class,
            FavouriteEventsSeeder::class,
            CouponSeeder::class,
            EmailTemplateSeeder::class,
            LiveStreamSeeder::class,
            LiveStreamsLinkupSeeder::class,
            RequestMoneySeeder::class,
            UserSubscriptionSeeder::class,
            AdvertisementSeeder::class,
            BankInfoSeeder::class,
            CashOutSeeder::class,
            ClubFeteSeeder::class,
            WalletSettingsSeeder::class,
            EMoneySeeder::class,
            EncounterSeeder::class,
            GiftSeeder::class,
            InvoiceSeeder::class,
            NewsSeeder::class,
            ProductCategorySeeder::class,

            ProductSeeder::class,
            OrderSeeder::class,
            // OrgSignUserSeeder::class,
            PayoutSeeder::class,
            ScanSignUserSeeder::class,
            // TransactionSeeder::class,
            // PaymentSeeder::class,
            RestaurantSeeder::class,
            SelectedAttendeeSeeder::class,
            SettingsSeeder::class,
            SponsorSeeder::class,
            SubscriptionSeeder::class,
            TaxSeeder::class,
            TicketSeeder::class,
            TicketSaleSeeder::class,

            CountrySeeder::class,
            CaribbeanIslandSeeder::class,
            CountryPhoneCodeSeeder::class,
            CurrencySeeder::class,
            NationalitySeeder::class,
            RolesAndPermissionsSeeder::class,
        ]);
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
