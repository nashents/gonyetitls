<?php

namespace App\Http\Controllers;

class TripEditAuthorizerController extends Controller
{
    /**
     * The Trip Edit Authorizers admin CRUD now lives on the Notifications
     * page, alongside notification-recipient management, so both "who to
     * notify" and "who can authorize trip edits" are configured in one place.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return redirect()->route('notifications.index');
    }

    /**
     * Display the pending Trip Edit Authorization requests queue.
     *
     * @return \Illuminate\Http\Response
     */
    public function pending()
    {
        return view('trip-edit-authorizers.pending');
    }
}
