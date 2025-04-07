<?php

namespace App\Http\Controllers;

use App\Models\ChecklistCategory;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreChecklistCategoryRequest;
use App\Http\Requests\UpdateChecklistCategoryRequest;

class ChecklistCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('checklist_categories.index');
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
     * @param  \App\Http\Requests\StoreChecklistCategoryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreChecklistCategoryRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ChecklistCategory  $checklistCategory
     * @return \Illuminate\Http\Response
     */
    public function show(ChecklistCategory $checklistCategory)
    {
        return view('checklist_categories.show')->with('checklist_category', $checklistCategory);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ChecklistCategory  $checklistCategory
     * @return \Illuminate\Http\Response
     */
    public function edit(ChecklistCategory $checklistCategory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateChecklistCategoryRequest  $request
     * @param  \App\Models\ChecklistCategory  $checklistCategory
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateChecklistCategoryRequest $request, ChecklistCategory $checklistCategory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ChecklistCategory  $checklistCategory
     * @return \Illuminate\Http\Response
     */
    public function destroy(ChecklistCategory $checklistCategory)
    {
        $checklistCategory->delete();
        Session::flash('success','Checklist Deleted Successfully!!');
        return redirect()->back();
    }
}
