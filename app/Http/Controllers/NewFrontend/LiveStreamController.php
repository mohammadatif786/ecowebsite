<?php

namespace App\Http\Controllers\NewFrontend;

use App\Domain\LiveStreams\Actions\CreateLiveStreamAction;
use App\Domain\LiveStreams\Actions\EndLiveStreamAction;
use App\Domain\LiveStreams\DTOs\CreateLiveStreamData;
use App\Domain\LiveStreams\Services\StreamingProviderServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\LiveStreams\EndLiveStreamRequest;
use App\Http\Requests\LiveStreams\LiveAnalyticsRequest;
use App\Http\Requests\LiveStreams\StoreLiveStreamRequest;
use App\Http\Requests\LiveStreams\TransferLiveEarningsRequest;
use App\Http\Resources\LiveStreamResource;
use App\Models\LinkupLiveGift;
use App\Models\LiveStreamGumlet;
use App\Services\LiveStreamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LiveStreamController extends Controller
{
    protected $liveStreamService;

    public function __construct(LiveStreamService $liveStreamService)
    {
        $this->liveStreamService = $liveStreamService;
    }

    public function index()
    {
        $user = Auth::user();

        return Inertia::render('new_front/live/Index', [
            'serverStreams' => $this->liveStreamService->getStreamsForFrontend(),
            'serverCategories' => $this->liveStreamService->getCategories(),
            'serverGifts' => $this->activeGiftCatalog(),
            'balance' => (int) ($user->coins ?? 0),
            'serverEarnings' => $this->liveStreamService->getEarningsData($user),
        ]);
    }

    public function watch(StreamingProviderServiceInterface $provider, ?string $stream = null)
    {
        $user = Auth::user();

        if ($stream !== null && ctype_digit($stream)) {
            $legacyStream = LiveStreamGumlet::query()->find($stream);

            return $legacyStream?->public_id
                ? redirect()->route('new_frontend.live.watch', $legacyStream->public_id)
                : abort(404);
        }

        $streamModel = LiveStreamGumlet::with('user')->where('public_id', $stream)->first();

        if (! $streamModel) {
            abort(404);
        }

        $this->authorize('view', $streamModel);

        $agoraData = null;
        if ($streamModel->status === 'live' || $streamModel->status === 'created') {
            $credentials = (string) $streamModel->user_id === (string) $user->id
                ? $provider->broadcasterCredentials($streamModel, $user)
                : $provider->viewerCredentials($streamModel, $user);

            $agoraData = [
                'appId' => $credentials['app_id'],
                'token' => $credentials['token'],
                'channel' => $credentials['channel'],
                'uid' => $credentials['uid'],
                'role' => (string) $streamModel->user_id === (string) $user->id ? 1 : 2,
            ];
        }

        return Inertia::render('new_front/live/Watch', [
            'serverStream' => [
                'id' => $streamModel->id,
                'public_id' => $streamModel->public_id,
                'title' => $streamModel->title,
                'host' => $streamModel->user?->name,
                'hostId' => $streamModel->user_id,
                'hostAvatar' => $streamModel->user?->avatar,
                'category' => $streamModel->broadcast_type,
                'location' => $streamModel->location,
                'viewers' => $streamModel->viewer_count,
                'likes' => $streamModel->like_count,
                'gifts' => (int) \App\Models\LiveGift::where('live_stream_gumlet_id', $streamModel->id)->sum('qty'),
                'sessionCoins' => (int) \App\Models\LiveGift::where('live_stream_gumlet_id', $streamModel->id)->sum('coins'),
                'cover' => $streamModel->image_url,
                'thumb' => $streamModel->image_url,
                'products' => $streamModel->products ?? [],
                'guests' => $streamModel->guests ?? [],
                'viewerCoins' => (int) ($user->coins ?? 0),
                'isHost' => $streamModel->user_id == $user->id,
            ],
            'agoraData' => $agoraData,
            'serverGifts' => $this->activeGiftCatalog(),
        ]);
    }

    private function activeGiftCatalog(): array
    {
        return LinkupLiveGift::query()
            ->where('active', true)
            ->orderBy('coins')
            ->get(['id', 'name', 'emoji', 'coins'])
            ->map(fn (LinkupLiveGift $gift) => [
                'id' => $gift->id,
                'name' => $gift->name,
                'emoji' => $gift->emoji ?: '🎁',
                'coins' => (int) $gift->coins,
            ])
            ->values()
            ->all();
    }

    public function start(StoreLiveStreamRequest $request, CreateLiveStreamAction $create, StreamingProviderServiceInterface $provider)
    {
        $user = Auth::user();
        $this->authorize('create', LiveStreamGumlet::class);
        $stream = $create->execute($user, CreateLiveStreamData::fromValidated($request->validated(), $request->file('cover_image')));
        $credentials = $provider->broadcasterCredentials($stream, $user);

        return response()->json([
            'success' => true,
            'stream' => new LiveStreamResource($stream),
            'credentials' => $credentials,
            'redirect' => route('new_frontend.live.watch', $stream->public_id),
        ]);
    }

    public function credentials(Request $request, LiveStreamGumlet $stream, StreamingProviderServiceInterface $provider)
    {
        $this->authorize('view', $stream);

        return response()->json([
            'credentials' => $provider->viewerCredentials($stream, $request->user()),
        ]);
    }

    public function end(EndLiveStreamRequest $request, LiveStreamGumlet $stream, EndLiveStreamAction $end)
    {
        $this->authorize('end', $stream);

        return response()->json(['success' => true, 'stream' => new LiveStreamResource($end->execute($stream))]);
    }

    public function analyticsData(LiveAnalyticsRequest $request)
    {
        $range = $request->validated('range', '7d');

        return response()->json($this->liveStreamService->getAnalyticsData($request->user(), $range));
    }

    public function transferEarnings(TransferLiveEarningsRequest $request)
    {
        $result = $this->liveStreamService->transferEarnings(
            $request->user(),
            $request->validated('idempotency_key')
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Transfer successful!',
                'transferred' => $result['transferred'],
                'new_balance' => $result['new_balance'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Transfer failed',
        ], 422);
    }
}
