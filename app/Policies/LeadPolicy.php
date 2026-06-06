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
        return $user->can('view_any_lead');
    }

    public function view(User $user, Lead $lead): bool
    {
        return $user->can('view_lead');
    }

    public function create(User $user): bool
    {
        return $user->can('create_lead');
    }

    public function update(User $user, Lead $lead): bool
    {
        return $user->can('update_lead');
    }

    public function delete(User $user, Lead $lead): bool
    {
        return $user->can('delete_lead');
    }

    public function restore(User $user, Lead $lead): bool
    {
        return $user->can('restore_lead');
    }

    public function forceDelete(User $user, Lead $lead): bool
    {
        return $user->can('force_delete_lead');
    }
}
