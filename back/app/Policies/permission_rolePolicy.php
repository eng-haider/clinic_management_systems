<?php

namespace App\Policies;

use App\Models\permission_role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class permission_rolePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any permission_role.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the permission_role.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\permission_role  $permission_role
     * @return mixed
     */
    public function view(User $user, permission_role $permission_role)
    {
        //
    }

    /**
     * Determine whether the user can create permission_role.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the permission_role.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\permission_role  $permission_role
     * @return mixed
     */
    public function update(User $user, permission_role $permission_role)
    {
        //
    }

    /**
     * Determine whether the user can delete the permission_role.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\permission_role  $permission_role
     * @return mixed
     */
    public function delete(User $user, permission_role $permission_role)
    {
        //
    }

    /**
     * Determine whether the user can restore the permission_role.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\permission_role  $permission_role
     * @return mixed
     */
    public function restore(User $user, permission_role $permission_role)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the permission_role.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\permission_role  $permission_role
     * @return mixed
     */
    public function forceDelete(User $user, permission_role $permission_role)
    {
        //
    }
}
