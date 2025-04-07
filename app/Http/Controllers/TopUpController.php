<?php

namespace App\Http\Controllers;

use App\Models\TopUp;
use App\Models\Container;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class TopUpController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('top_ups.index');
    }

    public function pending()
    {
        return view('top_ups.pending');
    }
    public function approved()
    {
        return view('top_ups.approved');
    }
    public function rejected()
    {
        return view('top_ups.rejected');
    }

    public function manage($id)
    {
        $container = Container::find($id);
        return view('top_ups.manage')->with('container', $container);
    }

    public function fuel($id)
    {
        $fuel_order = Fuel::find($id);
        return view('fuels.top_ups.index')->with('fuel_order', $fuel_order);
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
     * @param  \App\Models\TopUp  $topUp
     * @return \Illuminate\Http\Response
     */
    public function show(TopUp $topUp)
    {
        return view('top_ups.show')->with('top_up',$topUp);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TopUp  $topUp
     * @return \Illuminate\Http\Response
     */
    public function edit(TopUp $topUp)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TopUp  $topUp
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TopUp $topUp)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TopUp  $topUp
     * @return \Illuminate\Http\Response
     */
    public function destroy(TopUp $topUp)
    {
        $topUp->delete();
        Session::flash('success','Top Up record deleted successfully');
        return redirect()->back();
    }
}
