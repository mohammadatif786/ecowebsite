<?php

namespace App\Policies;

use App\Models\News;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NewsPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->hasRole('admin')) {
            return true;
        }
    }

    public function viewAny(User $user)
    {
        return $user->can('view news');
    }

    public function view(User $user, News $news)
    {
        return $user->can('view news');
    }

    public function create(User $user)
    {
        return $user->can('create news');
    }

    public function edit(User $user, News $news)
    {
        return $user->can('edit news');
    }

    public function update(User $user, News $news)
    {
        return $user->can('edit news') &&
            ($user->id === $news->user_id);
    }

    public function delete(User $user, News $news)
    {
        return $user->can('delete news');
    }

    public function restore(User $user, News $news)
    {
        return $user->can('restore news');
    }

    public function forceDelete(User $user, News $news)
    {
        return $user->can('force delete news');
    }

    public function toggleActive(User $user, News $news)
    {
        return $user->can('publish news');
    }
}
