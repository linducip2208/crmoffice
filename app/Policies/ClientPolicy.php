<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClientPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any.client');
    }

    public function view(User $user, Client $client): bool
    {
        return $user->can('view.client');
    }

    public function create(User $user): bool
    {
        return $user->can('create.client');
    }

    public function update(User $user, Client $client): bool
    {
        return $user->can('update.client');
    }

    public function delete(User $user, Client $client): bool
    {
        return $user->can('delete.client');
    }

    public function restore(User $user, Client $client): bool
    {
        return $user->can('restore.client');
    }

    public function forceDelete(User $user, Client $client): bool
    {
        return $user->can('force_delete.client');
    }
}
