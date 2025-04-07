<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('bookings.index');
    }
    public function deleted()
    {
        return view('bookings.deleted');
    }
    public function pending()
    {
        return view('bookings.pending');
    }
    public function approved()
    {
        return view('bookings.approved');
    }
    public function rejected()
    {
        return view('bookings.rejected');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('bookings.create');
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
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function show(Booking $booking)
    {
        return view('bookings.show')->with('booking',$booking);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function edit(Booking $booking)
    {
        return view('bookings.edit')->with('booking',$booking);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Booking $booking)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function destroy(Booking $booking)
    {

        $horse = $booking->horse;
        $trailer = $booking->trailer;
        $vehicle = $booking->vehicle;
        
        if (isset($vehicle)) {
            $vehicle->service = 0;
            $vehicle->update();
        }
        if (isset($horse)) {
            $horse->service = 0;
            $horse->update();
        }
        if (isset($trailer)) {
            $trailer->service = 0;
            $trailer->update();
        }

        

        $ticket = $booking->ticket;
  
        if (isset($ticket)) {
            $ticket_inventories = $ticket->ticket_inventories;
            if (isset($ticket_inventories) && $ticket_inventories->count()>0) {
               foreach ($ticket_inventories as $inventory) {
                $inventory->delete();
               }
            }
            $inventory_requisitions = $ticket->inventory_requisitions;
            if (isset($inventory_requisitions) && $inventory_requisitions->count()>0) {
                foreach ($inventory_requisitions as $requisition) {
                    $requisition->delete();
                }
            }
            $inventory_dispatches = $ticket->inventory_dispatches;
            if (isset($inventory_dispatches) && $inventory_dispatches->count()>0) {
                foreach ($inventory_dispatches as $dispatch) {
                    $dispatch->delete();
                }
            }
           
            $ticket->delete();
        }
        $inspection = $booking->inspection;
        if (isset($inspection)) {
            $inspection->delete();
        }
        $booking->delete();
        Session::flash('success','Booking Deleted Successfully!!');
        return redirect()->back();
    }
}
