<?php

namespace App\Http\Controllers;

class TripEditAuthorizerController extends Controller
{
    /**
     * Display the Trip Edit Authorizers admin CRUD (Super Admin only).
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('trip-edit-authorizers.index');
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
