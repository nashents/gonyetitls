<!doctype html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>Receipt Template</title>
@include('includes.css')

</head>
<body>

    <div class="container">
        <div class="card">
            <div class="card-body">
                <div id="invoice" >
                    <div class="invoice overflow-auto">
                        <div style="margin-left: -30px; margin-right:-30px">
                            <header >
                                <div class="row">
                                    <div class="col" style="margin-top:-15px">
                                        <a href="#"><img src="{{asset('images/uploads/'.$company->logo)}}" width="150" alt=""></a>
                                    </div>
                                    <div class="col company-details" style="margin-top:-100px">
                                      
                                        <h4 class="name" >
                                            <a target="_blank" href="javascript:;" style="color:  {{Auth::user()->employee->company ? Auth::user()->employee->company->color : Auth::user()->company->color }}">
                                                {{$company->name}}
                                            </a>
                                        </h4>
                                        <div>{{$company->street_address}} {{$company->suburb}} <br>
                                             {{$company->city}}, {{$company->country}}</div>
                                        <div>
                                            {{$company->phonenumber}}
                                            @if ($company->second_phonenumber)
                                            | {{$company->second_phonenumber}}
                                            @endif
                                            @if ($company->third_phonenumber)
                                            | {{$company->third_phonenumber}}
                                            @endif
                                        </div>
                                      
                                        
                                        <div>{{$company->email}}</div>
                                        @if ($company->second_email)
                                        <div>{{$company->second_email}}</div>
                                        @endif
                                        @if ($company->third_email)
                                        <div>{{$company->third_email}}</div>
                                        <br>
                                        @endif
                                        <div>
                                            @if (isset($company->vat_number))
                                                VAT No.: {{$company->vat_number}}
                                            @endif
                                        </div>
                                        <div>
                                            @if (isset($company->tin_number))
                                                TIN.: {{$company->tin_number}}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div style="padding-top: 25px; padding-bottom:15px">
                                    <center><h2>Receipt</h2>  </center>
                                </div>
                            </header>
                            <main>
                                <div class="row contacts"  style="padding-bottom: 10px;"  style="margin-bottom: 20px">
                                    <div class="col invoice-to">
                                        <div class="text-gray-light">Receipt For:</div>
                                        <h4 class="to">{{$receipt->payment->customer ? $receipt->payment->customer->name : ""}}</h4>
                                      
                                        <div class="address" >
                                            @if (isset($receipt->payment->customer->street_address) || isset($receipt->payment->customer->suburb))
                                             {{$receipt->payment->customer ? $receipt->payment->customer->street_address : ""}} {{$receipt->payment->customer ? $receipt->payment->customer->suburb : ""}}, <br>  
                                            @endif
                                             {{$receipt->payment->customer ? $receipt->payment->customer->city : ""}} {{$receipt->payment->customer ? $receipt->payment->customer->country : ""}}
                                        </div>
                                        
                                        @if (isset($receipt->payment->customer->email))
                                        <div class="email"><a href="mailto:{{$receipt->payment->customer->email}}">{{$receipt->payment->customer->email}}</a></div>
                                        @endif
                                        
                                        <div class="email">
                                            @if (isset($receipt->payment->customer->vat_number))
                                                VAT No.: {{$receipt->payment->customer ? $receipt->payment->customer->vat_number : ""}}
                                            @endif
                                        </div>
                                        <div class="email">
                                            @if (isset($receipt->payment->customer->tin_number))
                                                TIN.: {{$receipt->payment->customer ? $receipt->payment->customer->tin_number : ""}}
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col invoice-details" style="margin-top:-120px;">
                                        <div class="date" style="padding-bottom: 3px"> <strong>Receipt Number: </strong>{{$receipt->receipt_number}}</div>
                                        @if (isset($invoice))
                                            <div class="date" style="padding-bottom: 3px"> <strong>Invoice Number: </strong>{{$receipt->invoice->invoice_number}}</div>
                                        @endif
                                        <div class="date" style="padding-bottom: 3px"> <strong>Payment Date:</strong> {{$receipt->date}}</div>
                                        <div class="date" style="padding-bottom: 3px"> <strong>Currency:</strong> {{$receipt->currency ? $receipt->currency->name : ""}}</div>
                                    </div>
                                </div>
                                <br>
                                @if (isset($invoice))
                                <table>
                                    <thead>
                                        <tr>
                                       
                                            <th class="text-left"> <strong>Description</strong></th>
                                            <th class="text-right"><strong>Qty</strong></th>
                                            <th class="text-right"><strong>Rate</strong></th>
                                            <th class="text-right"><strong>Freight</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($invoice_items as $invoice_item)
                                        @php
                                            $invoice = $invoice_item->invoice;
                                        @endphp
                                             <tr>
                                               
                                                <td class="text-left">
                                                  @if ($invoice_item->trip_details)
                                                  {{$invoice_item->trip_details}}
                                                  @else   
                                                  {{$invoice_item->description}}
                                                  @endif
                                              </td>
                                                <td class="qty"> {{$invoice_item->qty}}</td>
                                                <td class="unit">  {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice_item->amount,2)}}</td>
                                                <td class="unit">  {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice_item->subtotal,2)}}</td>
                                            </tr>
            
                                        @endforeach
                                       
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="2"></td>
                                            <td colspan="2">SUBTOTAL</td>
                                            <td>  {{ $receipt->currency ? $receipt->currency->symbol : "" }}{{number_format($receipt->invoice->subtotal,2)}}</td>
                                        </tr>
                                        @if (isset($invoice->vat) && $invoice->vat > 0) 
                                        <tr>
                                            <td colspan="2"></td>
                                            <td colspan="2">TAX {{$company->vat}}%</td>
                                          
                                          <td>  {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice->vat_amount,2)}}</td>
                                        </tr>
                                        @endif
                                        @if ($invoice->credit_notes)
                                        @foreach ($invoice->credit_notes as $credit_note)
                                            <tr>
                                                <td colspan="2"></td>
                                                <td colspan="2">Credit Note# <small>{{$credit_note->credit_note_number}}</small></td>
                                            
                                            <td> - {{ $credit_note->currency ? $credit_note->currency->symbol : "" }}{{number_format($credit_note->total,2)}}</td>
                                            </tr>
                                        @endforeach
                                      
                                        @endif
                                        <tr>
                                            <td colspan="2"></td>
                                            <td colspan="2">GRAND TOTAL</td>
                                            <td>  {{ $receipt->currency ? $receipt->currency->symbol : "" }}{{number_format($receipt->invoice->total,2)}}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"></td>
                                            <td colspan="2">AMOUNT PAID</td>
                                            <td>  {{ $receipt->currency ? $receipt->currency->symbol : "" }}{{number_format($receipt->amount,2)}}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2"></td>
                                            <td colspan="2">BALANCE</td>
                                            <td>  {{ $receipt->currency ? $receipt->currency->symbol : "" }}{{number_format($receipt->balance,2)}}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                                @else
                                <table>
                                    <thead>
                                        <tr>
                                            <th class="text-center"> <strong>Description</strong></th>
                                            <th class="text-center"><strong>Payment Method</strong></th>
                                            <th class="text-center"><strong>Amount</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                   
                                             <tr>
                                                <td class="text-center">
                                                    Customer payment received
                                                </td>
                                                <td class="text-center">
                                                    {{$receipt->payment ? $receipt->payment->mode_of_payment : ""}}
                                              </td>
                                                <td class="unit text-center"> {{$receipt->currency ? $receipt->currency->symbol : ""}}{{number_format($receipt->amount,2)}}</td>
                                            </tr>
                                    </tbody>
                                    <tfoot>    
                                        <tr>
                                            <td colspan="1"></td>
                                            <td colspan="1" class="text-center">AMOUNT PAID {{$receipt->currency ? $receipt->currency->name : ""}} </td>
                                            <td class=" unit text-center">  {{ $receipt->currency ? $receipt->currency->symbol : "" }}{{number_format($receipt->amount,2)}}</td>
                                        </tr>
                                       
                                    </tfoot>
                                </table>
                                @endif
                                <br>
                                @if ($receipt->company->receipt_memo != "")
                                <div class="notices">
                                    <div><strong>Memo / Terms & Conditions</strong></div>
                                    <div class="notice">{{$company->receipt_memo}}</div>
                                </div>
                                @endif           
                            </main>

                            @if ($receipt->company->receipt_footer != "")
                         <center> <footer style="   position: fixed; 
                            bottom: -60px; 
                            left: 0px; 
                            right: 0px;
                            height: 50px;">{{$company->receipt_footer}}</footer></center>  
                         @endif
                        </div>
                        <!--DO NOT DELETE THIS div. IT is responsible for showing footer always at the bottom-->
                        <div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>