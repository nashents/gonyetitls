
@extends('layouts.main')
@section('extra-css')
@if (Auth::user()->employee->company)
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->employee->company->logo)!!}">
@elseif (Auth::user()->company)
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->company->logo)!!}">
@endif
@endsection
@section('title')
Invoice Preview |@if (Auth::user()->employee->company)
{{Auth::user()->employee->company->name}}
@elseif (Auth::user()->company)
{{Auth::user()->company->name}}
@endif
@endsection
@section('content')
<div class="container">
    <div class="card">
        <div class="card-body">
            <div id="invoice">
                <div class="invoice overflow-auto">
                    <div style="min-width: 600px">
                        <header>
                            <div class="row">
                                <div class="col">
                                    <a href="javascript:;">
                                                    <img src="{{asset('images/uploads/'.$company->logo)}}" width="100" alt="">
                                                </a>
                                </div>
                                <div class="col company-details" >
                                    <h6 class="name"><a target="_blank" href="javascript:;" style="color:  {{Auth::user()->employee->company ? Auth::user()->employee->company->color : Auth::user()->company->color }}">{{$company->name}}</a></h6>
                                    <div>{{$company->street_address}}, {{$company->suburb}}, {{$company->city}} {{$company->country}}</div>
                                    <div>{{$company->phonenumber}}</div>
                                    <div>{{$company->email}}</div>
                                </div>
                            </div>
                        </header>
                        <main>
                            <div class="row contacts">
                                <div class="col invoice-to">
                                    <div class="text-gray-light">BILL TO:</div>
                                    <h6 class="to">{{$invoice->customer ? $invoice->customer->name : "undefined"}}</h6>
                                    <div class="address" >{{$invoice->customer ? $invoice->customer->street_address : ""}} {{$invoice->customer ? $invoice->customer->suburb : ""}}, <br> {{$invoice->customer ? $invoice->customer->city : ""}}, {{$invoice->customer ? $invoice->customer->country : ""}}</div>
                                    <div class="email"><a href="mailto:{{$invoice->customer->email}}">{{$invoice->customer->email}}</a>
                                    </div>
                                </div>
                                <div class="col invoice-details"  >
                                    <div class="date" style="padding-bottom: 3px"> <strong>Invoice Number:</strong> {{$invoice->invoice_number}}</div>
                                    @if ($invoice->subheading)
                                    <div class="date" style="padding-bottom: 3px"> {{$invoice->subheading}}</div>
                                    @endif
                                    <div class="date" style="padding-bottom: 3px"><strong>Invoice Date:</strong> {{$invoice->date}}</div>
                                    @if ($invoice->expiry)
                                    <div class="date" style="padding-bottom: 3px"><strong>Payment Due:</strong> {{$invoice->expiry}}</div>
                                    @endif
                                </div>
                            </div>
                            <br>
                            @if ($invoice->reason == "Trip")
                            <table>
                                <thead>
                                    <tr>
                                        <th class="text-left"><strong>Trip#</strong></th>
                                        <th class="text-left"> <strong>Trip Details</strong></th>
                                        <th class="text-left"> <strong>Info</strong></th>
                                        <th class="text-right"><strong>Weight</strong><small>(Tons)</small></th>
                                        <th class="text-right"><strong>Rate</strong></th>
                                        <th class="text-right"><strong>Freight</strong></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($invoice_items as $invoice_item)
                                        @php
                                            $trip = $invoice_item->trip
                                        @endphp
                                         <tr>
                                            <td class="unit">{{ $trip->trip_number}}</td>
                                            <td class="text-left">
                                                    {{$invoice_item->trip_details}}
                                            </td>
                                            <td class="text-left">
                                                    {{$invoice_item->description}}
                                            </td>
                                            <td class="qty"> {{$trip->weight}}</td>
                                            <td class="unit">  {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($trip->rate,2)}}</td>
                                            <td class="unit">  {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($trip->turnover,2)}}</td>
                                        </tr>
        
                                    @endforeach
                                   
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2"></td>
                                        <td colspan="3">SUBTOTAL</td>
                                        <td>  {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice->subtotal,2)}}</td>
                                    </tr>
                                    @if (isset($invoice->vat) && $invoice->vat > 0) 
                                    <tr>
                                        <td colspan="2"></td>
                                        <td colspan="3">TAX {{$company->vat}}%</td>
                                      
                                      <td>  {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice->vat_amount,2)}}</td>
                                    </tr>
                                    @endif
                                    @if ($invoice->credit_notes)
                                    @foreach ($invoice->credit_notes as $credit_note)
                                        <tr>
                                            <td colspan="2"></td>
                                            <td colspan="3">Credit Note# <small>{{$credit_note->credit_note_number}}</small></td>
                                        
                                        <td> - {{ $credit_note->currency ? $credit_note->currency->symbol : "" }}{{number_format($credit_note->total,2)}}</td>
                                        </tr>
                                    @endforeach
                                  
                                    @endif
                                   
                                    <tr>
                                        <td colspan="2"></td>
                                        <td colspan="3">GRAND TOTAL</td>
                                        <td>  {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice->total,2)}}</td>
                                    </tr>
                                </tfoot>
                            </table>
                            @else   
                            <table>
                                <thead>
                                    <tr>
                                        <th class="text-left"><strong>#</strong></th>
                                        <th class="text-left"> <strong>Product</strong></th>
                                        <th class="text-right"><strong>Quantity</strong></th>
                                        <th class="text-right"><strong>Price</strong></th>
                                        <th class="text-right"><strong>Amount</strong></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($invoice_items as $invoice_item)
                                        @php
                                            $n = 1
                                        @endphp
                                         <tr>
                                            <td class="unit">{{ $n++}}</td>
                                            <td class="text-left">
                                                   <strong>{{$invoice_item->invoice_product ? $invoice_item->invoice_product->name : ""}}</strong>  <br>
                                                    {{$invoice_item->description}}
                                            </td>
                                            <td class="qty"> {{$invoice_item->qty}}</td>
                                            <td class="unit">
                                                @if ($invoice_item->amount)
                                                {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice_item->amount,2)}}        
                                                @endif
                                            </td>
                                            <td class="unit">
                                                @if ($invoice_item->subtotal)
                                                {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice_item->subtotal,2)}}
                                                @endif
                                            </td>
                                        </tr>
        
                                    @endforeach
                                   
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2"></td>
                                        <td colspan="2">SUBTOTAL</td>
                                        <td>  {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice->subtotal,2)}}</td>
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
                                        <td>  {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice->total,2)}}</td>
                                    </tr>
                                </tfoot>
                            </table>
                            @endif
                              
                            <div class="notices">
                                <div><strong>Notes / Terms & Conditions</strong></div>
                                <div class="notice">{{$company->invoice_memo}}</div>
                            </div>
                            @if ($invoice->bank_account)
                                    <table>
                                        <thead>
                                            <tr>
                                                <th  style="background-color: white;"><strong>Banking Details</strong></th>      
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                             
                                                
                                                <td class="text-left "  scope="col" >
                                                    @if ($invoice->bank_account->name )
                                                    <strong>Bank: </strong>{{ $invoice->bank_account->name }} <br> 
                                                    @endif
                                                    @if ($invoice->bank_account->branch)
                                                    <strong>Branch: </strong>{{ $invoice->bank_account->branch }} <br>
                                                    @endif
                                                    @if ($invoice->bank_account->branch_code)
                                                    <strong>Branch Code: </strong> {{ $invoice->bank_account->branch_code }} <br>
                                                    @endif
                                                    @if ($invoice->bank_account->swift_code)
                                                    <strong>Swift Code: </strong> {{ $invoice->bank_account->swift_code }} <br>
                                                    @endif
                                                    @if ($invoice->bank_account->type)
                                                    <strong>Account Type: </strong> {{ $invoice->bank_account->type }} <br> 
                                                    @endif
                                                   @if ($invoice->bank_account->account_number)
                                                   <strong>Account Number: </strong> {{ $invoice->bank_account->account_number }} <br>
                                                   @endif
                                                 
                                                 </td>
                                             
                                               
                                            </tr>
                                    
                                        </tbody>
                                    </table>
                                    @endif
                        </main>
                     <center> <footer>{{$company->invoice_footer}}</footer></center>  
                    </div>
                    <!--DO NOT DELETE THIS div. IT is responsible for showing footer always at the bottom-->
                    <div></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
