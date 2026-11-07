<?php

namespace App\Http\Controllers\Admin\EWallet;

use App\Domain\Wallet\WalletActivity;
use App\Http\Controllers\Controller;
use App\Models\WalletEvent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * E-Wallet → Wallet Activity: every wallet action by members and admins, searchable and exportable.
 */
class WalletActivityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $filters = $this->filters($request);
        $page = $this->query($filters)
            ->with(['user:id,name,email,linkup_id', 'actor:id,name,email', 'counterparty:id,name,email,linkup_id'])
            ->orderByDesc('id')
            ->paginate(50);

        return response()->json([
            'data' => collect($page->items())->map(fn (WalletEvent $e) => $this->present($e))->values(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'total' => $page->total(),
            ],
            'summary' => $this->summary(),
            'types' => WalletActivity::TYPES,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->filters($request);
        $name = 'wallet-activity-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($filters) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Time (UTC)', 'Member ID', 'Member', 'Email', 'Action', 'Status', 'Amount', 'Currency', 'Other member', 'Reference', 'Message', 'Done by', 'Channel', 'IP']);

            $this->query($filters)
                ->with(['user:id,name,email', 'actor:id,name', 'counterparty:id,name,email'])
                ->orderByDesc('id')
                ->chunk(500, function ($rows) use ($out) {
                    foreach ($rows as $e) {
                        fputcsv($out, array_map([$this, 'csvSafe'], [
                            optional($e->created_at)->toDateTimeString(),
                            $e->user_id,
                            $e->user?->name,
                            $e->user?->email,
                            WalletActivity::label($e->type),
                            $e->status,
                            $e->amount,
                            $e->currency,
                            $e->counterparty ? $e->counterparty->name . ' <' . $e->counterparty->email . '>' : '',
                            $e->reference,
                            $e->message,
                            $e->actor_type === 'admin' ? 'Admin: ' . ($e->actor?->name ?? '#' . $e->actor_id) : 'Member',
                            $e->channel,
                            $e->ip,
                        ]));
                    }
                });

            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Stop spreadsheet formula injection from user-typed text (notes, names). */
    public function csvSafe(mixed $value): mixed
    {
        if (is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $value;
        }

        return $value;
    }

    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'user_id' => ['nullable', 'integer'],
            'type' => ['nullable', 'string', 'max:60'],
            'status' => ['nullable', 'in:success,failed'],
            'actor' => ['nullable', 'in:user,admin'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'min_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        return $validated;
    }

    private function query(array $f): Builder
    {
        return WalletEvent::query()
            ->when($f['user_id'] ?? null, fn ($q, $id) => $q->where(fn ($w) => $w->where('user_id', $id)->orWhere('counterparty_id', $id)))
            ->when($f['type'] ?? null, fn ($q, $t) => $q->where('type', $t))
            ->when($f['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($f['actor'] ?? null, fn ($q, $a) => $q->where('actor_type', $a))
            ->when($f['from'] ?? null, fn ($q, $d) => $q->where('created_at', '>=', Carbon::parse($d)->startOfDay()))
            ->when($f['to'] ?? null, fn ($q, $d) => $q->where('created_at', '<=', Carbon::parse($d)->endOfDay()))
            ->when(isset($f['min_amount']), fn ($q) => $q->where('amount', '>=', $f['min_amount']))
            ->when($f['search'] ?? null, function ($q, $s) {
                $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $s) . '%';
                $q->where(function ($w) use ($like, $s) {
                    $w->whereHas('user', fn ($u) => $u->where('name', 'like', $like)->orWhere('email', 'like', $like)->orWhere('linkup_id', 'like', $like))
                        ->orWhere('reference', 'like', $like)
                        ->orWhere('ip', $s);
                    if (ctype_digit($s)) {
                        $w->orWhere('user_id', (int) $s);
                    }
                });
            });
    }

    private function summary(): array
    {
        $today = now()->startOfDay();
        $base = fn () => WalletEvent::query()->where('created_at', '>=', $today);

        return [
            'events_today' => $base()->count(),
            'failed_today' => $base()->where('status', 'failed')->count(),
            'members_today' => $base()->where('actor_type', 'user')->distinct('user_id')->count('user_id'),
            'pin_failures_24h' => WalletEvent::where('created_at', '>=', now()->subDay())
                ->where('status', 'failed')->where('message', 'like', '%PIN%')->count(),
            'money_out_today' => (float) $base()->where('status', 'success')
                ->whereIn('type', ['send_money', 'request_paid', 'bank_withdrawal_requested', 'ticket_paid_wallet', 'tag_item_bought'])
                ->sum('amount'),
        ];
    }

    private function present(WalletEvent $e): array
    {
        $person = fn ($u) => $u ? ['id' => $u->id, 'name' => $u->name, 'email' => $u->email, 'tag' => $u->linkup_id ?? null] : null;

        return [
            'id' => $e->id,
            'at' => optional($e->created_at)->toISOString(),
            'type' => $e->type,
            'label' => WalletActivity::label($e->type),
            'status' => $e->status,
            'amount' => $e->amount !== null ? (float) $e->amount : null,
            'currency' => $e->currency,
            'coins' => $e->meta['coins'] ?? null,
            'user' => $person($e->user) ?? ($e->user_id ? ['id' => $e->user_id, 'name' => 'Deleted user #' . $e->user_id] : null),
            'counterparty' => $person($e->counterparty),
            'actor_type' => $e->actor_type,
            'actor' => $e->actor_type === 'admin' ? $person($e->actor) : null,
            'reference' => $e->reference,
            'message' => $e->message,
            'note' => $e->meta['note'] ?? null,
            'channel' => $e->channel,
            'ip' => $e->ip,
            'device' => $this->device((string) $e->user_agent),
        ];
    }

    private function device(string $ua): string
    {
        if ($ua === '') return '—';
        $os = match (true) {
            str_contains($ua, 'iPhone') || str_contains($ua, 'iPad') => 'iOS',
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'Mac OS') => 'Mac',
            default => 'Other',
        };
        $app = match (true) {
            str_contains($ua, 'okhttp') || str_contains($ua, 'Dart') || str_contains($ua, 'CFNetwork') => 'App',
            str_contains($ua, 'Edg/') => 'Edge',
            str_contains($ua, 'Chrome') => 'Chrome',
            str_contains($ua, 'Safari') => 'Safari',
            str_contains($ua, 'Firefox') => 'Firefox',
            default => 'Browser',
        };

        return "$app · $os";
    }
}
