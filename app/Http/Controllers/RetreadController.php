<?php

namespace App\Http\Controllers;

use App\Models\Retread;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreRetreadRequest;
use App\Http\Requests\UpdateRetreadRequest;

class RetreadController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('retreads.index');
    }
    public function deleted()
    {
        return view('retreads.deleted');
    }
    public function rejected()
    {
        return view('retreads.rejected');
    }
    public function pending()
    {
        return view('retreads.pending');
    }
    public function approved()
    {
        return view('retreads.approved');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('retreads.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreRetreadRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRetreadRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Retread  $retread
     * @return \Illuminate\Http\Response
     */
    public function show(Retread $retread)
    {
        return view('retreads.show')->with('retread',$retread);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Retread  $retread
     * @return \Illuminate\Http\Response
     */
    public function edit(Retread $retread)
    {
        return view('retreads.edit')->with('retread',$retread);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateRetreadRequest  $request
     * @param  \App\Models\Retread  $retread
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRetreadRequest $request, Retread $retread)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Retread  $retread
     * @return \Illuminate\Http\Response
     */
    public function destroy(Retread $retread)
    {
        $retread_tyres = $retread->retread_tyres;
        if (isset($retread_tyres)) {
            foreach ($retread_tyres as $retread_tyre) {
                $retread_tyre->delete();
            }
        }
        $retread->delete();
        Session::flash('success','Retread Deleted Successfully!!');
        return redirect()->back();
    }
}
