<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Ticket;
use App\Models\Employee;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
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

    public function jobcard(Ticket $ticket){
        return view('tickets.jobcard')->with([
            'ticket' => $ticket,
          ]);
    }

    public function preview(Ticket $ticket){
      
        $company = Auth::user()->employee->company;

        return view('tickets.preview')->with([
            'ticket' => $ticket,
            'company' => $company,
          ]);
    }
    public function print(Ticket $ticket){
        $booking = $ticket->booking;
        $authorizer = User::find($booking->authorized_by_id);
         $company = Auth::user()->employee->company;

        return view('tickets.print')->with([
            'ticket' => $ticket,
            'company' => $company,
            'authorizer' => $authorizer,
          ]);
    }

    public function generatePDF(Ticket $ticket){
    
        $booking = $ticket->booking;
        $authorizer = User::find($booking->authorized_by_id);
        $company = Auth::user()->employee->company;

        $data = [
            'ticket' => $ticket,
            'company' => $company,
            'authorizer' => $authorizer,
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
