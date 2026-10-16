<?php

namespace App\Policies;

use App\Models\Survey;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SurveyPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view_any.survey');
    }

    public function view(User $user, Survey $survey): bool
    {
        return $user->can('view.survey');
    }

    public function create(User $user): bool
    {
        return $user->can('create.survey');
    }

    public function update(User $user, Survey $survey): bool
    {
        return $user->can('update.survey');
    }

    public function delete(User $user, Survey $survey): bool
    {
        return $user->can('delete.survey');
    }

    public function restore(User $user, Survey $survey): bool
    {
        return $user->can('delete.survey');
    }

    public function forceDelete(User $user, Survey $survey): bool
    {
        return $user->can('delete.survey');
    }
}
