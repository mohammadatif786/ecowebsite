<?php

namespace App\Support;

use App\Domain\Vibes\Enums\VibeStatus;
use App\Models\ClubFete;
use App\Models\LinkUpEvent;
use App\Models\OrganizerProfile;
use App\Models\User;
use App\Models\UserCustomPublisher;
use App\Models\Vibe;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * One shape for a Vibes post, used by the feed, a single post and a creator's
 * profile (app API). Keeps those three answers identical.
 */
class VibeFeedPresenter
{
    public const RELATIONS = ['creator', 'publisher', 'media', 'products.user', 'events.user', 'events.tickets', 'events.eventDetails'];

    /**
     * Published Vibes the viewer may see: visibility rules, blocked users and
     * posts the viewer hid. U Vibe posts only when "Also post on Vibes" was ticked.
     * No eager loads — use it for counts and id subqueries.
     */
    public static function base(?User $viewer): Builder
    {
        return Vibe::query()
            ->where('status', VibeStatus::Published)
            ->where(fn ($q) => $q->where('is_uvibe', false)->orWhere('show_on_vibes', true))
            ->visibleTo($viewer)
            ->whereNotIn('vibes.id', DB::table('uvibe_hidden_posts')->where('user_id', $viewer?->id ?? 0)->select('vibe_id'));
    }

    /** `base()` plus everything `present()` needs. */
    public static function query(?User $viewer): Builder
    {
        return self::base($viewer)
            ->with(self::RELATIONS)
            ->withExists(['likes as liked_by_viewer' => fn ($q) => $q->where('user_id', $viewer?->id)]);
    }

    public static function present(Vibe $vibe): array
    {
        $vibe->loadMissing(self::RELATIONS);

        $mediaItems = $vibe->media->map(fn ($m) => [
            'id' => $m->id,
            'type' => $m->media_type->value,
            'url' => self::url($m->disk, $m->path),
            'thumbnail' => $m->thumbnail_path ? self::url($m->disk, $m->thumbnail_path) : null,
        ]);

        $isReel = $vibe->media->contains(fn ($m) => $m->media_type->value === 'video');
        [$publisherName, $publisherType, $publisherAvatar] = self::publisher($vibe);

        return [
            'id' => $vibe->id,
            'created_by' => $vibe->created_by,
            'handle' => $publisherName,
            'publisher_type' => $publisherType,
            'avatar' => $publisherAvatar,
            'location' => $vibe->location_name,
            'media' => $mediaItems->values(),
            'kind' => $isReel ? 'reel' : (! empty($vibe->text_bg) && $mediaItems->isEmpty() ? 'text' : 'photo'),
            'caption' => $vibe->caption,
            'text_bg' => $vibe->text_bg,
            'text_font' => $vibe->text_font,
            'likes_count' => $vibe->likes_count,
            'comments_count' => $vibe->comments_count,
            'shares_count' => $vibe->shares_count,
            'reposts_count' => $vibe->reposts_count ?? 0,
            'bigups_count' => $vibe->bigups_count,
            'is_liked' => (bool) $vibe->liked_by_viewer,
            'repost_of' => $vibe->reposted_from_id ? ['id' => (int) $vibe->reposted_from_id, 'name' => $vibe->reposted_from_name] : null,
            'allow_coin_gifts' => (bool) $vibe->allow_coin_gifts,
            'bigup' => $vibe->bigups_count,
            'created_at' => $vibe->published_at?->toISOString() ?? $vibe->created_at?->toISOString(),
            'shoppable' => self::tag($vibe) !== null,
            'tag' => self::tag($vibe),
        ];
    }

    /** The product or event tagged on a post (the first one), or null. */
    public static function tag(Vibe $vibe): ?array
    {
        if ($vibe->products->isNotEmpty()) {
            $p = $vibe->products->first();

            return [
                'kind' => 'product',
                'vibe_id' => $vibe->id,
                'affiliate_user_id' => $vibe->created_by,
                'id' => $p->id,
                'title' => $p->name,
                'price' => $p->price,
                'seller' => $p->user?->name ?? $p->seller?->name,
                'image' => self::image($p->cover_image) ?? $p->image_url,
                'in_stock' => (bool) $p->status && (int) ($p->qty ?? 0) > 0,
            ];
        }

        if ($vibe->events->isNotEmpty()) {
            $e = $vibe->events->first();

            return [
                'kind' => 'event',
                'vibe_id' => $vibe->id,
                'affiliate_user_id' => $vibe->created_by,
                'id' => $e->id,
                'slug' => $e->slug,
                'title' => $e->title,
                'price' => $e->tickets->min('price') ?? 0,
                'date' => self::eventDate($e)?->format('Y-m-d'),
                'location' => $e->venue,
                'seller' => $e->user?->name,
                'image' => self::image($e->featured_image) ?? $e->image_url,
            ];
        }

        return null;
    }

    /** [name, type, avatar] of whoever the post is published as. */
    public static function publisher(Vibe $vibe): array
    {
        $publisher = $vibe->publisher;

        return match (true) {
            $publisher instanceof User => [$publisher->name, 'user', $publisher->avatar],
            $publisher instanceof OrganizerProfile => [$publisher->organizer_name, 'organization', $vibe->creator?->avatar],
            $publisher instanceof ClubFete => [$publisher->name, 'group', $vibe->creator?->avatar],
            $publisher instanceof UserCustomPublisher => [$publisher->name, $publisher->type, $vibe->creator?->avatar],
            default => [$vibe->creator?->name ?? 'User', 'user', $vibe->creator?->avatar],
        };
    }

    /** When an event happens: its single date, a recurring event's first date, or its start time. */
    public static function eventDate(LinkUpEvent $event): ?CarbonInterface
    {
        $details = $event->eventDetails;
        $date = $details?->event_type === 'recurring' ? $details?->recurr_start_date : $details?->single_event_date;

        return $date ?? $event->start_time;
    }

    /** A stored image path (or full URL) as a public URL. */
    public static function image(?string $path): ?string
    {
        if (! $path) {
            return null;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        $path = ltrim($path, '/');
        while (str_starts_with($path, 'storage/')) {
            $path = ltrim(substr($path, 8), '/');
        }

        return asset('storage/'.$path);
    }

    private static function url(string $disk, string $path): string
    {
        return $disk === 'public' ? asset('storage/'.$path) : Storage::disk($disk)->url($path);
    }
}
