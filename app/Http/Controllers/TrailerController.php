<?php

namespace App\Http\Controllers;

use App\Models\Trailer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class TrailerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('trailers.index');
    }
    public function archived()
    {
        return view('trailers.archived');
    }
    public function archive($id){
        $trailer = Trailer::find($id);
        $trailer->archive = 1;
        $trailer->service = 1;
        $trailer->status = 0;
        $trailer->update();
        Session::flash('success','Trailer Archived Successfully!!');
        return redirect(route('trailers.archived'));
    }
    public function age()
    {
        return view('trailers.age');
    }
    public function mileage()
    {
        return view('trailers.mileage');
    }
    public function manage()
    {
        return view('trailers.manage');
    }

    public function deactivate(Trailer $trailer){
        // $trailer = trailer::find($id);
        $trailer->status = 0 ;
        $trailer->update();
        Session::flash('success','Trailer Successfully Deactivated');
        return redirect(route('trailers.index'));
    }
    public function service(Trailer $trailer){

        $bookings = $trailer->bookings->where('status', 1);

        if (isset($bookings)) {
           foreach ($bookings as $booking) {
            $booking->status = 0;
            $booking->update();

            // $ticket = $booking->ticket;
            // if (isset($ticket)) {
            //     $ticket->closed_by_id = Auth::user()->id;
            //     $ticket->status = 0;
            //     $ticket->update();
            // }

           }
        }
    
        $trailer->service = 0 ;
        $trailer->update();

        Session::flash('success','Trailer Ticket Closed Successfully');
        return redirect(route('trailers.index'));
    }

    public function activate(Trailer $trailer){
        // $trailer = trailer::find($id);
        $trailer->status = 1 ;
        $trailer->update();
        Session::flash('success','Trailer Successfully Activated');
        return redirect(route('trailers.index'));
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
     * @param  \App\Models\Trailer  $trailer
     * @return \Illuminate\Http\Response
     */
    public function show(Trailer $trailer)
    {
        return view('trailers.show')->with([
            'trailer' => $trailer,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Trailer  $trailer
     * @return \Illuminate\Http\Response
     */
    public function edit(Trailer $trailer)
    {
        return view('trailers.edit')->with('trailer',$trailer);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Trailer  $trailer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Trailer $trailer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Trailer  $trailer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Trailer $trailer)
    {
        $trailer->delete();
        Session::flash('success','Trailer deleted successfully');
        return redirect()->back();
    }
}
