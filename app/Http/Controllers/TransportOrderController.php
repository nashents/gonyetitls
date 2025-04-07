<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\TransportOrder;
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
    public function order()
    {
        return view('transport_orders.order');
    }
    public function preview(TransportOrder $transport_order){
        if (isset(Auth::user()->employee->company)) {
            $company = Auth::user()->employee->company;
        }elseif (isset(Auth::user()->company)) {
          $company =  Auth::user()->company;
        }
        $customer = $transport_order->trip->customer;
        return view('transport_orders.preview')->with([
            'transport_order' => $transport_order,
            'company' => $company,
            'customer' => $customer,
          ]);
    }
    public function print(TransportOrder $transport_order){

        if (isset(Auth::user()->employee->company)) {
            $company = Auth::user()->employee->company;
        }elseif (isset(Auth::user()->company)) {
          $company =  Auth::user()->company;
        }
        $customer = $transport_order->trip->customer;
        return view('transport_orders.print')->with([
            'transport_order' => $transport_order,
            'company' => $company,
            'customer' => $customer,
          ]);
    }

    public function generatePDF(TransportOrder $transportOrder){
        if (isset(Auth::user()->employee->company)) {
            $company = Auth::user()->employee->company;
        }elseif (isset(Auth::user()->company)) {
          $company =  Auth::user()->company;
        }
        $customer = $transportOrder->trip->customer;
        $data = [
            'transport_order' => $transportOrder,
            'company' => $company,
            'customer' => $customer,
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
        //
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
