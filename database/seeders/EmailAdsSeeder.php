<?php

namespace Database\Seeders;

use App\Models\EmailAdCategory;
use App\Models\EmailSponsorAd;
use Illuminate\Database\Seeder;

class EmailAdsSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'key' => 'like_received',
                'short_label' => 'Like',
                'label' => 'You Have a Like',
                'icon' => '💚',
                'description' => 'Sent when a member receives a like or profile link.',
                'preview_title' => 'You Have a Like',
                'preview_subtitle' => 'Someone just liked your profile.',
                'is_system' => true,
            ],
            [
                'key' => 'money_received',
                'short_label' => 'Money In',
                'label' => 'You Have Received Money',
                'icon' => '💳',
                'description' => "Sent when money lands in a member's LinkUp Wallet.",
                'preview_title' => 'You Have Received Money',
                'preview_subtitle' => 'Your wallet has been updated.',
                'is_system' => true,
            ],
            [
                'key' => 'money_request',
                'short_label' => 'Request',
                'label' => 'Money Request Received',
                'icon' => '📩',
                'description' => 'Sent when a payment request is received.',
                'preview_title' => 'Money Request Received',
                'preview_subtitle' => 'A payment request was sent to you.',
                'is_system' => true,
            ],
            [
                'key' => 'events',
                'short_label' => 'Events',
                'label' => 'Events Email',
                'icon' => '🎟️',
                'description' => 'Sent for single events and upcoming event digests.',
                'preview_title' => 'New Event Alert',
                'preview_subtitle' => 'An organizer just promoted an event near you.',
                'is_system' => true,
            ],
            [
                'key' => 'marketplace',
                'short_label' => 'Shop',
                'label' => 'Marketplace Email',
                'icon' => '🛍️',
                'description' => 'Sent for shopping, seller, and product emails.',
                'preview_title' => 'Marketplace Picks',
                'preview_subtitle' => 'Discover top products from trusted sellers.',
                'is_system' => true,
            ],
            [
                'key' => 'birthday',
                'short_label' => 'Birthday',
                'label' => 'Birthday Email',
                'icon' => '🎂',
                'description' => 'Automated birthday greeting sent to members.',
                'preview_title' => 'Happy Birthday!',
                'preview_subtitle' => 'Today is all about celebrating you.',
                'is_system' => true,
            ],
        ];

        foreach ($categories as $cat) {
            EmailAdCategory::updateOrCreate(['key' => $cat['key']], $cat);
        }

        // Add an example ad for the 'like_received' category so testing works immediately
        $likeCat = EmailAdCategory::where('key', 'like_received')->first();
        if ($likeCat && EmailSponsorAd::count() === 0) {
            EmailSponsorAd::create([
                'company_name' => 'Scotiabank',
                'email_ad_category_id' => $likeCat->id,
                'start_date' => now()->subDays(1),
                'end_date' => now()->addMonths(6),
                'priority' => 1,
                'status' => 'active',
                'headline' => 'Bank smarter. Live better.',
                'message' => 'Scotiabank is proud to support the Caribbean community with secure digital banking.',
                'cta_text' => 'Learn More',
                'cta_url' => 'https://www.scotiabank.com',
                'image' => null, // Or provide a default path if available
            ]);
        }
    }
}
