<?php

namespace App\Policies;

use App\Models\permissions;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class permissionsPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any permissions.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the permissions.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\permissions  $permissions
     * @return mixed
     */
    public function view(User $user, permissions $permissions)
    {
        //
    }

    /**
     * Determine whether the user can create permissions.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the permissions.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\permissions  $permissions
     * @return mixed
     */
    public function update(User $user, permissions $permissions)
    {
        //
    }

    /**
     * Determine whether the user can delete the permissions.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\permissions  $permissions
     * @return mixed
     */
    public function delete(User $user, permissions $permissions)
    {
        //
    }

    /**
     * Determine whether the user can restore the permissions.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\permissions  $permissions
     * @return mixed
     */
    public function restore(User $user, permissions $permissions)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the permissions.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\permissions  $permissions
     * @return mixed
     */
    public function forceDelete(User $user, permissions $permissions)
    {
        //
    }
}
