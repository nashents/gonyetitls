<?php

namespace App\Http\Controllers;

class EditAuthorizerController extends Controller
{
    /**
     * The Edit Authorizers admin CRUD lives on the Notifications page,
     * alongside notification-recipient management, so both "who to notify"
     * and "who can authorize edits" are configured in one place.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return redirect()->route('notifications.index');
    }

    /**
     * Display the pending Edit Authorization requests queue, across every
     * module (trips, bills, ...) the current user is an authorizer for.
     *
     * @return \Illuminate\Http\Response
     */
    public function pending()
    {
        return view('edit-authorizers.pending');
    }
}
