<?php

namespace App\Http\Livewire\Invoices;

use App\Models\Invoice;
use Livewire\Component;
use Barryvdh\DomPDF\PDF;
use App\Mail\SendingInvoiceMail;
use Illuminate\Support\Facades\Mail;

class Preview extends Component
{
    public $invoice;
    public $invoice_items;
    public $company;

    public function mount($invoice, $invoice_items, $company){
        $this->invoice = $invoice;
        $this->invoice_items = $invoice_items;
        $this->company = $company;
    }

    public function sendEmail($id){
        $invoice = Invoice::find($id);
        $invoice_items = $invoice->invoice_items;
        $company = $invoice->company;
        $this->email = $invoice->customer ? $invoice->customer->email : "";
      
    
        if ( $this->email != "") {
          
            try {
               
            Mail::to($this->email)->send(new SendingInvoiceMail($invoice, $invoice_items, $company));
        }
        catch(\Exception $e){
        // Set Flash Message
        $this->dispatchBrowserEvent('alert',[
            'type'=>'error',
            'message'=>"Something went wrong while sending email!!"
        ]);
    }
        }

       
        return redirect()->back();
        $this->dispatchBrowserEvent('alert',[
            'type'=>'success',
            'message'=>"Email Sent Successfully!!"
        ]);
    }

    public function print($id){
        $invoice = Invoice::find($id);
        $trip = $invoice->trip;
        return view('invoices.print')->with([
            'invoice' =>  $this->invoice,
            'company' =>   $this->company,
            'invoice_items' =>  $this->invoice_items

        ]);
    }

    public function generatePdf($id){
        $invoice = Invoice::find($id);
        $data = [
            'invoice' =>  $this->invoice,
            'company' =>   $this->company,
            'invoice_items' =>  $this->invoice_items
        ];
        $pdf = PDF::loadView('invoices.invoice', $data);

        return $pdf->download('invoice.pdf');

    }
    
    public function render()
    {
        return view('livewire.invoices.preview');
    }
}
