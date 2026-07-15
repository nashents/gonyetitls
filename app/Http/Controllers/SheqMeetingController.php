<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SheqMeeting;

class SheqMeetingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('sheq_meetings.index');
    }
}
