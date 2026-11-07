<?php

namespace App\Models;

use App\Domain\Vibes\Enums\VibeStatus;
use App\Domain\Vibes\Enums\VibeVisibility;
use App\Models\Vote;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Vibe extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['created_by', 'publisher_type', 'publisher_id', 'caption', 'title', 'category', 'campus', 'university_id', 'price', 'is_uvibe', 'show_on_vibes', 'text_bg', 'text_font', 'text_style', 'posted_role', 'as_university', 'location_name', 'location_place_id', 'latitude', 'longitude', 'allow_coin_gifts', 'visibility', 'status', 'published_at', 'archived_at', 'reposted_from_id', 'reposted_from_name', 'edited_at'];

    protected function casts(): array
    {
        return [
            'allow_coin_gifts' => 'boolean',
            'is_uvibe' => 'boolean',
            'show_on_vibes' => 'boolean',
            'as_university' => 'boolean',
            'text_style' => 'array',
            'price' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'visibility' => VibeVisibility::class,
            'status' => VibeStatus::class,
            'published_at' => 'datetime',
            'archived_at' => 'datetime',
            'edited_at' => 'datetime',
        ];
    }

    public function university()
    {
        return $this->belongsTo(University::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function publisher()
    {
        return $this->morphTo();
    }

    public function media()
    {
        return $this->hasMany(VibeMedia::class)->orderBy('sort_order');
    }

    public function attachments()
    {
        return $this->hasMany(VibeAttachment::class);
    }

    public function products()
    {
        return $this->morphedByMany(Product::class, 'attachable', 'vibe_attachments');
    }

    public function events()
    {
        return $this->morphedByMany(LinkUpEvent::class, 'attachable', 'vibe_attachments');
    }

    public function likes()
    {
        return $this->morphMany(Vote::class, 'votable')->where('type', 'like');
    }

    public function authUserLike()
    {
        return $this->morphOne(Vote::class, 'votable')->where('user_id', auth()->id())->where('type', 'like');
    }

    public function comments()
    {
        return $this->hasMany(VibeComment::class);
    }

    /**
     * Only posts the viewer is allowed to see:
     * public posts, the viewer's own posts, friends-only posts from friends,
     * followers-only posts from creators the viewer follows — and nothing from
     * someone who blocked the viewer or whom the viewer blocked.
     */
    public function scopeVisibleTo(Builder $query, ?User $viewer): Builder
    {
        if (! $viewer) {
            return $query->where('visibility', VibeVisibility::Public->value);
        }

        $viewerId = $viewer->getKey();

        return $query
            ->where(function (Builder $q) use ($viewerId) {
                $q->where('created_by', $viewerId)
                    ->orWhere('visibility', VibeVisibility::Public->value)
                    ->orWhere(function (Builder $friends) use ($viewerId) {
                        // Friend requests go both ways, so check both directions.
                        $friends->where('visibility', VibeVisibility::Friends->value)
                            ->where(function (Builder $either) use ($viewerId) {
                                $either->whereIn('created_by', DB::table('friend_requests')->where('status', 1)->where('user_id', $viewerId)->select('receiver_id'))
                                    ->orWhereIn('created_by', DB::table('friend_requests')->where('status', 1)->where('receiver_id', $viewerId)->select('user_id'));
                            });
                    })
                    ->orWhere(function (Builder $followers) use ($viewerId) {
                        $followers->where('visibility', VibeVisibility::Followers->value)
                            ->whereIn('created_by', CreatorFollower::query()->where('follower_id', $viewerId)->select('creator_id'));
                    });
            })
            ->whereNotIn('created_by', BlockedUser::query()
                ->where('user_id', $viewerId)->where('is_blocked', true)->select('blocked_user_id'))
            ->whereNotIn('created_by', BlockedUser::query()
                ->where('blocked_user_id', $viewerId)->where('is_blocked', true)->select('user_id'));
    }

    /** Replace this post's hashtags with the ones in `$caption` (each tag once). */
    public function syncHashtags(?string $caption): void
    {
        DB::table('vibe_hashtags')->where('vibe_id', $this->getKey())->delete();

        if (! $caption || ! preg_match_all('/#(\w+)/u', $caption, $m)) {
            return;
        }

        $now = now();
        $rows = collect($m[1])->map(fn ($t) => mb_strtolower(mb_substr($t, 0, 64)))->unique()->take(30)
            ->map(fn ($tag) => ['vibe_id' => $this->getKey(), 'tag' => $tag, 'created_at' => $now, 'updated_at' => $now])
            ->values()->all();

        if ($rows !== []) {
            DB::table('vibe_hashtags')->insert($rows);
        }
    }

    /** Same rule as scopeVisibleTo, for one post that is already loaded. */
    public function isVisibleTo(?User $viewer): bool
    {
        if ($viewer && (int) $this->created_by === (int) $viewer->getKey()) {
            return true;
        }

        return self::query()->whereKey($this->getKey())->visibleTo($viewer)->exists();
    }
}
