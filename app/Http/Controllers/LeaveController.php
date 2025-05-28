<?php

namespace App\Http\Controllers;

use App\Models\Leave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LeaveController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('leaves.index');
    }
    public function reports(){
        return view('leaves.reports');
    }
    public function manage(){
        return view('leaves.manage');
    }
    public function myTeam(){
        return view('leaves.myteam');
    }

    public function pending()
    {
        return view('leaves.pending');
    }



    public function approved()
    {

        return view('leaves.approved');
    }

    public function rejected()
    {
        return view('leaves.rejected');
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
     * @param  \App\Models\Leave  $leave
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $leave = Leave::find($id);
        // $to = \Carbon\Carbon::parse($leave->to);
        // $from = \Carbon\Carbon::parse($leave->from);
        // $days = $to->diffInDays($from);
        // return $days;
        return view('leaves.show')->with('leave', $leave);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Leave  $leave
     * @return \Illuminate\Http\Response
     */
    public function edit(Leave $leave)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Leave  $leave
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Leave $leave)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Leave  $leave
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $leave =  Leave::find($id);
        $leave->delete();
        Session::flash('success','Leave Application successfully deleted');
        return redirect()->back();
    }
}
