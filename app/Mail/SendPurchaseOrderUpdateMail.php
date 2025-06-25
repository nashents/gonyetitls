<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPurchaseOrderUpdateMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

 /**
     * Create a new message instance.
     *
     * @return void
     */
    public $filePath;
    public $company;
    public $purchase;
    public $vendor;
    public $ccEmails;
    public $user;
    public $employee;

    public function __construct($data, $filePath, $employee = null,  $notifications)
    {
        if ($notifications->count() > 1) {
            foreach ($notifications as $notification) {
                $this->ccEmails = $notification->email ? $notification->email : $notification->employee->email;
            }
        }
        $this->employee = $employee;
        $this->filePath = $filePath;
        $this->company = $data['company'];
        $this->purchase = $data['purchase'];
        $this->vendor = $this->purchase->vendor;
        $this->user = User::find($this->purchase->authorized_by_id);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
       return $this->view('emails.purchase_order')
                    ->from($this->company->noreply)
                    ->subject($this->company->name.' '.date('M Y').' Purchase Order For '. $this->vendor->name)
                    ->cc($this->ccEmails)
                    ->attach(Storage::path($this->filePath));
    }
}
