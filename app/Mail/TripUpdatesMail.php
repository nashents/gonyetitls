<?php

namespace App\Mail;

use App\Models\Destination;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class TripUpdatesMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $trip;
    public $trips;
    public $company;
    public $customer;
    public $trip_group;
    public $cargo;
    public $origin;
    public $origin_destination;
    public $destination;
    public $final_destination;
    public $loading_point;
    public $offloading_point;

    public function __construct($trip, $company)
    {
     
        $this->trip = $trip;
        $this->trip_group = $trip->trip_group;
        if ( $this->trip_group) {
            $this->trips = $this->trip_group->trips;
        }
      
        $this->cargo = $trip->cargo;
        $this->loading_point = $trip->loading_point;
        $this->offloading_point = $trip->offloading_point;
        $this->customer = $trip->customer;
        $this->company = $company;

        $this->origin = Destination::find($this->trip->from)->country ? Destination::find($this->trip->from)->country->name : "";
        $this->origin_destination = Destination::find($this->trip->from)->city . ' ' . $this->origin ;
        $this->destination = Destination::find($this->trip->to)->country ? Destination::find($this->trip->to)->country->name : "";
        $this->final_destination = Destination::find($this->trip->to)->city . ' ' . $this->destination;
      
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from($this->company->noreply)
        ->subject('Customer Trip Status Updates')
        ->view('emails.trip_updates');
    }
}
