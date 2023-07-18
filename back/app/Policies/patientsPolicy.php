<?php

namespace App\Policies;

use App\Models\patients;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class patientsPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any patients.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the patients.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\patients  $patients
     * @return mixed
     */
    public function view(User $user, patients $patients)
    {
        //
    }

    /**
     * Determine whether the user can create patients.
     *
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the patients.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\patients  $patients
     * @return mixed
     */
    public function update(User $user, patients $patients)
    {
        //
    }

    /**
     * Determine whether the user can delete the patients.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\patients  $patients
     * @return mixed
     */
    public function delete(User $user, patients $patients)
    {
        //
    }

    /**
     * Determine whether the user can restore the patients.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\patients  $patients
     * @return mixed
     */
    public function restore(User $user, patients $patients)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the patients.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\patients  $patients
     * @return mixed
     */
    public function forceDelete(User $user, patients $patients)
    {
        //
    }
}
