<?php

namespace App\Http\Controllers\Admin\EWallet;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\User;
use App\Repositories\Admin\AdminOverviewRepository;
use App\Services\Admin\AdminOverviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/** E-Wallet → Transactions Ledger: the full wallet ledger, page by page (not just the latest 300). */
class WalletLedgerController extends Controller
{
    private const TYPE_FILTERS = [
        'Wallet Load' => ['processor_id' => 'deposit', 'meta' => 'recharge'],
        'Send Money' => ['processor_id' => 'transfer', 'p2p' => true],
        'Cash Out' => ['processor_id' => 'transfer', 'meta' => 'bank_withdrawal'],
        'Cash Out Refund' => ['processor_id' => 'transfer', 'meta' => 'bank_withdrawal_refund'],
        'Merchant Pay' => ['processor_id' => 'charge'],
        'Coin Purchase' => ['processor_id' => ['conversion_credit', 'conversion_debit']],
        'LinkUp Live' => ['processor_id' => 'live deposit'],
    ];

    public function index(Request $request, AdminOverviewService $service): JsonResponse
    {
        $f = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', 'string', 'max:40'],
            'status' => ['nullable', 'in:success,pending,failed'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);
        $types = AdminOverviewRepository::USER_TYPES;

        $q = Transaction::query()->with(['from', 'to']);

        if ($rule = self::TYPE_FILTERS[$f['type'] ?? ''] ?? null) {
            is_array($rule['processor_id']) ? $q->whereIn('processor_id', $rule['processor_id']) : $q->where('processor_id', $rule['processor_id']);
            if (isset($rule['meta'])) $q->where('meta->type', $rule['meta']);
            if (! empty($rule['p2p'])) $q->whereIn('from_type', $types)->whereIn('to_type', $types);
        }
        if ($s = $f['status'] ?? null) {
            $q->whereIn('status', match ($s) {
                'success' => ['success'],
                'pending' => ['pending', 'awaiting_approval', 'awaiting_payment', 'in_progress', 'on_hold'],
                default => ['failed', 'canceled', 'expired', 'refunded'],
            });
        }
        if ($d = $f['from'] ?? null) $q->where('created_at', '>=', Carbon::parse($d)->startOfDay());
        if ($d = $f['to'] ?? null) $q->where('created_at', '<=', Carbon::parse($d)->endOfDay());
        if ($term = $f['search'] ?? null) {
            $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $term) . '%';
            $ids = User::where('name', 'like', $like)->orWhere('email', 'like', $like)->orWhere('linkup_id', 'like', $like)->limit(200)->pluck('id');
            $q->where(function ($w) use ($ids, $types, $term) {
                $w->where(fn ($x) => $x->whereIn('from_type', $types)->whereIn('from_id', $ids))
                    ->orWhere(fn ($x) => $x->whereIn('to_type', $types)->whereIn('to_id', $ids))
                    ->orWhere('uuid', $term);
            });
        }

        $page = $q->orderByDesc('id')->paginate(50);

        return response()->json([
            'data' => collect($page->items())->map(fn ($tx) => $service->mapWalletMovement($tx))->values(),
            'meta' => ['current_page' => $page->currentPage(), 'last_page' => $page->lastPage(), 'total' => $page->total()],
            'types' => array_keys(self::TYPE_FILTERS),
        ]);
    }
}
