<?php

namespace App\Http\Controllers;

use App\Models\Fuel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class FuelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('fuels.index');
    }
    public function deleted()
    {
        return view('fuels.deleted');
    }
    public function rejected()
    {
        return view('fuels.rejected');
    }
    public function pending()
    {
        return view('fuels.pending');
    }
    public function approved()
    {
        return view('fuels.approved');
    }
    
    public function preview(Fuel $fuel){
        if (isset(Auth::user()->employee->company)) {
            $company = Auth::user()->employee->company;
        }elseif (isset(Auth::user()->company)) {
          $company =  Auth::user()->company;
        }
        $container = $fuel->container;
        return view('fuels.preview')->with([
            'fuel' => $fuel,
            'company' => $company,
            'container' => $container,
          ]);
    }
    public function print(Fuel $fuel){

        if (isset(Auth::user()->employee->company)) {
            $company = Auth::user()->employee->company;
        }elseif (isset(Auth::user()->company)) {
          $company =  Auth::user()->company;
        }
        $container = $fuel->container;
        return view('fuels.print')->with([
            'fuel' => $fuel,
            'company' => $company,
            'container' => $container,
          ]);
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
     * @param  \App\Models\Fuel  $fuel
     * @return \Illuminate\Http\Response
     */
    public function show(Fuel $fuel)
    {
        return view('fuels.show')->with('fuel',$fuel);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Fuel  $fuel
     * @return \Illuminate\Http\Response
     */
    public function edit(Fuel $fuel)
    {
        return view('fuels.edit')->with('fuel',$fuel);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Fuel  $fuel
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Fuel $fuel)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Fuel  $fuel
     * @return \Illuminate\Http\Response
     */
    public function destroy(Fuel $fuel)
    {
        $fuel->delete();
        Session::flash('success','Fuel Order Deleted Successfully!!');
        return redirect()->back();
    }
}
