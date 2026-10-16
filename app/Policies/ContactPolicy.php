<?php

namespace App\Policies;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContactPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any.contact');
    }

    public function view(User $user, Contact $contact): bool
    {
        return $user->can('view.contact');
    }

    public function create(User $user): bool
    {
        return $user->can('create.contact');
    }

    public function update(User $user, Contact $contact): bool
    {
        return $user->can('update.contact');
    }

    public function delete(User $user, Contact $contact): bool
    {
        return $user->can('delete.contact');
    }

    public function restore(User $user, Contact $contact): bool
    {
        return $user->can('delete.contact');
    }

    public function forceDelete(User $user, Contact $contact): bool
    {
        return $user->can('delete.contact');
    }
}
