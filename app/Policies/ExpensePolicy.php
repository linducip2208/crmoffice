<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ExpensePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any.expense');
    }

    public function view(User $user, Expense $expense): bool
    {
        return $user->can('view.expense');
    }

    public function create(User $user): bool
    {
        return $user->can('create.expense');
    }

    public function update(User $user, Expense $expense): bool
    {
        return $user->can('update.expense');
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $user->can('delete.expense');
    }

    public function restore(User $user, Expense $expense): bool
    {
        return $user->can('delete.expense');
    }

    public function forceDelete(User $user, Expense $expense): bool
    {
        return $user->can('delete.expense');
    }
}
