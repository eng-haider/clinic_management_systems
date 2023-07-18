<?php

namespace App\Policies;

use App\Models\bills;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class billsPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any bills.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the bills.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\bills  $bills
     * @return mixed
     */
    public function view(User $user, bills $bills)
    {
        //
    }

    /**
     * Determine whether the user can create bills.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the bills.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\bills  $bills
     * @return mixed
     */
    public function update(User $user, bills $bills)
    {
        //
    }

    /**
     * Determine whether the user can delete the bills.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\bills  $bills
     * @return mixed
     */
    public function delete(User $user, bills $bills)
    {
        //
    }

    /**
     * Determine whether the user can restore the bills.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\bills  $bills
     * @return mixed
     */
    public function restore(User $user, bills $bills)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the bills.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\bills  $bills
     * @return mixed
     */
    public function forceDelete(User $user, bills $bills)
    {
        //
    }
}
