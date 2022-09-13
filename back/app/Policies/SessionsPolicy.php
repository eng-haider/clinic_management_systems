<?php

namespace App\Policies;

use App\Models\Sessions;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SessionsPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any sessions.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the sessions.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Sessions  $sessions
     * @return mixed
     */
    public function view(User $user, Sessions $sessions)
    {
        //
    }

    /**
     * Determine whether the user can create sessions.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the sessions.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Sessions  $sessions
     * @return mixed
     */
    public function update(User $user, Sessions $sessions)
    {
        //
    }

    /**
     * Determine whether the user can delete the sessions.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Sessions  $sessions
     * @return mixed
     */
    public function delete(User $user, Sessions $sessions)
    {
        //
    }

    /**
     * Determine whether the user can restore the sessions.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Sessions  $sessions
     * @return mixed
     */
    public function restore(User $user, Sessions $sessions)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the sessions.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Sessions  $sessions
     * @return mixed
     */
    public function forceDelete(User $user, Sessions $sessions)
    {
        //
    }
}
