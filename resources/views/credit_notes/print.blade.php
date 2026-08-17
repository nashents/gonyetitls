
@extends('layouts.main')
@section('extra-css')
@if (Auth::user()->employee->company)
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->employee->company->logo)!!}">
@elseif (Auth::user()->company)
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->company->logo)!!}">
@endif
@endsection
@section('title')
Credit Note | @if (Auth::user()->employee->company)
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
                <x-loading/>
            <div class="invoice overflow-auto">
                <div style="min-width: 600px">
                    <header>
                        <div class="row">
                            <div class="col">
                                <a href="javascript:;">
                                    <img src="{{asset('images/uploads/'.$company->logo)}}" width="150" alt="">
                                </a>
                            </div>
                            <div class="col company-details">
                            
                                <h4 class="name" >
                                    <a target="_blank" href="javascript:;" style="color:  {{$company->color ? $company->color : "#000000" }}">
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
                                    VAT No.: {{$company->vat_number}}
                                </div>
                                <div>
                                    TIN.: {{$company->tin_number}}
                                </div>
                            </div>
                        </div>
                    
                        <div style="padding-top: 25px; padding-bottom:15px">
                            <center><h2>CREDIT NOTE</h2></center>
                        </div>
                    
                    </header>
                    <main>
                        <div class="row contacts">
                            <div class="col invoice-to" >
                                <div class="text-gray-light">BILL TO:</div>
                                <h6 class="to"> <strong>Customer Name: </strong> {{$credit_note->customer ? $credit_note->customer->name : ""}}</h6>
                            
                                <div class="address" >
                                    <strong>Customer Address: </strong>
                                    @if (isset($credit_note->customer->street_address) || isset($credit_note->customer->suburb))
                                    {{$credit_note->customer ? $credit_note->customer->street_address : ""}} {{$credit_note->customer ? $credit_note->customer->suburb : ""}}, <br>  
                                    @endif
                                    {{$credit_note->customer ? $credit_note->customer->city : ""}} {{$credit_note->customer ? $credit_note->customer->country : ""}}
                                </div>
                            
                                @if (isset($credit_note->customer->email))
                                <div class="email">
                                    <strong>Customer Email: </strong><a href="mailto:{{$credit_note->customer->email}}">{{$credit_note->customer->email}}</a>
                                </div>
                                @endif
                                @if (isset($credit_note->customer->email))
                                <div class="email">
                                    <strong>Customer Phonenumber: </strong>{{$credit_note->customer->phonenumber}}
                                </div>
                                @endif
                                <div class="email">
                                
                                    <strong>Customer VAT No:</strong> {{$credit_note->customer ? $credit_note->customer->vat_number : ""}}
                                
                                </div>
                                <div class="email">
                                
                                    <strong>Customer TIN No:</strong> {{$credit_note->customer ? $credit_note->customer->tin_number : ""}}
                                
                                </div>
                            </div>
                            <div class="col invoice-details">
                            @if (Auth::user()->employee->company->fiscalize == TRUE)
                            <div class="date" style="padding-bottom: 3px"> <strong>Document No.:</strong> {{$credit_note->credit_note_number}}</div>
                            @else   
                            <div class="date" style="padding-bottom: 3px"> <strong>Credit Note No.:</strong> {{$credit_note->credit_note_number}}</div>
                            @endif
                            <div class="date" style="padding-bottom: 3px"> <strong>Reference No.:</strong> {{$credit_note->invoice ? $credit_note->invoice->invoice_number : ""}}</div>
                           
                            <div class="date" style="padding-bottom: 3px"><strong>Date:</strong> {{$credit_note->date}}</div>
                          
                            <div class="date" style="padding-bottom: 3px"><strong>Currency:</strong> {{$credit_note->currency ? $credit_note->currency->name : ""}}</div>
                        </div>
                        </div>

                          <table>
                                <thead>
                                    <tr>
                                        <th class="text-center"> <strong>Description</strong></th>
                                        <th class="text-right"><strong>Qty</strong></th>
                                        <th class="text-right"><strong>Amount</strong></th>
                                        <th class="text-right"><strong>Subtotal</strong></th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @forelse ($credit_note_items as $credit_note_item)
                                         <tr>
                                            <td class="text-center">
                                                {{$credit_note_item->description}}
                                            </td>
                                            <td class="unit text-right"> {{$credit_note_item->qty}}</td>
                                            <td class="unit text-right">
                                                @if ($credit_note_item->amount)
                                                {{ $credit_note->currency ? $credit_note->currency->symbol : "" }}{{number_format($credit_note_item->amount,2)}}
                                                @endif
                                            </td>
                                            <td class="unit text-right">
                                                @if ($credit_note_item->subtotal)
                                                    {{ $credit_note->currency ? $credit_note->currency->symbol : "" }}{{number_format($credit_note_item->subtotal,2)}}
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center">No items</td></tr>
                                    @endforelse

                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3">SUB-TOTAL {{ $credit_note->currency ? $credit_note->currency->name : "" }} <small>(Excl)</small></td>
                                        <td>
                                            {{ $credit_note->currency ? $credit_note->currency->symbol : "" }}{{number_format($credit_note->subtotal,2)}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3">VAT TOTAL</td>
                                        <td>
                                            {{ $credit_note->currency ? $credit_note->currency->symbol : "" }}{{number_format($credit_note->tax_amount ?: 0,2)}}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3">CREDIT NOTE TOTAL {{ $credit_note->currency ? $credit_note->currency->name : "" }} </td>
                                        <td>
                                            @if ($credit_note->total)
                                                  {{ $credit_note->currency ? $credit_note->currency->symbol : "" }}{{number_format($credit_note->total,2)}}
                                            @endif
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        <br>
                        @if ($credit_note->credit_note_reason)
                            <div class="notices">
                            <strong>REASON FOR CREDIT: </strong>{{$credit_note->credit_note_reason}}
                            </div>
                        @endif
                        @if ($credit_note->memo)
                            <div class="notices">
                                <div><strong>Notes / Terms & Conditions</strong></div>
                                <div class="notice">{{$credit_note->memo}}</div>
                            </div>
                        @endif
                    
                        <br>
                    
                    </main>
                
                     <center> 
                            <footer style=" position:fixed; bottom: 0px; left: 0px; right: 0px; ">
                                {{$credit_note->footer}}
                                @if ($company->show_branding ?? true)
                                    <br>
                                    <strong style="font-size: 18px;">Powered By</strong> <img src="{{asset('images/logo.png')}}" alt="" style="width: 20%; height:20%">
                                @endif
                            </footer>
                    </center>  
                </div>
                <!--DO NOT DELETE THIS div. IT is responsible for showing footer always at the bottom-->
                <div></div>
            </div>
        </div>
        </div>
    </div>
</div>
@endsection

@section('extra-js')
<script>
  window.addEventListener("load", window.print());
</script>
@endsection

