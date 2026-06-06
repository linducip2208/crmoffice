<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any_project');
    }

    public function view(User $user, Project $project): bool
    {
        return $user->can('view_project');
    }

    public function create(User $user): bool
    {
        return $user->can('create_project');
    }

    public function update(User $user, Project $project): bool
    {
        return $user->can('update_project');
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->can('delete_project');
    }

    public function restore(User $user, Project $project): bool
    {
        return $user->can('restore_project');
    }

    public function forceDelete(User $user, Project $project): bool
    {
        return $user->can('force_delete_project');
    }
}
