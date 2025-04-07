<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FuelOrderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $data;
    public $station_email;
    public $date;
    public $order_number;
    public $driver;
    public $horse;
    public $checked_by;
    public $authorized_by;
    public $fuel_type;
    public $quantity;
    public $delivery_point;
    public $collection_point;
    public $station_name;
    public $regnumber;
    public $company;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data, $company)
    {
        $this->station_email = $data['station_email'];
        $this->collection_point = $data['collection_point'];
        $this->delivery_point = $data['delivery_point'];
        $this->checked_by = $data['checked_by'];
        $this->authorized_by = $data['authorized_by'];
        $this->date = $data['date'];
        $this->regnumber = $data['regnumber'];
        $this->station_name = $data['station_name'];
        $this->driver = $data['driver'];
        $this->horse = $data['horse'];
        $this->order_number = $data['order_number'];
        $this->fuel_type = $data['fuel_type'];
        $this->quantity = $data['quantity'];
        $this->company = $company;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.fuel_order')
                    ->from($this->company->noreply)
                    ->subject('Fuel Order Notification');
    }
}
