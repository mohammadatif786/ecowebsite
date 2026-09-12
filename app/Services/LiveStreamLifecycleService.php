<?php

namespace App\Services;

use App\Events\StreamEnded;
use App\Models\LiveComment;
use App\Models\LivePoll;
use App\Models\LivePollOption;
use App\Models\LiveQna;
use App\Models\LiveStreamGumlet;
use App\Models\LiveViewer;
use Illuminate\Support\Facades\DB;

class LiveStreamLifecycleService
{
    public function complete(LiveStreamGumlet $stream): bool
    {
        $completedStream = DB::transaction(function () use ($stream) {
            $stream = LiveStreamGumlet::query()->lockForUpdate()->find($stream->id);

            // This prevents duplicate unload, heartbeat, and retry requests from
            // changing a completed/cancelled stream or broadcasting twice.
            if ($stream === null || $stream->status !== 'live') {
                return null;
            }

            $pollIds = LivePoll::where('live_stream_gumlet_id', $stream->id)->pluck('id');

            if ($pollIds->isNotEmpty()) {
                LivePollOption::whereIn('live_poll_id', $pollIds)->delete();
                LivePoll::whereIn('id', $pollIds)->delete();
            }

            LiveQna::where('live_stream_gumlet_id', $stream->id)->delete();
            LiveComment::where('live_stream_gumlet_id', $stream->id)->delete();

            LiveViewer::where('live_stream_gumlet_id', $stream->id)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'left_at' => now(),
                ]);

            $stream->forceFill([
                'status' => 'completed',
                'ended_at' => now(),
            ])->save();

            return $stream;
        });

        if ($completedStream === null) {
            return false;
        }

        broadcast(new StreamEnded($completedStream));

        return true;
    }
}
