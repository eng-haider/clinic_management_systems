<?php

namespace App\Policies;

use App\Models\roles;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class rolesPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any roles.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the roles.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\roles  $roles
     * @return mixed
     */
    public function view(User $user, roles $roles)
    {
        //
    }

    /**
     * Determine whether the user can create roles.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the roles.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\roles  $roles
     * @return mixed
     */
    public function update(User $user, roles $roles)
    {
        //
    }

    /**
     * Determine whether the user can delete the roles.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\roles  $roles
     * @return mixed
     */
    public function delete(User $user, roles $roles)
    {
        //
    }

    /**
     * Determine whether the user can restore the roles.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\roles  $roles
     * @return mixed
     */
    public function restore(User $user, roles $roles)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the roles.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\roles  $roles
     * @return mixed
     */
    public function forceDelete(User $user, roles $roles)
    {
        //
    }
}
