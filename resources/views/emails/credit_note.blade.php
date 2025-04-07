
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
                                <div class="col company-details" style="margin-top:-80px;">
                                  
                                    <h3 class="name" >
                                        <a target="_blank" href="javascript:;" style="color:  {{Auth::user()->employee->company ? Auth::user()->employee->company->color : Auth::user()->company->color }}">
                                    {{$company->name}}
                                    </a>
                                    </h3>
                                    <div>{{$company->street_address}}, {{$company->suburb}}, {{$company->city}} {{$company->country}}</div>
                                    <div>{{$company->phonenumber}}
                                    </div>
                                    <div>{{$company->email}}</div>
                                </div>
                            </div>
                        </header>
                        <main>
                            <div class="row contacts">
                                <div class="col invoice-to">
                                    <div class="text-gray-light">Invoice To:</div>
                                    <h4 class="to">{{$invoice->customer ? $invoice->customer->name : "undefined"}}</h4>
                                    <div class="address">{{$invoice->customer ? $invoice->customer->street_address : ""}} {{$invoice->customer ? $invoice->customer->suburb : ""}}, {{$invoice->customer ? $invoice->customer->city : ""}}, {{$invoice->customer ? $invoice->customer->country : ""}}</div>
                                    <div class="email"><a href="mailto:{{$invoice->customer->email}}">{{$invoice->customer->email}}</a>
                                    </div>
                                </div>
                                <div class="col invoice-details"  style="margin-top:-85px;">
                                    <h3 class="invoice-id">Invoice#: {{$invoice->invoice_number}}</h3>
                                    <h4 class="to">{{$invoice->subheading}}</h4>
                                    <div class="date">Date of Invoice: {{$invoice->date}}</div>
                                    <div class="date">Due Date: {{$invoice->expiry}}</div>
                                </div>
                            </div>
                            <table>
                                <thead>
                                    <tr>
                                        <th class="text-left"><strong>#</strong></th>
                                        <th class="text-left"> <strong>Trip Details</strong></th>
                                        {{-- <th class="text-left"> <strong>Additional Info</strong></th> --}}
                                        <th class="text-right"><strong>Weight</strong></th>
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
                                            {{-- <td class="text-left">
                                                    {{$invoice_item->description}}
                                            </td> --}}
                                            <td class="qty"> {{$trip->weight}} <small>Tons</small></td>
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
                                    <tr>
                                        <td colspan="2"></td>
                                        <td colspan="3">TAX {{$company->vat}}%</td>
                                      
                                      <td>  {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice->vat_amount,2)}}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"></td>
                                        <td colspan="3">GRAND TOTAL</td>
                                        <td>  {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice->total,2)}}</td>
                                    </tr>
                                </tfoot>
                            </table>
                            <br>
                            <div class="notices">
                                <div><strong>MEMO</strong></div>
                                <div class="notice">{{$company->invoice_memo}}</div>
                            </div>
                            <br>
                            <br>
                            <div class="notices">
                                <div> <strong>BANKING DETAILS</strong> </div>
                                    Bank: {{ $invoice->bank_account ? $invoice->bank_account->name : "" }} <br>
                                    Branch: {{ $invoice->bank_account ? $invoice->bank_account->branch : "" }} <br>
                                    Branch Code: {{ $invoice->bank_account ? $invoice->bank_account->branch_code : "" }} <br>
                                    Account Type: {{ $invoice->bank_account ? $invoice->bank_account->type : "" }} <br>
                                    Account Number: {{ $invoice->bank_account ? $invoice->bank_account->account_number : "" }} <br>
                            </div>
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
