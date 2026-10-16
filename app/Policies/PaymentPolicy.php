<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaymentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any.payment');
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->can('view.payment');
    }

    public function create(User $user): bool
    {
        return $user->can('create.payment');
    }

    public function update(User $user, Payment $payment): bool
    {
        return $user->can('create.payment');
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $user->can('delete.payment');
    }

    public function restore(User $user, Payment $payment): bool
    {
        return $user->can('delete.payment');
    }

    public function forceDelete(User $user, Payment $payment): bool
    {
        return $user->can('delete.payment');
    }
}
