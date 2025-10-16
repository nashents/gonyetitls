<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreShiftRequest;
use App\Http\Requests\UpdateShiftRequest;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('shifts.index');
    }
    public function reports()
    {
        return view('shifts.reports');
    }

    public function preview(Request $request)
    {
        // You can now access all passed parameters via $request
        $filters = $request->all();
       
        // Pass data to view, perform filtering, etc.
        return view('shifts.preview', compact('filters'));
    }

        public function pending()
    {
        return view('shifts.pending');
    }
    public function approved()
    {
        return view('shifts.approved');
    }
    public function rejected()
    {
        return view('shifts.rejected');
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
     * @param  \App\Http\Requests\StoreShiftRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreShiftRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Shift  $shift
     * @return \Illuminate\Http\Response
     */
    public function show(Shift $shift)
    {
         return view('shifts.show')->with('shift',$shift);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Shift  $shift
     * @return \Illuminate\Http\Response
     */
    public function edit(Shift $shift)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateShiftRequest  $request
     * @param  \App\Models\Shift  $shift
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateShiftRequest $request, Shift $shift)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Shift  $shift
     * @return \Illuminate\Http\Response
     */
    public function destroy(Shift $shift)
    {
        $shift->delete();
        $rehandlings = $shift->rehandlings;
        $trips = $shift->trips;
        if ($rehandlings) {
            foreach ($rehandlings as $rehandling) {
                $rehandling->delete();
            }
        }
        if ($trips) {
            foreach ($trips as $trip) {
                $delivery_note = $trip->delivery_note;
                if ($delivery_note) {
                    $delivery_note->delete();
                }
                $trip->delete();
            }
        }
        Session::flash('success','Shift Deleted Successfully');
        return redirect()->back();
    }
}
