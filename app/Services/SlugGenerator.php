<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SlugGenerator
{
    /**
     * Turn a title into a unique slug for the given table/column,
     * appending -2, -3, etc. on collision.
     *
     * $ignoreId excludes the row's own id when re-checking uniqueness
     * during a backfill/update — otherwise an event would collide with
     * itself once it already has the slug being generated.
     */
    public function unique(string $title, string $table, string $column = 'slug', ?int $ignoreId = null): string
    {
        $base = Str::slug($title);
        $base = $base !== '' ? $base : 'event';

        $slug = $base;
        $suffix = 2;

        while ($this->exists($table, $column, $slug, $ignoreId)) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    private function exists(string $table, string $column, string $slug, ?int $ignoreId): bool
    {
        return DB::table($table)
            ->where($column, $slug)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists();
    }
}
