<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DepartmentHead;
use Illuminate\Support\Facades\Session;

class DepartmentHeadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('department_heads.index');
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DepartmentHead  $departmentHead
     * @return \Illuminate\Http\Response
     */
    public function show(DepartmentHead $departmentHead)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DepartmentHead  $departmentHead
     * @return \Illuminate\Http\Response
     */
    public function edit(DepartmentHead $departmentHead)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DepartmentHead  $departmentHead
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DepartmentHead $departmentHead)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DepartmentHead  $departmentHead
     * @return \Illuminate\Http\Response
     */
    public function destroy(DepartmentHead $departmentHead)
    {
        $departmentHead->delete();
        Session::flash('success','Department Head Removed Successfully!!');
        return redirect()->back();
    }
}
