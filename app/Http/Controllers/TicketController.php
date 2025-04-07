<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('tickets.index');
    }

    public function cards($id)
    {
        $employee = Employee::find($id);
        return view('tickets.cards')->with('employee', $employee);
    }

    public function preview(Ticket $ticket){
      
        $company = Auth::user()->employee->company;

        return view('tickets.preview')->with([
            'ticket' => $ticket,
            'company' => $company,
          ]);
    }
    public function print(Ticket $ticket){

         $company = Auth::user()->employee->company;

        return view('tickets.print')->with([
            'ticket' => $ticket,
            'company' => $company,
          ]);
    }

    public function generatePDF(Ticket $ticket){

        $company = Auth::user()->employee->company;

        $data = [
            'ticket' => $ticket,
            'company' => $company,
        ];
        $pdf = PDF::loadView('tickets.ticket', $data);

        return $pdf->download('JobCard.pdf');

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
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function show(Ticket $ticket)
    {
        return view('tickets.show')->with('ticket', $ticket);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function edit(Ticket $ticket)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Ticket $ticket)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Ticket  $ticket
     * @return \Illuminate\Http\Response
     */
    public function destroy(Ticket $ticket)
    {
        
    }
}
