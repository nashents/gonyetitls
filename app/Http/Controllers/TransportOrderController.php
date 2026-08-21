<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\User;
use App\Models\Destination;
use Illuminate\Http\Request;
use App\Models\TransportOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class TransportOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('transport_orders.index');
    }

        public function pending()
    {
        return view('transport_orders.pending');
    }
    public function approved()
    {
        return view('transport_orders.approved');
    }
    public function rejected()
    {
        return view('transport_orders.rejected');
    }

    public function order()
    {
        return view('transport_orders.order');
    }
    public function preview($id){
      
        return view('transport_orders.preview')->with([
            'id' => $id
          ]);
    }
    public function print($id){
        $company = Auth::user()->employee->company;
        $trip = Trip::with([
        'customer:id,name,street_address,suburb,city,country,phonenumber,email',
        'customer.contacts',
        'consignee:id,name,street_address,suburb,city,country,phonenumber,email',
        'consignee.contacts',
        'driver.employee',
        'horse' => function ($q) {
            $q->select('id', 'registration_number', 'fleet_number', 'horse_make_id', 'horse_model_id')
            ->with([
                'horse_make:id,name',
                'horse_model:id,name',
            ]);
        },
        'transporter:id,name',
        'cmr_detail',
        ])->find($id);

        abort_if(!$trip, 404);

        $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
        $origin = Destination::find($trip->from);
        $destination = Destination::find($trip->to);
        $authorizer = User::find($trip->authorized_by_id);

        return view('transport_orders.print')->with([
            'trip' => $trip,
            'company' => $company,
            'origin' => $origin,
            'destination' => $destination,
            'authorizer' => $authorizer,
            'pattern' => $pattern,
          ]);
    }

    public function generatePDF($id){
        // Route param is a Trip id (matches preview()/print() above, and the
        // `transport_orders.order` view itself reads $trip throughout).
        $company = Auth::user()->employee->company;
        $trip = Trip::with([
        'customer:id,name,street_address,suburb,city,country,phonenumber,email',
        'customer.contacts',
        'consignee:id,name,street_address,suburb,city,country,phonenumber,email',
        'consignee.contacts',
        'driver.employee',
        'horse' => function ($q) {
            $q->select('id', 'registration_number', 'fleet_number', 'horse_make_id', 'horse_model_id')
            ->with([
                'horse_make:id,name',
                'horse_model:id,name',
            ]);
        },
        'transporter:id,name',
        'cmr_detail',
        ])->find($id);

        abort_if(!$trip, 404);

        $pattern = '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/';
        $origin = Destination::find($trip->from);
        $destination = Destination::find($trip->to);
        $authorizer = User::find($trip->authorized_by_id);
        $data = [
            'trip' => $trip,
            'company' => $company,
            'origin' => $origin,
            'destination' => $destination,
            'authorizer' => $authorizer,
            'pattern' => $pattern,
        ];
        $pdf = PDF::loadView('transport_orders.order', $data);

        return $pdf->download('transport_order.pdf');

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
     * @param  \App\Models\TransportOrder  $transportOrder
     * @return \Illuminate\Http\Response
     */
    public function show(TransportOrder $transportOrder)
    {
        return view('transport_orders.show',[
            'transport_order' => $transportOrder
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TransportOrder  $transportOrder
     * @return \Illuminate\Http\Response
     */
    public function edit(TransportOrder $transportOrder)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TransportOrder  $transportOrder
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TransportOrder $transportOrder)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TransportOrder  $transportOrder
     * @return \Illuminate\Http\Response
     */
    public function destroy(TransportOrder $transportOrder)
    {
        //
    }
}
