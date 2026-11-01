<?php

namespace App\Services;

use App\Models\GiftCoins;
use App\Models\Notification;
use App\Models\User;
use App\Models\UvibeBoost;
use App\Models\Vibe;
use App\Models\VibeMessage;
use App\Support\UVibeOptions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * "Say Thank You" (the 🙏 in the U Vibe header), for the website and the mobile app alike.
 *
 * Who you can thank: everyone who sent you something you haven't thanked them for yet —
 *  - a Vibes gift (coins, same list as the Vibes page), and
 *  - a Boost on one of your U Vibe posts.
 * Each person is listed once, with their newest gift or Boost. Thanking them clears everything
 * they sent so far, puts your message in your Vibes chat with them and notifies them.
 *
 * Only someone who really sent you something can be thanked — the server checks, so this can't be
 * used to push messages at people you have nothing to do with.
 */
class UVibeThankYouService
{
    /**
     * @return array{senders:array<int,array>, template:string, max:int}
     */
    public function senders(User $user): array
    {
        $senders = $this->pending($user)
            ->sortByDesc(fn (array $r) => [$r['at']->getTimestamp(), $r['id']])
            ->groupBy('user_id')
            ->map(function (Collection $items) {
                $latest = $items->first();
                $member = $latest['member'];

                return [
                    'user_id' => (int) $member->id,
                    'name' => (string) ($member->name ?: 'Member'),
                    'handle' => $member->linkup_id ? '@'.$member->linkup_id : null,
                    'avatar' => $member->avatar,
                    'gift' => $latest['label'],
                    'emoji' => $latest['emoji'],
                    'coins' => (int) $latest['coins'],
                    'source' => $latest['source'],
                    // How many gifts/Boosts are waiting for this one thank-you.
                    'count' => $items->count(),
                    'time' => $latest['at']->diffForHumans(),
                    'at' => $latest['at']->toISOString(),
                ];
            })
            ->values()
            ->all();

        return ['senders' => $senders, 'template' => UVibeOptions::THANK_YOU_TEMPLATE, 'max' => UVibeOptions::THANK_YOU_MAX];
    }

    /**
     * Thank someone. Returns the refreshed list so the window can show who is left.
     *
     * @return array{name:string, senders:array<int,array>}
     */
    public function thank(User $user, int $toUserId, string $message): array
    {
        $message = trim($message);
        if ($message === '') {
            $this->fail('message', 'Write a thank-you message first.');
        }
        if (mb_strlen($message) > UVibeOptions::THANK_YOU_MAX) {
            $this->fail('message', 'Keep it under '.UVibeOptions::THANK_YOU_MAX.' characters.');
        }
        if ($toUserId === (int) $user->id) {
            $this->fail('user_id', 'You can’t thank yourself.');
        }

        $to = User::find($toUserId);
        if (! $to) {
            $this->fail('user_id', 'That member is no longer here.');
        }

        DB::transaction(function () use ($user, $to, $message) {
            $gifts = $this->giftsFrom($user, $to)->lockForUpdate()->get(['id']);
            $boosts = $this->boostsFrom($user, $to)->lockForUpdate()->get(['id']);

            if ($gifts->isEmpty() && $boosts->isEmpty()) {
                $this->fail('user_id', 'You can only thank someone who sent you a gift or a Boost.');
            }

            GiftCoins::whereIn('id', $gifts->pluck('id'))->update(['responded_at' => now()]);
            UvibeBoost::whereIn('id', $boosts->pluck('id'))->update(['thanked_at' => now()]);

            // The thank-you lands in your Vibes chat with them…
            VibeMessage::create([
                'from_user_id' => $user->id,
                'to_user_id' => $to->id,
                'content' => $message,
                'type' => 'text',
            ]);

            // …and in their notifications (same as thanking from the Vibes page).
            Notification::create([
                'title' => 'Thank you for your support!',
                'message' => $message,
                'send_by' => $user->id,
                'user_id' => $to->id,
                'type' => 'gift',
                'context' => 'say_thank_you',
                'unread' => true,
                'avatar' => $user->avatar ?? null,
                'metadata' => ['sender_name' => $user->name],
            ]);
        });

        return ['name' => (string) ($to->name ?: 'them'), 'senders' => $this->senders($user)['senders']];
    }

    /* ---------------------------------------------------------------- what is waiting */

    /**
     * Every gift and Boost not yet thanked, one row each.
     *
     * @return Collection<int,array{id:int, user_id:int, member:User, label:string, emoji:string, coins:int, source:string, at:\Carbon\CarbonInterface}>
     */
    protected function pending(User $user): Collection
    {
        $rows = collect();

        $this->giftsFrom($user)->with('sender:id,name,avatar,linkup_id')->get()
            ->each(function (GiftCoins $g) use ($rows) {
                if ($g->sender) {
                    $rows->push([
                        'id' => (int) $g->id,
                        'user_id' => (int) $g->sender_id,
                        'member' => $g->sender,
                        'label' => (string) ($g->name ?: 'Gift'),
                        'emoji' => (string) ($g->emoji ?: '🎁'),
                        'coins' => (int) $g->coins,
                        'source' => 'vibes',
                        'at' => $g->created_at,
                    ]);
                }
            });

        $this->boostsFrom($user)->with('user:id,name,avatar,linkup_id')->get()
            ->each(function (UvibeBoost $b) use ($rows) {
                if ($b->user) {
                    $catalog = UVibeOptions::boost($b->gift);
                    $rows->push([
                        'id' => (int) $b->id,
                        'user_id' => (int) $b->user_id,
                        'member' => $b->user,
                        'label' => $catalog[1] ?? 'Boost',
                        'emoji' => $catalog[2] ?? '⚡',
                        'coins' => (int) $b->coins,
                        'source' => 'uvibe',
                        'at' => $b->created_at,
                    ]);
                }
            });

        return $rows->filter(fn (array $r) => $r['at'] !== null);
    }

    /** Vibes gifts sent to this member that they haven't answered — the same ones the Vibes page lists. */
    protected function giftsFrom(User $user, ?User $sender = null)
    {
        return GiftCoins::query()
            ->where('recieved_id', $user->id)
            ->where('sender_id', '!=', $user->id)
            ->whereNull('responded_at')
            ->where(fn ($q) => $q->where('source', 'vibe')->orWhereNotNull('vibe_id'))
            ->when($sender, fn ($q) => $q->where('sender_id', $sender->id));
    }

    /** Boosts sent to this member's U Vibe posts that they haven't thanked. */
    protected function boostsFrom(User $user, ?User $sender = null)
    {
        return UvibeBoost::query()
            ->whereNull('thanked_at')
            ->where('user_id', '!=', $user->id)
            ->whereIn('vibe_id', Vibe::query()->where('created_by', $user->id)->select('id'))
            ->when($sender, fn ($q) => $q->where('user_id', $sender->id));
    }

    protected function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
