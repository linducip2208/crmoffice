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
        return $user->can('view_any_proposal');
    }

    public function view(User $user, Proposal $proposal): bool
    {
        return $user->can('view_proposal');
    }

    public function create(User $user): bool
    {
        return $user->can('create_proposal');
    }

    public function update(User $user, Proposal $proposal): bool
    {
        return $user->can('update_proposal');
    }

    public function delete(User $user, Proposal $proposal): bool
    {
        return $user->can('delete_proposal');
    }

    public function restore(User $user, Proposal $proposal): bool
    {
        return $user->can('restore_proposal');
    }

    public function forceDelete(User $user, Proposal $proposal): bool
    {
        return $user->can('force_delete_proposal');
    }
}
