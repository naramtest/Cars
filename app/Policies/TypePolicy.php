<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Type;
use Illuminate\Auth\Access\HandlesAuthorization;

class TypePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_vehicle::type');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Type $type): bool
    {
        return $user->can('view_vehicle::type');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_vehicle::type');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Type $type): bool
    {
        return $user->can('update_vehicle::type');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Type $type): bool
    {
        return $user->can('delete_vehicle::type');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_vehicle::type');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, Type $type): bool
    {
        return $user->can('force_delete_vehicle::type');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_vehicle::type');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, Type $type): bool
    {
        return $user->can('restore_vehicle::type');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_vehicle::type');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, Type $type): bool
    {
        return $user->can('replicate_vehicle::type');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_vehicle::type');
    }
}
