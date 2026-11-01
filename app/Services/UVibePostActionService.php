<?php

namespace App\Services;

use App\Actions\InitiateWalletRechargeAction;
use App\Domain\Vibes\Enums\VibeStatus;
use App\DTOs\WalletRechargeDTO;
use App\Models\LinkUpEvent;
use App\Models\Notification;
use App\Models\User;
use App\Models\UvibeApplication;
use App\Models\UvibeBoost;
use App\Models\UvibePostReport;
use App\Models\Vibe;
use App\Support\UVibeOptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Everything a member can do with a U Vibe post once it is in the feed: edit / delete (own posts),
 * hide / report / repost (other people's), Boost, and for Campus Gigs apply and get paid.
 *
 * The website and the mobile API call this same class (through UVibePostController), so the rules
 * below are the rules everywhere. Every method throws a ValidationException (422, with a message
 * a member can read) when the action isn't allowed.
 *
 * Money:
 *  - Boost spends the sender's coins and nothing else. It is appreciation: the poster is not paid
 *    coins, and it never touches the cash-convertible gift (GiftCoins) records.
 *  - A gig is paid by the person who posted it, from their own wallet, to the worker who applied
 *    (the wallet's normal send-money action, protected by the transaction PIN). Nobody can pay
 *    themselves, and the worker never "marks themselves paid".
 */
class UVibePostActionService
{
    public function __construct(
        private readonly UniversityService $universities,
        private readonly UVibeFeedService $feed,
    ) {}

    /* ---------------------------------------------------------------- own posts */

    /**
     * Edit title / words / price / linked event. Gigs keep their pay and can't be re-linked
     * (people may already have applied for that pay).
     *
     * @param  array{title?:string, caption?:?string, price?:mixed, event_id?:?int}  $data
     */
    public function edit(User $user, Vibe $vibe, array $data): Vibe
    {
        $this->mustOwn($user, $vibe);

        $title = trim((string) ($data['title'] ?? ''));
        $caption = trim((string) ($data['caption'] ?? ''));

        if ($title === '') {
            $this->fail('title', 'Add a title.');
        }
        if ($caption === '' && $vibe->media->isEmpty()) {
            // A text card is its words; a photo post may have no text.
            $this->fail('caption', 'Write something for your text card.');
        }

        $isGig = $vibe->category === 'opportunity';
        $update = ['title' => $title, 'caption' => $caption === '' ? null : $caption, 'edited_at' => now()];

        if (! $isGig && array_key_exists('price', $data)) {
            $price = $data['price'] === null || $data['price'] === '' ? 0 : $data['price'];
            if (! is_numeric($price) || $price < 0 || $price > 100000) {
                $this->fail('price', 'Enter a price between 0 and 100,000.');
            }
            $update['price'] = $price;
        }

        $eventId = null;
        $linkEvent = ! $isGig && array_key_exists('event_id', $data);
        if ($linkEvent && filled($data['event_id'])) {
            $eventId = (int) $data['event_id'];
            $allowed = LinkUpEvent::query()->whereKey($eventId)->where(fn ($q) => $q
                ->where('user_id', $user->id)
                ->orWhereIn('id', $vibe->events->pluck('id')))->exists();
            if (! $allowed) {
                $this->fail('event_id', 'You can only link your own events.');
            }
        }

        DB::transaction(function () use ($vibe, $update, $linkEvent, $eventId, $caption) {
            $vibe->update($update);

            if ($linkEvent) {
                $vibe->events()->sync($eventId ? [$eventId] : []);
            }

            // Hashtags follow the words.
            DB::table('vibe_hashtags')->where('vibe_id', $vibe->id)->delete();
            if ($caption !== '' && preg_match_all('/#(\w+)/', $caption, $m) && ! empty($m[1])) {
                DB::table('vibe_hashtags')->insert(collect($m[1])->map(fn ($tag) => [
                    'vibe_id' => $vibe->id,
                    'tag' => strtolower($tag),
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->all());
            }
        });

        return $this->fresh($vibe);
    }

    public function delete(User $user, Vibe $vibe): void
    {
        $this->mustOwn($user, $vibe);

        $vibe->delete();
    }

    /* ---------------------------------------------------------------- other people's posts */

    /** "Hide this post": it disappears from this member's feed only. */
    public function hide(User $user, Vibe $vibe): void
    {
        $this->mustNotOwn($user, $vibe, 'That’s your own post — delete it instead.');

        DB::table('uvibe_hidden_posts')->insertOrIgnore([
            'user_id' => $user->id,
            'vibe_id' => $vibe->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Report a post to the moderation team. The poster never learns who reported.
     *
     * @return array{already:bool}
     */
    public function report(User $user, Vibe $vibe, string $reasonKey, ?string $notes = null): array
    {
        $this->mustNotOwn($user, $vibe, 'You can’t report your own post.');

        $reason = UVibeOptions::reportReason($reasonKey);
        if (! $reason) {
            $this->fail('reason', 'Choose what’s wrong with the post.');
        }

        $notes = trim((string) $notes);
        if ($reason[0] === 'other' && $notes === '') {
            $this->fail('notes', 'Tell us briefly what’s wrong.');
        }

        if (UvibePostReport::where('vibe_id', $vibe->id)->where('reporter_id', $user->id)->exists()) {
            return ['already' => true];
        }

        $excerpt = trim(($vibe->title ?? '').($vibe->caption ? ' — '.$vibe->caption : ''));

        UvibePostReport::create([
            'vibe_id' => $vibe->id,
            'reporter_id' => $user->id,
            'author_id' => $vibe->created_by,
            'reason_key' => $reason[0],
            'reason' => $reason[2],
            'severity' => $reason[4],
            'notes' => $notes === '' ? null : mb_substr($notes, 0, 500),
            'excerpt' => mb_substr($excerpt, 0, 220),
            'campus' => $vibe->campus,
            'has_media' => $vibe->media->isNotEmpty(),
            'status' => 'Open',
        ]);

        return ['already' => false];
    }

    /**
     * Repost: a copy of the post on the member's own (main) campus, under their name, pointing
     * back at the original. Boosts, likes and comments start from zero on the copy.
     *
     * @return array{post:Vibe, original:Vibe}
     */
    public function repost(User $user, Vibe $vibe): array
    {
        $this->mustNotOwn($user, $vibe, 'That’s already your own post.');

        if (Vibe::where('created_by', $user->id)->where('reposted_from_id', $vibe->id)->exists()) {
            $this->fail('post', 'You already reposted this.');
        }

        // Same rule as posting: a member needs a university first.
        $university = $this->universities->postingUniversity($user, null, null);
        $from = $this->feed->posterName($vibe);

        $copy = DB::transaction(function () use ($user, $vibe, $university, $from) {
            $copy = Vibe::create([
                'created_by' => $user->id,
                'publisher_type' => 'user',
                'publisher_id' => $user->id,
                'caption' => $vibe->caption,
                'title' => $vibe->title,
                'category' => $vibe->category,
                'campus' => $university->name,
                'university_id' => $university->id,
                'price' => $vibe->price,
                'is_uvibe' => true,
                'show_on_vibes' => false,
                'text_bg' => $vibe->text_bg,
                'text_font' => $vibe->text_font,
                'text_style' => $vibe->text_style,
                'location_name' => $vibe->location_name,
                'location_place_id' => $vibe->location_place_id,
                'latitude' => $vibe->latitude,
                'longitude' => $vibe->longitude,
                'allow_coin_gifts' => $vibe->allow_coin_gifts,
                'visibility' => 'public',
                'status' => VibeStatus::Published,
                'published_at' => now(),
                'reposted_from_id' => $vibe->id,
                'reposted_from_name' => mb_substr($from, 0, 120),
            ]);

            foreach ($vibe->media as $m) {
                $copy->media()->create([
                    'media_type' => $m->media_type,
                    'source' => $m->source,
                    'disk' => $m->disk,
                    'path' => $m->path,
                    'thumbnail_path' => $m->thumbnail_path,
                    'original_name' => $m->original_name,
                    'mime_type' => $m->mime_type,
                    'size' => $m->size,
                    'width' => $m->width,
                    'height' => $m->height,
                    'duration' => $m->duration,
                    'processing_status' => $m->processing_status,
                    'metadata' => $m->metadata,
                    'sort_order' => $m->sort_order,
                ]);
            }

            $products = $vibe->products->pluck('id')->all();
            $events = $vibe->events->pluck('id')->all();
            if ($products) {
                $copy->products()->sync($products);
            }
            if ($events) {
                $copy->events()->sync($events);
            }

            $vibe->increment('reposts_count');

            return $copy;
        });

        return ['post' => $this->fresh($copy), 'original' => $this->fresh($vibe)];
    }

    /* ---------------------------------------------------------------- Boost */

    /**
     * Send a Boost (coins) to a post. Returns the sender's new coin balance.
     *
     * @return array{coins:int, boost_coins:int, gift:array}
     */
    public function boost(User $user, Vibe $vibe, string $giftKey): array
    {
        $gift = UVibeOptions::boost($giftKey);
        if (! $gift) {
            $this->fail('gift', 'Choose a Boost to send.');
        }
        $this->mustNotOwn($user, $vibe, 'You can’t Boost your own post.');

        if (! $vibe->allow_coin_gifts) {
            $this->fail('post', 'The poster turned Boosts off for this post.');
        }

        [, $label, $emoji, $cost] = $gift;

        DB::transaction(function () use ($user, $vibe, $giftKey, $label, $emoji, $cost) {
            // One guarded statement: the coins are taken only if the member still has them.
            $taken = User::whereKey($user->id)->where('coins', '>=', $cost)->decrement('coins', $cost);
            if (! $taken) {
                $this->fail('coins', 'Not enough coins — top up your wallet first.');
            }

            UvibeBoost::create(['vibe_id' => $vibe->id, 'user_id' => $user->id, 'gift' => $giftKey, 'coins' => $cost]);
            $vibe->increment('boost_coins', $cost);

            Notification::create([
                'title' => 'You got a Boost!',
                'message' => "{$user->name} sent a {$emoji} {$label} Boost to “{$vibe->title}”.",
                'send_by' => $user->id,
                'user_id' => $vibe->created_by,
                'type' => 'gift',
                'context' => 'uvibe_boost',
                'unread' => true,
                'avatar' => $user->avatar ?? null,
                'metadata' => ['vibe_id' => $vibe->id, 'gift' => $giftKey, 'coins' => $cost, 'sender_name' => $user->name],
            ]);
        });

        return [
            'coins' => (int) User::whereKey($user->id)->value('coins'),
            'boost_coins' => (int) Vibe::whereKey($vibe->id)->value('boost_coins'),
            'gift' => ['key' => $giftKey, 'label' => $label, 'emoji' => $emoji, 'coins' => $cost],
        ];
    }

    /* ---------------------------------------------------------------- Campus Gigs */

    /**
     * Apply to a gig.
     *
     * @return array{already:bool, application:UvibeApplication}
     */
    public function apply(User $user, Vibe $vibe): array
    {
        if ($vibe->category !== 'opportunity') {
            $this->fail('post', 'This post isn’t a Campus Gig.');
        }
        $this->mustNotOwn($user, $vibe, 'You posted this gig — people apply to it.');

        $existing = UvibeApplication::where('vibe_id', $vibe->id)->where('user_id', $user->id)->first();
        if ($existing) {
            return ['already' => true, 'application' => $existing];
        }

        $application = DB::transaction(function () use ($user, $vibe) {
            $application = UvibeApplication::create(['vibe_id' => $vibe->id, 'user_id' => $user->id, 'status' => 'applied']);

            Notification::create([
                'title' => 'New gig applicant',
                'message' => "{$user->name} applied to your gig “{$vibe->title}”.",
                'send_by' => $user->id,
                'user_id' => $vibe->created_by,
                'type' => 'gig',
                'context' => 'uvibe_gig_apply',
                'unread' => true,
                'avatar' => $user->avatar ?? null,
                'metadata' => ['vibe_id' => $vibe->id, 'application_id' => $application->id, 'applicant_id' => $user->id],
            ]);

            return $application;
        });

        return ['already' => false, 'application' => $application];
    }

    /**
     * Who applied to a gig (for the person who posted it).
     *
     * @return array<int,array{id:int,user_id:int,name:string,avatar:?string,status:string,applied:string,paid_at:?string,paid_amount:?float}>
     */
    public function applicants(User $user, Vibe $vibe): array
    {
        $this->mustOwn($user, $vibe);

        return UvibeApplication::with('user')
            ->where('vibe_id', $vibe->id)
            ->orderBy('id')
            ->get()
            ->map(fn (UvibeApplication $a) => [
                'id' => $a->id,
                'user_id' => $a->user_id,
                'name' => $a->user?->name ?? 'Member',
                'avatar' => $a->user?->avatar,
                'status' => $a->status,
                'applied' => $a->created_at?->diffForHumans() ?? '',
                'paid_at' => $a->paid_at?->toISOString(),
                'paid_amount' => $a->paid_amount !== null ? (float) $a->paid_amount : null,
            ])
            ->all();
    }

    /**
     * Pay a worker the gig's pay, from the poster's wallet. Needs the poster's transaction PIN.
     */
    public function pay(User $poster, Vibe $vibe, UvibeApplication $application, string $pin): UvibeApplication
    {
        $this->mustOwn($poster, $vibe);
        abort_unless((int) $application->vibe_id === (int) $vibe->id, 404);

        if ($vibe->category !== 'opportunity') {
            $this->fail('post', 'This post isn’t a Campus Gig.');
        }

        $pay = (float) $vibe->price;
        if ($pay < 1 || $pay != floor($pay)) {
            $this->fail('post', 'This gig needs a whole-dollar pay amount before you can pay a worker.');
        }
        if ((int) $application->user_id === (int) $poster->id) {
            $this->fail('post', 'You can’t pay yourself.');
        }

        // Same PIN check as the wallet's send-money.
        if (empty($poster->transaction_pin)) {
            $this->fail('pin', 'Transaction PIN is not set. Please set your PIN in Settings first.');
        }
        if (! Hash::check($pin, $poster->transaction_pin)) {
            $this->fail('pin', 'Incorrect transaction PIN. Please try again.');
        }

        return DB::transaction(function () use ($poster, $vibe, $application, $pay) {
            // Lock the application so a double tap can't pay twice.
            $locked = UvibeApplication::whereKey($application->id)->lockForUpdate()->first();
            if (! $locked || $locked->status === 'paid') {
                $this->fail('post', 'This worker is already paid.');
            }

            try {
                app(InitiateWalletRechargeAction::class)->sendMoneyToUser(
                    new WalletRechargeDTO($poster->id, (int) $pay, null, (int) $locked->user_id, 'Campus Gig · '.mb_substr((string) $vibe->title, 0, 120)),
                );
            } catch (\RuntimeException $e) {
                $this->fail('post', str_contains($e->getMessage(), 'Insufficient')
                    ? 'Not enough money in your wallet — top up first.'
                    : $e->getMessage());
            }

            $locked->update(['status' => 'paid', 'paid_amount' => $pay, 'paid_at' => now()]);

            return $locked->load('user');
        });
    }

    /* ---------------------------------------------------------------- helpers */

    /** The post as the feed shows it to this member. */
    public function present(Vibe $vibe, User $viewer): array
    {
        return $this->feed->present($this->fresh($vibe), $viewer);
    }

    private function fresh(Vibe $vibe): Vibe
    {
        return Vibe::with(UVibeFeedService::RELATIONS)->findOrFail($vibe->id);
    }

    private function mustOwn(User $user, Vibe $vibe): void
    {
        abort_unless((int) $vibe->created_by === (int) $user->id, 403, 'This isn’t your post.');
    }

    private function mustNotOwn(User $user, Vibe $vibe, string $message): void
    {
        if ((int) $vibe->created_by === (int) $user->id) {
            $this->fail('post', $message);
        }
    }

    private function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
