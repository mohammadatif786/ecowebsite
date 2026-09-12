<?php

namespace Database\Seeders;

use App\Models\Notification;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $notifications = [
            // Today - Recent notifications
            [
                'title' => 'You received $25.00 from Marcus',
                'message' => '"For lunch 🍔"',
                'send_by' => 'Marcus',
                'type' => 'payment',
                'context' => 'Payments',
                'unread' => true,
                'priority' => true,
                'icon' => '💵',
                'metadata' => json_encode([
                    'amount' => 25,
                    'currency' => 'USD',
                    'status' => 'received',
                    'cta' => ['label' => 'View', 'href' => '#']
                ]),
                'created_at' => $now->copy()->subMinutes(5),
            ],
            [
                'title' => 'Amira sent you a Rose 🌹',
                'message' => '"Thinking of you."',
                'send_by' => 'Amira',
                'type' => 'gift',
                'context' => 'Gifts',
                'unread' => true,
                'priority' => false,
                'icon' => '🌹',
                'avatar' => 'https://i.pravatar.cc/40?img=5',
                'metadata' => json_encode([
                    'giftType' => 'rose',
                    'cta' => ['label' => 'Say thanks', 'href' => '#']
                ]),
                'created_at' => $now->copy()->subMinutes(12),
            ],
            [
                'title' => 'Marcus requested $60.00',
                'message' => 'Request pending approval',
                'send_by' => 'Marcus',
                'type' => 'payment',
                'context' => 'Payments',
                'unread' => true,
                'priority' => true,
                'icon' => '🧾',
                'metadata' => json_encode([
                    'amount' => 60,
                    'currency' => 'USD',
                    'status' => 'request',
                    'ctaGroup' => [
                        ['label' => 'Approve', 'kind' => 'approve'],
                        ['label' => 'Decline', 'kind' => 'decline']
                    ]
                ]),
                'created_at' => $now->copy()->subMinutes(20),
            ],
            [
                'title' => 'Khizar sent you a message',
                'message' => '"Hey, how are you doing?"',
                'send_by' => 'Khizar',
                'type' => 'message',
                'context' => 'Messages',
                'unread' => true,
                'priority' => false,
                'avatar' => 'https://i.pravatar.cc/40?img=12',
                'metadata' => json_encode([
                    'actor' => 'Khizar',
                    'cta' => ['label' => 'Reply', 'href' => '#']
                ]),
                'created_at' => $now->copy()->subMinutes(35),
            ],
            [
                'title' => 'New match: Aaliyah',
                'message' => 'You and Aaliyah liked each other. Say hi!',
                'send_by' => 'System',
                'type' => 'match',
                'context' => 'Matches',
                'unread' => false,
                'priority' => false,
                'icon' => '💘',
                'metadata' => json_encode([
                    'cta' => ['label' => 'Open chat', 'href' => '#']
                ]),
                'created_at' => $now->copy()->subMinutes(50),
            ],

            // Earlier - Older notifications
            [
                'title' => 'Payment failed: $12.00 to Sam',
                'message' => 'Card declined. Try another method.',
                'send_by' => 'System',
                'type' => 'payment',
                'context' => 'Payments',
                'unread' => false,
                'priority' => true,
                'icon' => '❌',
                'metadata' => json_encode([
                    'amount' => 12,
                    'currency' => 'USD',
                    'status' => 'failed',
                    'cta' => ['label' => 'Retry', 'href' => '#']
                ]),
                'created_at' => $now->copy()->subHours(26),
            ],
            [
                'title' => 'You received a Diamond 💎',
                'message' => 'From Jenna',
                'send_by' => 'Jenna',
                'type' => 'gift',
                'context' => 'Gifts',
                'unread' => false,
                'priority' => false,
                'icon' => '💎',
                'avatar' => 'https://i.pravatar.cc/40?img=20',
                'metadata' => json_encode([
                    'giftType' => 'diamond',
                    'cta' => ['label' => 'View gift', 'href' => '#']
                ]),
                'created_at' => $now->copy()->subHours(28),
            ],
            [
                'title' => 'New login from a Safari browser',
                'message' => 'If this wasn\'t you, secure your account.',
                'send_by' => 'System',
                'type' => 'system',
                'context' => 'System',
                'unread' => true,
                'priority' => true,
                'icon' => '🔔',
                'metadata' => json_encode([
                    'cta' => ['label' => 'Review', 'href' => '#']
                ]),
                'created_at' => $now->copy()->subHours(29),
            ],
            [
                'title' => 'Sierra sent you a message',
                'message' => '"Coffee this weekend?"',
                'send_by' => 'Sierra',
                'type' => 'message',
                'context' => 'Messages',
                'unread' => false,
                'priority' => false,
                'avatar' => 'https://i.pravatar.cc/40?img=31',
                'metadata' => json_encode([
                    'actor' => 'Sierra',
                    'cta' => ['label' => 'Reply', 'href' => '#']
                ]),
                'created_at' => $now->copy()->subHours(30),
            ],
            [
                'title' => 'Your profile was approved',
                'message' => 'You can now be discovered by more matches.',
                'send_by' => 'System',
                'type' => 'system',
                'context' => 'System',
                'unread' => false,
                'priority' => false,
                'icon' => '⚙️',
                'created_at' => $now->copy()->subHours(36),
            ],
        ];

        foreach ($notifications as $notification) {
            Notification::create($notification);
        }
    }
}
