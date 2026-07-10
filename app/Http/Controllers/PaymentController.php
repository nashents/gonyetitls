<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendRemittanceAdviceMail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('payments.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('payments.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorePaymentRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePaymentRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function show(Payment $payment)
    {
        return view('payments.show')->with('payment',$payment);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function edit(Payment $payment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdatePaymentRequest  $request
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePaymentRequest $request, Payment $payment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Payment  $payment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Payment $payment)
    {


        Session::flash('success','Payment Deleted Successfully');
        return redirect()->back();
    }

    /**
     * Bills settled by this payment, either directly (single bill_id)
     * or via bill_payments (split payment across multiple bills).
     */
    private function remittanceAdviceData(Payment $payment): array
    {
        $company = Auth::user()->employee->company;
        $vendor = $payment->vendor;

        $bills = $payment->bill
            ? collect([$payment->bill])
            : $payment->bill_payments->map->bill->filter()->values();

        return [
            'payment' => $payment,
            'vendor' => $vendor,
            'company' => $company,
            'bills' => $bills,
        ];
    }

    public function remittanceAdvicePreview(Payment $payment)
    {
        abort_unless($payment->vendor_id, 404);

        return view('remittance_advice.preview')->with($this->remittanceAdviceData($payment));
    }

    public function remittanceAdvicePrint(Payment $payment)
    {
        abort_unless($payment->vendor_id, 404);

        return view('remittance_advice.print')->with($this->remittanceAdviceData($payment));
    }

    public function remittanceAdvicePDF(Payment $payment)
    {
        abort_unless($payment->vendor_id, 404);

        $data = $this->remittanceAdviceData($payment);

        $pdf = PDF::loadView('remittance_advice.remittance_advice', $data);

        $fileName = 'remittance_advice_' . time() . '.pdf';
        $filePath = 'myfiles/documents/' . $fileName;
        Storage::disk('local')->put($filePath, $pdf->output());

        return $pdf->download('remittance_advice.pdf');
    }

    public function remittanceAdviceEmail(Payment $payment)
    {
        abort_unless($payment->vendor_id, 404);

        $data = $this->remittanceAdviceData($payment);

        $pdf = PDF::loadView('remittance_advice.remittance_advice', $data);

        $fileName = 'remittance_advice_' . time() . '.pdf';
        $filePath = 'myfiles/documents/' . $fileName;
        Storage::disk('local')->put($filePath, $pdf->output());

        if ($data['vendor'] && $data['vendor']->email) {
            Mail::to($data['vendor']->email)->send(new SendRemittanceAdviceMail($data, $filePath));
        }

        Session::flash('success', 'Remittance Advice Sent Successfully');
        return redirect()->back();
    }
}
