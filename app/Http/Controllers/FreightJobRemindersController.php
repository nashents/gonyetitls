<?php

namespace App\Http\Controllers;

/** Freight > Freight Jobs > Job Reminders (Livewire Freight\JobReminders\Index). */
class FreightJobRemindersController extends Controller
{
    public function index()
    {
        return view('freight.job-reminders.index');
    }
}
