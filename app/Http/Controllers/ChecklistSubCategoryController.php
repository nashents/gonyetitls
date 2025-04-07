<?php

namespace App\Http\Controllers;

use App\Models\ChecklistSubCategory;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreChecklistSubCategoryRequest;
use App\Http\Requests\UpdateChecklistSubCategoryRequest;

class ChecklistSubCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('checklist_sub_categories.index');
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
     * @param  \App\Http\Requests\StoreChecklistSubCategoryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreChecklistSubCategoryRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ChecklistSubCategory  $checklistSubCategory
     * @return \Illuminate\Http\Response
     */
    public function show(ChecklistSubCategory $checklistSubCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ChecklistSubCategory  $checklistSubCategory
     * @return \Illuminate\Http\Response
     */
    public function edit(ChecklistSubCategory $checklistSubCategory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateChecklistSubCategoryRequest  $request
     * @param  \App\Models\ChecklistSubCategory  $checklistSubCategory
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateChecklistSubCategoryRequest $request, ChecklistSubCategory $checklistSubCategory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ChecklistSubCategory  $checklistSubCategory
     * @return \Illuminate\Http\Response
     */
    public function destroy(ChecklistSubCategory $checklistSubCategory)
    {
        $checklistSubCategory->delete();
        Session::flash('success','Inspection Group Deleted Successfully!!');
        return redirect()->back();
    }
}
