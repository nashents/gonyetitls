<?php

namespace App\Http\Controllers;

use App\Models\TripDestination;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreTripDestinationRequest;
use App\Http\Requests\UpdateTripDestinationRequest;

class TripDestinationController extends Controller
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
     * @param  \App\Http\Requests\StoreTripDestinationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTripDestinationRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TripDestination  $tripDestination
     * @return \Illuminate\Http\Response
     */
    public function show(TripDestination $tripDestination)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TripDestination  $tripDestination
     * @return \Illuminate\Http\Response
     */
    public function edit(TripDestination $tripDestination)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTripDestinationRequest  $request
     * @param  \App\Models\TripDestination  $tripDestination
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTripDestinationRequest $request, TripDestination $tripDestination)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TripDestination  $tripDestination
     * @return \Illuminate\Http\Response
     */
    public function destroy(TripDestination $tripDestination)
    {
        $trip = $tripDestination->trip;
        $weight = $tripDestination->weight;
        $quantity = $tripDestination->quantity;
        $litreage = $tripDestination->litreage;
        $litreage_at_20 = $tripDestination->litreage_at_20;

        if (isset($trip)) {

            $delivery_note = $trip->delivery_note;

            if (isset($delivery_note)) {
                if (isset($weight)) {
                    $delivery_note->offloaded_weight =  $delivery_note->offloaded_weight - $weight;
                }
               if (isset($quantity)) {
                    $delivery_note->offloaded_quantity =  $delivery_note->offloaded_quantity - $quantity;
                }
               if (isset($litreage)) {
                    $delivery_note->offloaded_litreage =  $delivery_note->offloaded_litreage - $litreage;
                }
               if (isset($litreage_at_20)) {
                    $delivery_note->offloaded_litreage_at_20 =  $delivery_note->offloaded_litreage_at_20 - $litreage_at_20;
                }
             
            }
            # code...
        }
      

    

        $tripDestination->delete();

        Session::flash('success','Trip Destination Successfully Deleted');
        return redirect()->back();
    }
}
