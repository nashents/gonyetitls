<?php

namespace App\Http\Controllers;

use App\Models\ChecklistItem;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreChecklistItemRequest;
use App\Http\Requests\UpdateChecklistItemRequest;

class ChecklistItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('checklist_items.index');
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
     * @param  \App\Http\Requests\StoreChecklistItemRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreChecklistItemRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ChecklistItem  $checklistItem
     * @return \Illuminate\Http\Response
     */
    public function show(ChecklistItem $checklistItem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ChecklistItem  $checklistItem
     * @return \Illuminate\Http\Response
     */
    public function edit(ChecklistItem $checklistItem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateChecklistItemRequest  $request
     * @param  \App\Models\ChecklistItem  $checklistItem
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateChecklistItemRequest $request, ChecklistItem $checklistItem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ChecklistItem  $checklistItem
     * @return \Illuminate\Http\Response
     */
    public function destroy(ChecklistItem $checklistItem)
    {
        $checklistItem->delete();
        Session::flash('success','Inspection Item Deleted Successfully!!');
        return redirect()->back();
    }
}
