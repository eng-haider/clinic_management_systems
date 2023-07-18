<?php

namespace App\Policies;

use App\Models\SessionsCases;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SessionsCasesPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any sessionsCases.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the sessionsCases.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SessionsCases  $sessionsCases
     * @return mixed
     */
    public function view(User $user, SessionsCases $sessionsCases)
    {
        //
    }

    /**
     * Determine whether the user can create sessionsCases.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the sessionsCases.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SessionsCases  $sessionsCases
     * @return mixed
     */
    public function update(User $user, SessionsCases $sessionsCases)
    {
        //
    }

    /**
     * Determine whether the user can delete the sessionsCases.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SessionsCases  $sessionsCases
     * @return mixed
     */
    public function delete(User $user, SessionsCases $sessionsCases)
    {
        //
    }

    /**
     * Determine whether the user can restore the sessionsCases.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SessionsCases  $sessionsCases
     * @return mixed
     */
    public function restore(User $user, SessionsCases $sessionsCases)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the sessionsCases.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\SessionsCases  $sessionsCases
     * @return mixed
     */
    public function forceDelete(User $user, SessionsCases $sessionsCases)
    {
        //
    }
}
