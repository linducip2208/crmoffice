<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AnnouncementPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view.announcement');
    }

    public function view(User $user, Announcement $announcement): bool
    {
        return $user->can('view.announcement');
    }

    public function create(User $user): bool
    {
        return $user->can('manage.announcement');
    }

    public function update(User $user, Announcement $announcement): bool
    {
        return $user->can('manage.announcement');
    }

    public function delete(User $user, Announcement $announcement): bool
    {
        return $user->can('manage.announcement');
    }

    public function restore(User $user, Announcement $announcement): bool
    {
        return $user->can('manage.announcement');
    }

    public function forceDelete(User $user, Announcement $announcement): bool
    {
        return $user->can('manage.announcement');
    }
}
