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
        return $user->can('view_any.goal');
    }

    public function view(User $user, Goal $goal): bool
    {
        return $user->can('view.goal');
    }

    public function create(User $user): bool
    {
        return $user->can('create.goal');
    }

    public function update(User $user, Goal $goal): bool
    {
        return $user->can('update.goal');
    }

    public function delete(User $user, Goal $goal): bool
    {
        return $user->can('delete.goal');
    }

    public function restore(User $user, Goal $goal): bool
    {
        return $user->can('delete.goal');
    }

    public function forceDelete(User $user, Goal $goal): bool
    {
        return $user->can('delete.goal');
    }
}
