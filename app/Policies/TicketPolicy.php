<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TicketPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any.ticket');
    }

    public function view(User $user, Ticket $ticket): bool
    {
        return $user->can('view.ticket');
    }

    public function create(User $user): bool
    {
        return $user->can('create.ticket');
    }

    public function update(User $user, Ticket $ticket): bool
    {
        return $user->can('update.ticket');
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->can('delete.ticket');
    }

    public function restore(User $user, Ticket $ticket): bool
    {
        return $user->can('delete.ticket');
    }

    public function forceDelete(User $user, Ticket $ticket): bool
    {
        return $user->can('delete.ticket');
    }
}
