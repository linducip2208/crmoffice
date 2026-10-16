<?php

namespace App\Policies;

use App\Models\Estimate;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EstimatePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any.estimate');
    }

    public function view(User $user, Estimate $estimate): bool
    {
        return $user->can('view.estimate');
    }

    public function create(User $user): bool
    {
        return $user->can('create.estimate');
    }

    public function update(User $user, Estimate $estimate): bool
    {
        return $user->can('update.estimate');
    }

    public function delete(User $user, Estimate $estimate): bool
    {
        return $user->can('delete.estimate');
    }

    public function restore(User $user, Estimate $estimate): bool
    {
        return $user->can('delete.estimate');
    }

    public function forceDelete(User $user, Estimate $estimate): bool
    {
        return $user->can('delete.estimate');
    }
}
