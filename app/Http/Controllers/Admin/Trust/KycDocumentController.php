<?php

namespace App\Http\Controllers\Admin\Trust;

use App\Domain\Kyc\KycDocumentSource;
use App\Domain\Kyc\KycDocumentStore;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

/**
 * Streams one KYC document to an admin. The route sits inside the admin group and also needs a
 * signed URL, so a copied link stops working after KycDocumentSource::LINK_MINUTES.
 */
class KycDocumentController extends Controller
{
    public function show(string $source, int $id, string $document, KycDocumentStore $store)
    {
        abort_unless(in_array($source, ['wallet', 'organizer', 'user'], true), 404);

        $located = $store->locate(KycDocumentSource::storedValue($source, $id, $document));
        abort_if($located === null, 404);

        // One trail entry per admin per document every 10 minutes (thumbnails reload with the page).
        $seenKey = 'kyc-doc-viewed:' . auth()->id() . ":{$source}:{$id}:{$document}";
        if (\Illuminate\Support\Facades\Cache::add($seenKey, 1, now()->addMinutes(10))) {
        \App\Domain\Wallet\WalletActivity::record('kyc_document_viewed', [
            'user_id' => match ($source) {
                'wallet' => \App\Models\UserWalletKyc::whereKey($id)->value('user_id'),
                'organizer' => \App\Models\OrganizerKyc::with('organizer:id,user_id')->find($id)?->organizer?->user_id,
                default => $id,
            },
            'actor_type' => 'admin',
            'reference' => $source . ' KYC #' . $id . ' · ' . $document,
        ]);
        }

        Log::info('KYC document viewed', [
            'admin_id' => auth()->id(),
            'source' => $source,
            'record_id' => $id,
            'document' => $document,
        ]);

        return $store->response($located);
    }
}
