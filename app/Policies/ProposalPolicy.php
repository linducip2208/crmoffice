<?php

namespace App\Policies;

use App\Models\Proposal;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProposalPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any.proposal');
    }

    public function view(User $user, Proposal $proposal): bool
    {
        return $user->can('view.proposal');
    }

    public function create(User $user): bool
    {
        return $user->can('create.proposal');
    }

    public function update(User $user, Proposal $proposal): bool
    {
        return $user->can('update.proposal');
    }

    public function delete(User $user, Proposal $proposal): bool
    {
        return $user->can('delete.proposal');
    }

    public function restore(User $user, Proposal $proposal): bool
    {
        return $user->can('delete.proposal');
    }

    public function forceDelete(User $user, Proposal $proposal): bool
    {
        return $user->can('delete.proposal');
    }
}
