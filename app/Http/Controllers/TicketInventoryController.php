<?php

namespace App\Http\Controllers;

use App\Models\TicketInventory;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\StoreTicketInventoryRequest;
use App\Http\Requests\UpdateTicketInventoryRequest;

class TicketInventoryController extends Controller
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
     * @param  \App\Http\Requests\StoreTicketInventoryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTicketInventoryRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TicketInventory  $ticketInventory
     * @return \Illuminate\Http\Response
     */
    public function show(TicketInventory $ticketInventory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TicketInventory  $ticketInventory
     * @return \Illuminate\Http\Response
     */
    public function edit(TicketInventory $ticketInventory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTicketInventoryRequest  $request
     * @param  \App\Models\TicketInventory  $ticketInventory
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTicketInventoryRequest $request, TicketInventory $ticketInventory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TicketInventory  $ticketInventory
     * @return \Illuminate\Http\Response
     */
    public function destroy(TicketInventory $ticketInventory)
    {
        $requisition = $ticketInventory->inventory_requisition;
        if (isset($requisition)) {
            $requisition->delete();
        }

        $dispatch = $ticketInventory->inventory_dispatch;
        if (isset($dispatch)) {
            $dispatch->delete();
        }

        $inventory = $ticketInventory->inventory;
        if (isset($inventory)) {
            $inventory->balance = $inventory->balance + $ticketInventory->weight;
            if ($inventory->balance <= 0) {
                $inventory->status = 0;
            }else {
                $inventory->status = 1;
            }
            $inventory->update();
        }
   
        $bill = $ticketInventory->bill;
        $bill_expenses = $bill->bill_expenses;
        if (isset($bill_expenses)) {
            foreach ($bill_expenses as $bill_expense) {
                $bill_expense->delete();
            }
        }
        if(isset($bill)){
            $bill->delete();
        }
      

        $ticketInventory->delete();

        Session::flash('success','Ticket Inventory Deleted Successfully');
        return redirect()->back();
    }
}
