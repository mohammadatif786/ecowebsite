<?php

namespace App\Domain\Wallet;

use App\Models\WalletEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Writes the wallet activity trail. Never throws: a logging problem must not break a payment.
 */
final class WalletActivity
{
    /** Labels shown to admins, grouped for the filter menu. */
    public const TYPES = [
        'Money' => [
            'topup_started' => 'Top-up started',
            'topup_completed' => 'Top-up completed',
            'send_money' => 'Sent money',
            'request_money' => 'Requested money',
            'request_paid' => 'Paid a request',
            'request_rejected' => 'Rejected a request',
            'request_cancelled' => 'Cancelled a request',
            'request_responded' => 'Answered a request',
            'ticket_paid_wallet' => 'Paid ticket from wallet',
            'tag_item_bought' => 'Bought tagged item',
        ],
        'Coins & gifts' => [
            'coins_purchase_started' => 'Coin purchase started',
            'coins_purchased' => 'Coins purchased',
            'coins_gifted' => 'Gifted coins',
            'gift_sent' => 'Sent a gift',
            'gifts_cashed_out' => 'Cashed out gifts',
            'earnings_to_wallet' => 'Moved earnings to wallet',
        ],
        'Bank' => [
            'bank_account_saved' => 'Saved bank account',
            'bank_withdrawal_requested' => 'Requested bank withdrawal',
            'bank_withdrawal_cancelled' => 'Cancelled bank withdrawal',
            'bank_withdrawal_approved' => 'Withdrawal approved (admin)',
            'bank_withdrawal_rejected' => 'Withdrawal rejected (admin)',
        ],
        'Security & identity' => [
            'pin_changed' => 'Changed transaction PIN',
            'pin_verified' => 'Checked transaction PIN',
            'kyc_submitted' => 'Submitted identity check',
            'kyc_reviewed' => 'Identity check reviewed (admin)',
            'kyc_document_viewed' => 'ID document viewed (admin)',
        ],
        'Contacts & ASUE' => [
            'contact_added' => 'Added Quick Pay contact',
            'contact_removed' => 'Removed Quick Pay contact',
            'asue_created' => 'Created ASUE',
            'asue_joined' => 'Joined ASUE',
        ],
    ];

    public static function label(string $type): string
    {
        foreach (self::TYPES as $group) {
            if (isset($group[$type])) {
                return $group[$type];
            }
        }

        return Str::headline($type);
    }

    /**
     * @param  array{user_id?: ?int, actor_id?: ?int, actor_type?: string, status?: string, amount?: float|int|string|null,
     *               currency?: ?string, counterparty_id?: ?int, reference?: ?string, message?: ?string, meta?: array, channel?: ?string}  $data
     */
    public static function record(string $type, array $data = []): ?WalletEvent
    {
        try {
            $request = request();
            $actorId = $data['actor_id'] ?? auth()->id();

            return WalletEvent::create([
                'user_id' => $data['user_id'] ?? $actorId,
                'actor_id' => $actorId,
                'actor_type' => $data['actor_type'] ?? 'user',
                'type' => Str::limit($type, 60, ''),
                'status' => $data['status'] ?? 'success',
                'amount' => isset($data['amount']) && is_numeric($data['amount']) ? round((float) $data['amount'], 2) : null,
                'currency' => $data['currency'] ?? (isset($data['amount']) ? 'USD' : null),
                'counterparty_id' => $data['counterparty_id'] ?? null,
                'reference' => isset($data['reference']) ? Str::limit((string) $data['reference'], 120, '') : null,
                'message' => isset($data['message']) ? Str::limit((string) $data['message'], 250) : null,
                'meta' => $data['meta'] ?? null,
                'channel' => $data['channel'] ?? self::channel(),
                'ip' => $request?->ip(),
                'user_agent' => $request ? Str::limit((string) $request->userAgent(), 250, '') : null,
            ]);
        } catch (Throwable $e) {
            Log::warning('Wallet activity not recorded', ['type' => $type, 'error' => $e->getMessage()]);

            return null;
        }
    }

    private static function channel(): string
    {
        $request = request();
        if (! $request) {
            return 'system';
        }
        if ($request->is('api/*') || $request->bearerToken()) {
            return 'mobile';
        }

        return $request->is('admin/*') ? 'admin' : 'web';
    }
}
