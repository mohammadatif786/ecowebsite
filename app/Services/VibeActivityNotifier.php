<?php

namespace App\Services;

use App\Models\BlockedUser;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserReel;
use App\Models\Vibe;
use Illuminate\Support\Facades\Storage;

/**
 * The Vibes activity feed (the ♥ in the Vibes header): likes, comments, replies, @mentions
 * and new followers. Never tells you about your own actions or about blocked people, and a
 * like that's undone and redone within a day doesn't notify twice.
 */
class VibeActivityNotifier
{
    /** Notification contexts that belong to the Vibes activity feed. */
    public const CONTEXTS = [
        'vibe_like', 'reel_like', 'vibe_comment', 'reel_comment', 'vibe_reply', 'reel_reply',
        'vibe_mention', 'reel_mention', 'vibe_follow', 'vibe_repost',
        'vibe_bigup', 'reel_bigup', 'reel_gift', 'say_thank_you',
    ];

    private const MAX_MENTIONS = 5;

    public function liked(User $actor, Vibe|UserReel $target): void
    {
        $owner = $this->ownerOf($target);
        $kind = $this->kind($target);

        $this->notify($actor, $owner, "{$kind}_like", 'like', 'New like', "{$actor->name} liked your {$this->noun($target)}.", $this->meta($target), dedupe: true);
    }

    /** A comment or reply: tells the owner, the person replied to, and anyone @mentioned. */
    public function commented(User $actor, Vibe|UserReel $target, string $text, ?int $commentId = null, ?User $repliedTo = null): void
    {
        $owner = $this->ownerOf($target);
        $kind = $this->kind($target);
        $meta = $this->meta($target) + ['comment_id' => $commentId, 'comment' => mb_substr($text, 0, 140)];
        $told = [$actor->id];

        if ($owner) {
            $this->notify($actor, $owner, "{$kind}_comment", 'comment', 'New comment', "{$actor->name} commented: ".mb_substr($text, 0, 80), $meta);
            $told[] = $owner->id;
        }

        if ($repliedTo && ! in_array($repliedTo->id, $told, true)) {
            $this->notify($actor, $repliedTo, "{$kind}_reply", 'comment', 'New reply', "{$actor->name} replied to your comment: ".mb_substr($text, 0, 80), $meta);
            $told[] = $repliedTo->id;
        }

        $this->mentioned($actor, $target, $text, $told, $meta);
    }

    /** @handles in a caption or comment. Returns the ids notified. */
    public function mentioned(User $actor, Vibe|UserReel $target, ?string $text, array $skip = [], ?array $meta = null): array
    {
        if (! $text || ! preg_match_all('/(?<![\w@])@([A-Za-z0-9_.]{2,40})/u', $text, $m)) {
            return [];
        }

        $handles = collect($m[1])->map(fn ($h) => rtrim($h, '.'))->unique()->take(self::MAX_MENTIONS);
        $kind = $this->kind($target);
        $meta ??= $this->meta($target);
        $notified = [];

        User::whereIn('linkup_id', $handles)->get()->each(function (User $user) use ($actor, $kind, $meta, $skip, &$notified) {
            if (in_array($user->id, $skip, true)) {
                return;
            }
            $this->notify($actor, $user, "{$kind}_mention", 'mention', 'You were mentioned', "{$actor->name} mentioned you in a {$kind}.", $meta);
            $notified[] = $user->id;
        });

        return $notified;
    }

    public function followed(User $actor, User $creator): void
    {
        $this->notify($actor, $creator, 'vibe_follow', 'follow', 'New follower', "{$actor->name} started following you.", ['follower_id' => $actor->id], dedupe: true);
    }

    public function reposted(User $actor, Vibe $original): void
    {
        $this->notify($actor, $this->ownerOf($original), 'vibe_repost', 'repost', 'Reposted', "{$actor->name} reposted your post.", $this->meta($original));
    }

    private function notify(User $actor, ?User $to, string $context, string $type, string $title, string $message, array $meta, bool $dedupe = false): void
    {
        if (! $to || (int) $to->id === (int) $actor->id || $this->blocked($actor->id, $to->id)) {
            return;
        }

        if ($dedupe) {
            $recent = Notification::query()
                ->where('user_id', $to->id)->where('send_by', $actor->id)->where('context', $context)
                ->where('created_at', '>=', now()->subDay())
                ->get(['metadata'])
                ->contains(fn ($n) => $this->sameTarget($this->decode($n->metadata), $meta));
            if ($recent) {
                return;
            }
        }

        Notification::create([
            'title' => $title,
            'message' => $message,
            'send_by' => $actor->id,
            'user_id' => $to->id,
            'type' => $type,
            'context' => $context,
            'unread' => true,
            'avatar' => $actor->avatar,
            'metadata' => $meta,
        ]);
    }

    private function sameTarget(array $a, array $b): bool
    {
        foreach (['vibe_id', 'reel_id', 'follower_id'] as $key) {
            if (isset($b[$key])) {
                return (int) ($a[$key] ?? 0) === (int) $b[$key];
            }
        }

        return false;
    }

    private function decode(mixed $metadata): array
    {
        if (is_array($metadata)) {
            return $metadata;
        }
        $decoded = is_string($metadata) ? json_decode($metadata, true) : null;
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }

        return is_array($decoded) ? $decoded : [];
    }

    private function meta(Vibe|UserReel $target): array
    {
        if ($target instanceof UserReel) {
            return ['reel_id' => $target->id, 'thumbnail' => $target->thumbnail_path ?: ($target->type === 'image' ? $target->file_path : null)];
        }

        $first = $target->media()->first();
        $thumb = null;
        if ($first) {
            $path = $first->thumbnail_path ?: ($first->media_type->value === 'image' ? $first->path : null);
            $thumb = $path ? ($first->disk === 'public' ? asset('storage/'.$path) : Storage::disk($first->disk)->url($path)) : null;
        }

        return ['vibe_id' => $target->id, 'thumbnail' => $thumb];
    }

    private function ownerOf(Vibe|UserReel $target): ?User
    {
        return User::find($target instanceof UserReel ? $target->user_id : $target->created_by);
    }

    private function kind(Vibe|UserReel $target): string
    {
        return $target instanceof UserReel ? 'reel' : 'vibe';
    }

    private function noun(Vibe|UserReel $target): string
    {
        return $target instanceof UserReel ? 'reel' : 'post';
    }

    private function blocked(int $a, int $b): bool
    {
        return BlockedUser::query()->where('is_blocked', true)->where(fn ($q) => $q
            ->where(fn ($x) => $x->where('user_id', $a)->where('blocked_user_id', $b))
            ->orWhere(fn ($y) => $y->where('user_id', $b)->where('blocked_user_id', $a)))->exists();
    }
}
