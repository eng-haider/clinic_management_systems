<?php

namespace App\Policies;

use App\Models\Cases;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CasesPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any cases.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the cases.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Cases  $cases
     * @return mixed
     */
    public function view(User $user, Cases $cases)
    {
        //
    }

    /**
     * Determine whether the user can create cases.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the cases.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Cases  $cases
     * @return mixed
     */
    public function update(User $user, Cases $cases)
    {
        //
    }

    /**
     * Determine whether the user can delete the cases.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Cases  $cases
     * @return mixed
     */
    public function delete(User $user, Cases $cases)
    {
        //
    }

    /**
     * Determine whether the user can restore the cases.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Cases  $cases
     * @return mixed
     */
    public function restore(User $user, Cases $cases)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the cases.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Cases  $cases
     * @return mixed
     */
    public function forceDelete(User $user, Cases $cases)
    {
        //
    }
}
