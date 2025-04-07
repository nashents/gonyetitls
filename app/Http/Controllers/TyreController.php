<?php

namespace App\Http\Controllers;

use App\Models\Tyre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class TyreController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('tyres.index');
    }
    public function orders()
    {
        return view('tyres.orders');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('tyres.create');
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
     * @param  \App\Models\Tyre  $tyre
     * @return \Illuminate\Http\Response
     */
    public function show(Tyre $tyre)
    {
        return view('tyres.show')->with('tyre',$tyre);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Tyre  $tyre
     * @return \Illuminate\Http\Response
     */
    public function edit(Tyre $tyre)
    {
        return view('tyres.edit')->with('tyre',$tyre);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Tyre  $tyre
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Tyre $tyre)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Tyre  $tyre
     * @return \Illuminate\Http\Response
     */
    public function destroy(Tyre $tyre)
    {
        $assignment = $tyre->tyre_assignment;
        if (isset($assignment)) {
            $assignment->delete();
        }
        $tyre->delete();
        Session::flash('success','Tyre & Assignments Deleted Successfully!!');
        return redirect()->back();
    }
}
