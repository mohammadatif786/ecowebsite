<?php

namespace App\Domain\Kyc;

use App\Models\OrganizerKyc;
use App\Models\User;
use App\Models\UserWalletKyc;
use Illuminate\Support\Facades\URL;

/**
 * The three places KYC documents are recorded, and how an admin link names each document.
 *
 *  wallet    — user_wallet_kycs.kyc_documents (array)   keys: document_1, document_2, ...
 *  organizer — organizer_k_y_c_s columns                keys: passport_front, passport_back, proof_of_address
 *  user      — users columns (profile / mobile KYC)     keys: front_side, back_side, address_proof, selfie
 */
final class KycDocumentSource
{
    public const ORGANIZER_FIELDS = ['passport_front', 'passport_back', 'proof_of_address'];

    public const USER_FIELDS = ['front_side', 'back_side', 'address_proof', 'selfie'];

    /** Minutes an admin document link stays valid. */
    public const LINK_MINUTES = 30;

    public static function storedValue(string $source, int $id, string $document): ?string
    {
        return match ($source) {
            'wallet' => self::walletDocument($id, $document),
            'organizer' => in_array($document, self::ORGANIZER_FIELDS, true)
                ? OrganizerKyc::findOrFail($id)->getAttribute($document)
                : null,
            'user' => in_array($document, self::USER_FIELDS, true)
                ? User::findOrFail($id)->getAttribute($document)
                : null,
            default => null,
        };
    }

    /**
     * Signed, short-lived link to the admin-only file route. External URLs (another host) are passed through.
     */
    public static function adminUrl(string $source, int $id, string $document, ?string $storedValue): ?string
    {
        if (! $storedValue) {
            return null;
        }

        $value = trim($storedValue);
        if (preg_match('#^https?://#i', $value)) {
            $host = parse_url($value, PHP_URL_HOST);
            $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
            if ($host && $appHost && strcasecmp($host, $appHost) !== 0 && strcasecmp($host, request()->getHost()) !== 0) {
                return $value;
            }
        }

        return URL::temporarySignedRoute(
            'admin.trust.kyc.documents.file',
            now()->addMinutes(self::LINK_MINUTES),
            ['source' => $source, 'id' => $id, 'document' => $document],
            absolute: false, // relative signature: survives proxies / http→https at the edge
        );
    }

    private static function walletDocument(int $id, string $document): ?string
    {
        if (! preg_match('/^document_(\d{1,3})$/', $document, $m)) {
            return null;
        }

        $documents = array_values((array) (UserWalletKyc::findOrFail($id)->kyc_documents ?: []));

        return $documents[((int) $m[1]) - 1] ?? null;
    }
}
