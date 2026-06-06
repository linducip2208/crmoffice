<?php

namespace App\Policies;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class GoalPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_goal');
    }

    public function view(User $user, Goal $goal): bool
    {
        return $user->can('view_goal');
    }

    public function create(User $user): bool
    {
        return $user->can('create_goal');
    }

    public function update(User $user, Goal $goal): bool
    {
        return $user->can('update_goal');
    }

    public function delete(User $user, Goal $goal): bool
    {
        return $user->can('delete_goal');
    }

    public function restore(User $user, Goal $goal): bool
    {
        return $user->can('restore_goal');
    }

    public function forceDelete(User $user, Goal $goal): bool
    {
        return $user->can('force_delete_goal');
    }
}
