<?php

namespace App\Console\Commands\Kyc;

use App\Domain\Kyc\KycDocumentSource;
use App\Domain\Kyc\KycDocumentStore;
use App\Models\OrganizerKyc;
use App\Models\User;
use App\Models\UserWalletKyc;
use Illuminate\Console\Command;

/**
 * One-off: moves KYC documents that are still on the public disk to the private "kyc" disk
 * and updates the stored paths. Safe to run more than once.
 */
class SecureKycDocuments extends Command
{
    protected $signature = 'kyc:secure-documents {--dry-run : List what would move without changing anything}';

    protected $description = 'Move KYC documents (ID photos, selfies, proof of address) off the public disk';

    private array $counts = ['moved' => 0, 'already_private' => 0, 'missing' => 0];

    public function handle(KycDocumentStore $store): int
    {
        $dry = (bool) $this->option('dry-run');

        UserWalletKyc::query()->whereNotNull('kyc_documents')->chunkById(100, function ($rows) use ($store, $dry) {
            foreach ($rows as $kyc) {
                $documents = array_values((array) ($kyc->kyc_documents ?: []));
                $changed = false;
                foreach ($documents as $i => $value) {
                    $new = $this->secure($store, $value, "wallet/{$kyc->user_id}", $dry, "wallet #{$kyc->id} document_" . ($i + 1));
                    if ($new !== null && $new !== $value) {
                        $documents[$i] = $new;
                        $changed = true;
                    }
                }
                if ($changed && ! $dry) {
                    $kyc->forceFill(['kyc_documents' => $documents])->saveQuietly();
                }
            }
        });

        OrganizerKyc::query()->chunkById(100, function ($rows) use ($store, $dry) {
            foreach ($rows as $kyc) {
                $updates = [];
                foreach (KycDocumentSource::ORGANIZER_FIELDS as $field) {
                    $value = $kyc->getAttribute($field);
                    $new = $this->secure($store, $value, "organizer/{$kyc->organizer_id}", $dry, "organizer #{$kyc->id} {$field}");
                    if ($new !== null && $new !== $value) {
                        $updates[$field] = $new;
                    }
                }
                if ($updates && ! $dry) {
                    $kyc->forceFill($updates)->saveQuietly();
                }
            }
        });

        User::query()
            ->where(function ($q) {
                foreach (KycDocumentSource::USER_FIELDS as $field) {
                    $q->orWhereNotNull($field);
                }
            })
            ->select(array_merge(['id'], KycDocumentSource::USER_FIELDS))
            ->chunkById(100, function ($rows) use ($store, $dry) {
                foreach ($rows as $user) {
                    $updates = [];
                    foreach (KycDocumentSource::USER_FIELDS as $field) {
                        $value = $user->getAttribute($field);
                        $new = $this->secure($store, $value, "user/{$user->id}", $dry, "user #{$user->id} {$field}");
                        if ($new !== null && $new !== $value) {
                            $updates[$field] = $new;
                        }
                    }
                    if ($updates && ! $dry) {
                        User::whereKey($user->id)->update($updates);
                    }
                }
            });

        $this->newLine();
        $this->info(($dry ? '[dry run] would move: ' : 'Moved: ') . $this->counts['moved']
            . ' · already private: ' . $this->counts['already_private']
            . ' · file not found: ' . $this->counts['missing']);

        return self::SUCCESS;
    }

    private function secure(KycDocumentStore $store, ?string $value, string $folder, bool $dry, string $label): ?string
    {
        if (! $value) {
            return null;
        }

        $located = $store->locate($value);
        if ($located === null) {
            $this->counts['missing']++;
            $this->line("  missing   {$label}  ({$value})");

            return null;
        }

        if ($located['disk'] === KycDocumentStore::DISK) {
            $this->counts['already_private']++;

            return $value;
        }

        $this->counts['moved']++;
        if ($dry) {
            $this->line("  would move {$label}");

            return null;
        }

        $new = $store->moveToPrivate($value, $folder);
        if ($new === null) {
            $this->counts['moved']--;
            $this->counts['missing']++;
            $this->warn("  failed    {$label}");
        }

        return $new;
    }
}
