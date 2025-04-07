<?php

namespace App\Http\Controllers;

use App\Models\ReminderItem;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreReminderItemRequest;
use App\Http\Requests\UpdateReminderItemRequest;

class ReminderItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('reminder_items.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreReminderItemRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreReminderItemRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ReminderItem  $reminderItem
     * @return \Illuminate\Http\Response
     */
    public function show(ReminderItem $reminderItem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ReminderItem  $reminderItem
     * @return \Illuminate\Http\Response
     */
    public function edit(ReminderItem $reminderItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateReminderItemRequest  $request
     * @param  \App\Models\ReminderItem  $reminderItem
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateReminderItemRequest $request, ReminderItem $reminderItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ReminderItem  $reminderItem
     * @return \Illuminate\Http\Response
     */
    public function destroy(ReminderItem $reminderItem)
    {
        $reminderItem->delete();
        Session::flash('success','Reminder Item Deleted Successfully');
        return redirect()->back();
    }
}
