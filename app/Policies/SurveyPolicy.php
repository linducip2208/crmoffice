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
        return $user->can('view_any_survey');
    }

    public function view(User $user, Survey $survey): bool
    {
        return $user->can('view_survey');
    }

    public function create(User $user): bool
    {
        return $user->can('create_survey');
    }

    public function update(User $user, Survey $survey): bool
    {
        return $user->can('update_survey');
    }

    public function delete(User $user, Survey $survey): bool
    {
        return $user->can('delete_survey');
    }

    public function restore(User $user, Survey $survey): bool
    {
        return $user->can('restore_survey');
    }

    public function forceDelete(User $user, Survey $survey): bool
    {
        return $user->can('force_delete_survey');
    }
}
