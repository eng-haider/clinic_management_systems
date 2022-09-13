<?php

namespace App\Policies;

use App\Models\doctors;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class doctorsPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any doctors.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the doctors.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\doctors  $doctors
     * @return mixed
     */
    public function view(User $user, doctors $doctors)
    {
        //
    }

    /**
     * Determine whether the user can create doctors.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the doctors.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\doctors  $doctors
     * @return mixed
     */
    public function update(User $user, doctors $doctors)
    {
        //
    }

    /**
     * Determine whether the user can delete the doctors.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\doctors  $doctors
     * @return mixed
     */
    public function delete(User $user, doctors $doctors)
    {
        //
    }

    /**
     * Determine whether the user can restore the doctors.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\doctors  $doctors
     * @return mixed
     */
    public function restore(User $user, doctors $doctors)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the doctors.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\doctors  $doctors
     * @return mixed
     */
    public function forceDelete(User $user, doctors $doctors)
    {
        //
    }
}
