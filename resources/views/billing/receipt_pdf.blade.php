{{--
    Receipt for a payment against a Gonyeti license invoice (dompdf). Same
    classic styling (includes.pdf_css) and letterhead as the classic invoice
    layout, so the invoice and its receipts match. Expects $payment, $invoice,
    $client (the paying company), $company (Gonyeti), $receiptNumber, $balanceAfter.
--}}
<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Receipt {{ $receiptNumber }}</title>
    @include('includes.pdf_css')
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-body">
                <div id="invoice">
                    <div class="invoice overflow-auto">
                        <div style="min-width: 600px">
                            <header>
                                <div class="row">
                                    <div class="col">
                                        <img src="{{asset('images/uploads/'.$company->logo)}}" width="200" alt="">
                                    </div>
                                    <div class="col company-details">
                                        <h4 class="name" style="color: {{ $company->color ?: '#000000' }}">{{ $company->name }}</h4>
                                        <div>{{$company->street_address}} {{$company->suburb}} <br>
                                            {{$company->city}}, {{$company->country}}</div>
                                        <div>
                                            {{$company->phonenumber}}
                                            @if ($company->second_phonenumber) | {{$company->second_phonenumber}} @endif
                                            @if ($company->third_phonenumber) | {{$company->third_phonenumber}} @endif
                                        </div>
                                        <div>{{$company->email}}</div>
                                        @if ($company->vat_number)<div>VAT No.: {{$company->vat_number}}</div>@endif
                                        @if ($company->tin_number)<div>TIN.: {{$company->tin_number}}</div>@endif
                                    </div>
                                </div>
                                <div>
                                    <center><h2>Receipt</h2></center>
                                </div>
                            </header>
                            <main>
                                <div class="row contacts">
                                    <div class="col invoice-to">
                                        <div class="text-gray-light">RECEIVED FROM:</div>
                                        <h6 class="to"><strong>Customer Name: </strong> {{ $client?->name }}</h6>
                                        <div class="address">
                                            <strong>Customer Address: </strong>
                                            @if ($client?->street_address || $client?->suburb)
                                                {{ $client->street_address }} {{ $client->suburb }}, <br>
                                            @endif
                                            {{ $client?->city }} {{ $client?->country }}
                                        </div>
                                        @if ($client?->email)
                                            <div class="email"><strong>Customer Email: </strong> {{ $client->email }}</div>
                                        @endif
                                        @if ($client?->phonenumber)
                                            <div class="email"><strong>Customer Phonenumber: </strong> {{ $client->phonenumber }}</div>
                                        @endif
                                    </div>
                                    <div class="col invoice-details">
                                        <div class="date" style="padding-bottom: 3px"><strong>Receipt No.:</strong> {{ $receiptNumber }}</div>
                                        <div class="date" style="padding-bottom: 3px"><strong>Invoice No.:</strong> {{ $invoice->invoice_number }}</div>
                                        <div class="date" style="padding-bottom: 3px"><strong>Payment Date:</strong> {{ $payment->date->format('Y-m-d') }}</div>
                                        <div class="date" style="padding-bottom: 3px"><strong>Currency:</strong> {{ $invoice->currency?->name }}</div>
                                    </div>
                                </div>

                                <table>
                                    <thead>
                                        <tr>
                                            <th class="text-center"><strong>Description</strong></th>
                                            <th class="text-center"><strong>Payment Method</strong></th>
                                            <th class="text-center"><strong>Reference</strong></th>
                                            <th class="text-right"><strong>Amount</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-center">Payment for {{ $invoice->description ?: 'invoice ' . $invoice->invoice_number }}</td>
                                            <td class="unit text-center">{{ $payment->method }}</td>
                                            <td class="unit text-center">{{ $payment->reference }}</td>
                                            <td class="unit text-right">{{ $invoice->money($payment->amount) }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="1"></td>
                                            <td colspan="2">INVOICE TOTAL {{ $invoice->currency?->name }}</td>
                                            <td>{{ $invoice->money($invoice->amount) }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="1"></td>
                                            <td colspan="2">AMOUNT PAID</td>
                                            <td>{{ $invoice->money($payment->amount) }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="1"></td>
                                            <td colspan="2">BALANCE</td>
                                            <td>{{ $invoice->money($balanceAfter) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>

                                @if ($company->receipt_memo)
                                    <div class="notices">
                                        <div><strong>Memo / Terms & Conditions</strong></div>
                                        <div class="notice">{{ $company->receipt_memo }}</div>
                                    </div>
                                @endif
                            </main>

                            @if ($company->receipt_footer)
                                <center>
                                    <footer style="text-align: center;">{{ $company->receipt_footer }}</footer>
                                </center>
                            @endif
                        </div>
                        <div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
