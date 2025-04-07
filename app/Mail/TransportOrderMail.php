<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TransportOrderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $trailer_regnumbers;
    public $collection_point;
    public $delivery_point;
    public $weight;
    public $cargo;
    public $measurement;
    public $litreage;
    public $quantity;
    public $authorized_by;
    public $checked_by;
    public $start_date;
    public $email;
    public $driver;
    public $regnumbers;
    public $trip;
    public $date;
    public $horse;
    public $transporter;
    public $company;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $company)
    {
        $this->email = $data['email'];
        $this->date = $data['date'];
        $this->collection_point = $data['collection_point'];
        $this->delivery_point = $data['delivery_point'];
        $this->checked_by = $data['checked_by'];
        $this->authorized_by = $data['authorized_by'];
        $this->regnumbers = $data['regnumbers'];
        $this->driver = $data['driver'];
        $this->horse = $data['horse'];
        $this->trip = $data['trip'];
        $this->transporter = $data['transporter'];
        $this->cargo = $data['cargo'];
        $this->quantity = $data['quantity'];
        $this->litreage = $data['litreage'];
        $this->weight = $data['weight'];
        $this->measurement = $data['measurement'];
        $this->company = $company;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.transport_order')
                    ->from($this->company->noreply)
                    ->subject('Transportation Order Notification');
    }
}
