<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LeadPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any.lead');
    }

    public function view(User $user, Lead $lead): bool
    {
        return $user->can('view.lead');
    }

    public function create(User $user): bool
    {
        return $user->can('create.lead');
    }

    public function update(User $user, Lead $lead): bool
    {
        return $user->can('update.lead');
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $user->can('delete.lead');
    }

    public function restore(User $user, Lead $lead): bool
    {
        return $user->can('delete.lead');
    }

    public function forceDelete(User $user, Lead $lead): bool
    {
        return $user->can('delete.lead');
    }
}
