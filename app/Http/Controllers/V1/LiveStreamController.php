<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\LiveStreamCategories;
use App\Models\LiveStreamGumlet;
use App\Models\PrivateLiveStreamSub;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LiveStreamController extends Controller
{
    // mirrors the "Live Network" data built by Frontend\LiveStreamGumletController::index
    // (viewer_count/gift_count/image_url come from LiveStreamGumlet's own accessors —
    // is_subscribed is computed the same way index() does it)
    //
    // ?category= filters the same way the web modal's topic tabs do (Index.vue's
    // filteredLiveStreams): omitted/"All" returns everything, "Private" matches on
    // visibility, anything else matches broadcast_type exactly.
    public function network(Request $request)
    {
        $user = Auth::user();
        $category = $request->query('category', 'All');

        $query = LiveStreamGumlet::with('user')->where('status', 'live');

        if ($category === 'Private') {
            $query->where('visibility', 'private');
        } elseif ($category !== 'All') {
            $query->where('broadcast_type', $category);
        }

        $streams = $query->get();

        $streams->each(function (LiveStreamGumlet $stream) use ($user) {
            $stream->is_subscribed = $this->isSubscribed($stream, $user);
        });

        $categories = LiveStreamCategories::query()->orderBy('category')->get();

        return response()->json([
            'status' => true,
            'data' => [
                'streams' => $streams,
                'categories' => $categories->pluck('category')->values(),
                'active_category' => $category,
            ],
        ], 200);
    }

    // mirrors LiveStreamCategories list used to build the "Live Network" topic tabs
    public function categories()
    {
        $categories = LiveStreamCategories::query()->orderBy('category')->get();

        return response()->json([
            'status' => true,
            'data' => [
                'categories' => $categories->pluck('category')->values(),
            ],
        ], 200);
    }

    private function isSubscribed(LiveStreamGumlet $stream, $user): bool
    {
        if ($stream->user_id == $user->id) {
            return true;
        }

        if ($stream->visibility !== 'private') {
            return true;
        }

        $hasOneTime = PrivateLiveStreamSub::where('pay_user_id', $user->id)
            ->where('stream_id', $stream->id)
            ->where('status', 'active')
            ->exists();

        $monthly = PrivateLiveStreamSub::where('pay_user_id', $user->id)
            ->where('user_streamer_id', $stream->user_id)
            ->whereRaw('LOWER(payment_type) = ?', ['monthly'])
            ->whereIn('status', ['active', 'Active'])
            ->orderByDesc('created_at')
            ->first();

        $hasMonthly = $monthly && Carbon::parse($monthly->created_at)->addDays(30)->isFuture();

        return $hasOneTime || $hasMonthly;
    }
}
