<?php

namespace App\Policies;

use App\Models\TyreAssignment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TyreAssignmentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TyreAssignment  $tyreAssignment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, TyreAssignment $tyreAssignment)
    {
        //
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TyreAssignment  $tyreAssignment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, TyreAssignment $tyreAssignment)
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TyreAssignment  $tyreAssignment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, TyreAssignment $tyreAssignment)
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TyreAssignment  $tyreAssignment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, TyreAssignment $tyreAssignment)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\TyreAssignment  $tyreAssignment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, TyreAssignment $tyreAssignment)
    {
        //
    }
}
