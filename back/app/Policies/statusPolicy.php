<?php

namespace App\Policies;

use App\Models\status;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class statusPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any status.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the status.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\status  $status
     * @return mixed
     */
    public function view(User $user, status $status)
    {
        //
    }

    /**
     * Determine whether the user can create status.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the status.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\status  $status
     * @return mixed
     */
    public function update(User $user, status $status)
    {
        //
    }

    /**
     * Determine whether the user can delete the status.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\status  $status
     * @return mixed
     */
    public function delete(User $user, status $status)
    {
        //
    }

    /**
     * Determine whether the user can restore the status.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\status  $status
     * @return mixed
     */
    public function restore(User $user, status $status)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the status.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\status  $status
     * @return mixed
     */
    public function forceDelete(User $user, status $status)
    {
        //
    }
}
