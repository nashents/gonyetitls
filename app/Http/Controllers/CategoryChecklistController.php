<?php

namespace App\Http\Controllers;

use App\Models\CategoryChecklist;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreCategoryChecklistRequest;
use App\Http\Requests\UpdateCategoryChecklistRequest;

class CategoryChecklistController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
     * @param  \App\Http\Requests\StoreCategoryChecklistRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCategoryChecklistRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CategoryChecklist  $categoryChecklist
     * @return \Illuminate\Http\Response
     */
    public function show(CategoryChecklist $categoryChecklist)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CategoryChecklist  $categoryChecklist
     * @return \Illuminate\Http\Response
     */
    public function edit(CategoryChecklist $categoryChecklist)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCategoryChecklistRequest  $request
     * @param  \App\Models\CategoryChecklist  $categoryChecklist
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCategoryChecklistRequest $request, CategoryChecklist $categoryChecklist)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CategoryChecklist  $categoryChecklist
     * @return \Illuminate\Http\Response
     */
    public function destroy(CategoryChecklist $categoryChecklist)
    {
        $categoryChecklist->delete();
        Session::flash('success','Category Checklist Item Deleted Successfully');
        return redirect()->back();
    }
}
