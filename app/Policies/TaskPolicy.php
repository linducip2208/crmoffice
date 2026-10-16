<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any.task');
    }

    public function view(User $user, Task $task): bool
    {
        return $user->can('view.task');
    }

    public function create(User $user): bool
    {
        return $user->can('create.task');
    }

    public function update(User $user, Task $task): bool
    {
        return $user->can('update.task');
    }

    public function delete(User $user, Task $task): bool
    {
        return $user->can('delete.task');
    }

    public function restore(User $user, Task $task): bool
    {
        return $user->can('delete.task');
    }

    public function forceDelete(User $user, Task $task): bool
    {
        return $user->can('delete.task');
    }
}
