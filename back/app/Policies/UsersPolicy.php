<?php

namespace App\Policies;

use App\Models\Users;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UsersPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any users.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the users.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Users  $users
     * @return mixed
     */
    public function view(User $user, Users $users)
    {
        //
    }

    /**
     * Determine whether the user can create users.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the users.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Users  $users
     * @return mixed
     */
    public function update(User $user, Users $users)
    {
        //
    }

    /**
     * Determine whether the user can delete the users.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Users  $users
     * @return mixed
     */
    public function delete(User $user, Users $users)
    {
        //
    }

    /**
     * Determine whether the user can restore the users.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Users  $users
     * @return mixed
     */
    public function restore(User $user, Users $users)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the users.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Users  $users
     * @return mixed
     */
    public function forceDelete(User $user, Users $users)
    {
        //
    }
}
