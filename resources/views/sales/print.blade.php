@extends('layouts.main')
@section('extra-css')
@if (Auth::user()->employee->company)
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->employee->company->logo)!!}">
@elseif (Auth::user()->company)
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->company->logo)!!}">
@endif
@endsection
@section('title')
Invoice Print |@if (Auth::user()->employee->company)
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
                                     
                                            VAT No.: {{$company->vat_number}}
                                     
                                    </div>
                                    <div>
                                      
                                            TIN.: {{$company->tin_number}}
                                      
                                    </div>
                                </div>
                            </div>
                          
                            <div style="padding-top: 25px; padding-bottom:15px">
                                <center><h2>{{ Auth::user()->employee->company->fiscalize == TRUE ? " FISCAL TAX INVOICE" : "INVOICE"}} </h2>  </center>
                            </div>
                               
                                       
                        
                          
                            
                        </header>
                        <main>
                            <div class="row contacts">
                                <div class="col invoice-to" >
                                    <div class="text-gray-light">BILL TO:</div>
                                    <h4 class="to">{{$invoice->customer ? $invoice->customer->name : ""}}</h4>
                                  
                                    <div class="address" >
                                        @if (isset($invoice->customer->street_address) || isset($invoice->customer->suburb))
                                         {{$invoice->customer ? $invoice->customer->street_address : ""}} {{$invoice->customer ? $invoice->customer->suburb : ""}}, <br>  
                                        @endif
                                         {{$invoice->customer ? $invoice->customer->city : ""}} {{$invoice->customer ? $invoice->customer->country : ""}}
                                    </div>
                                    
                                    @if (isset($invoice->customer->email))
                                    <div class="email"><a href="mailto:{{$invoice->customer->email}}">{{$invoice->customer->email}}</a></div>
                                    @endif
                                    
                                    <div class="email">
                                     
                                            VAT No.: {{$invoice->customer ? $invoice->customer->vat_number : ""}}
                                     
                                    </div>
                                    <div class="email">
                                      
                                            TIN.: {{$invoice->customer ? $invoice->customer->tin_number : ""}}
                                      
                                    </div>
                                </div>
                                <div class="col invoice-details">
                                    @if (Auth::user()->employee->company->fiscalize == TRUE)
                                    <div class="date" style="padding-bottom: 3px"> <strong>Document No.:</strong> {{$invoice->invoice_number}}</div>
                                    @else   
                                    <div class="date" style="padding-bottom: 3px"> <strong>Invoice No.:</strong> {{$invoice->invoice_number}}</div>
                                    @endif
                                   
                                    @if ($invoice->subheading)
                                    <div class="date" style="padding-bottom: 3px"> {{$invoice->subheading}}</div>
                                    @endif
                                    <div class="date" style="padding-bottom: 3px"><strong>Invoice Date:</strong> {{$invoice->date}}</div>
                                    @if ($invoice->expiry)
                                    <div class="date" style="padding-bottom: 3px"><strong>Payment Due:</strong> {{$invoice->expiry}}</div>
                                    @endif
                                    <div class="date" style="padding-bottom: 3px"><strong>Currency:</strong> {{$invoice->currency ? $invoice->currency->name : ""}}</div>
                                    
                                </div>
                            </div>
                            @if ($invoice->reason == "Trip")
                            <table>
                                <thead>
                                    <tr class="text-center">
                                        <th class="text-center"> <strong>Description</strong></th>
                                        <th class="text-center"><strong>Qty</strong></th>
                                        <th class="text-center"><strong>Price</strong></th>
                                        <th class="text-center"><strong>Total</strong><small>(Excl)</small></th>
                                        <th class="text-center"><strong>VAT AMT</strong></th>
                                        <th class="text-center"><strong>Total</strong><small>(Incl)</small></th>
                                    </tr>
                                </thead>
                                <tbody>
                           
                                    @foreach ($invoice_items as $invoice_item)
                                         <tr >
                                            <td class="text-center">
                                                 {{$invoice_item->trip_details}}
                                            </td>
                                            <td class="unit text-center"> 
                                               {{ $invoice_item->qty }}
                                            </td>
                                            <td class="unit text-center"> 
                                                @if ($invoice_item->amount)
                                                    {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice_item->amount,2)}}
                                                @endif    
                                            </td>
                                            <td class="unit text-center">
                                                @if ($invoice_item->subtotal)
                                                    {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice_item->subtotal,2)}}
                                                @endif
                                            </td>
        
                                            <td class="unit text-center">
                                                @if (isset($invoice_item->tax_amount) && $invoice_item->tax_amount > 0)
                                                    {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice_item->tax_amount,2)}}
                                                @elseif(isset($invoice->vat) && $invoice->vat > 0) 
                                                    @php
                                                        $tax_amount = $invoice_item->subtotal * ($invoice->vat/100);
                                                    @endphp
                                                    @if (isset($tax_amount))
                                                        {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($tax_amount,2)}}
                                                    @endif
                                                @else
                                                    {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format(0,2)}}
                                                @endif
                                            </td>
                                          
                                            <td class="unit text-center">
                                                @if (isset($invoice_item->subtotal_incl))
                                                    {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice_item->subtotal_incl,2)}}
                                                @elseif(isset($tax_amount))
                                                    @php
                                                        $subtotal_incl =  $invoice_item->subtotal + $tax_amount;
                                                    @endphp
                                                    @if (isset($subtotal_incl))
                                                        {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($subtotal_incl,2)}}
                                                    @endif
                                                @else
                                                {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice_item->subtotal,2)}}
                                                @endif
                                            </td>
                                        </tr>
                                      
                                    @endforeach
                                   
                                </tbody>
                                <tfoot>
        
                                    <tr>
                                        <td colspan="3"></td>
                                        <td colspan="2">SUB-TOTAL {{ $invoice->currency ? $invoice->currency->name : "" }} <small>(Excl)</small></td>
                                        <td>  
                                            @if ($invoice->subtotal)
                                                {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice->subtotal,2)}}  
                                            @endif
                                        </td>
                                    </tr>
                                  
                                    <tr>
                                        <td colspan="3"></td>
                                        <td colspan="2">VAT TOTAL</td>
                                         
                                        <td>
                                            @if (isset($invoice->tax_amount) && $invoice->tax_amount > 0) 
                                                {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice->tax_amount,2)}}
                                            @else
                                                {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format(0,2)}}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3"></td>
                                        <td colspan="2">INVOICE TOTAL {{ $invoice->currency ? $invoice->currency->name : "" }} </td>
                                        <td>
                                            @if ($invoice->total)
                                                  {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice->total,2)}}
                                            @endif
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                            @else   
                            <table>
                                <thead>
                                    <tr>
                                        <th class="text-center"> <strong>Description</strong></th>
                                        <th class="text-right"><strong>Qty</strong></th>
                                        <th class="text-right"><strong>Price</strong></th>
                                        <th class="text-right"><strong>Total</strong><small>(Excl)</small></th>
                                        <th class="text-right"><strong>VAT AMT</strong></th>
                                        <th class="text-right"><strong>Total</strong><small>(Incl)</small></th>
                                    </tr>
                                </thead>
                                <tbody>
                                   
                                    @foreach ($invoice_items as $invoice_item)
                                         <tr>
                                            <td class="text-center">
                                                   <strong>{{$invoice_item->product ? $invoice_item->product->name : ""}}</strong>  <br>
                                                   {{$invoice_item->product ? $invoice_item->product->description : ""}}
                                            </td>
                                            <td class="unit text-center"> {{$invoice_item->qty}}</td>
                                            <td class="unit text-center">
                                                @if ($invoice_item->amount)
                                                {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice_item->amount,2)}}        
                                                @endif
                                            </td>
                                            <td class="unit text-center">
                                                @if ($invoice_item->subtotal)
                                                    {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice_item->subtotal,2)}}
                                                @endif
                                            </td>
        
                                            <td class="unit text-center">
                                                @if (isset($invoice->tax_amount) && $invoice->tax_amount > 0)
                                                    {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice_item->tax_amount,2)}}
                                                @else
                                                    {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format(0,2)}}
                                                @endif
                                            </td>
                                          
                                            <td class="unit text-center">
                                                @if (isset($invoice_item->subtotal_incl))
                                                    {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice_item->subtotal_incl,2)}}
                                                @endif
                                            </td>
                                        </tr>
        
                                    @endforeach
                                   
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3"></td>
                                        <td colspan="2">SUB-TOTAL {{ $invoice->currency ? $invoice->currency->name : "" }} <small>(Excl)</small></td>
                                        <td>  
                                            @if ($invoice->subtotal)
                                                {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice->subtotal,2)}}  
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3"></td>
                                        <td colspan="2">VAT TOTAL</td>
                                         
                                        <td>
                                            @if (isset($invoice->tax_amount) && $invoice->tax_amount > 0) 
                                            {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice->tax_amount,2)}}
                                            @else
                                            {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format(0,2)}}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3"></td>
                                        <td colspan="2">INVOICE TOTAL {{ $invoice->currency ? $invoice->currency->name : "" }} </td>
                                        <td>
                                            @if ($invoice->total)
                                                  {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice->total,2)}}
                                            @endif
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                            @endif
                           
                            @if ($invoice->memo)
                            <div class="notices">
                                <div><strong>Notes / Terms & Conditions</strong></div>
                                <div class="notice">{{$invoice->memo}}</div>
                            </div>
                            @endif
                          
                            <br>
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
                                                    @if ($invoice->bank_account->account_name)
                                                    <strong>Account Name: </strong> {{ $invoice->bank_account->account_name }} <br>
                                                    @endif
                                                   @if ($invoice->bank_account->account_number)
                                                   <strong>Account Number: </strong> {{ $invoice->bank_account->account_number }} <br>
                                                   @endif
                                                 
                                                 </td>
                                            </tr>
                                    
                                        </tbody>
                                    </table>
                                    @endif
                           
                            @if ($invoice->memo)
                            <div class="notices">
                                <div><strong>Notes / Terms & Conditions</strong></div>
                                <div class="notice">{{$invoice->memo}}</div>
                            </div>
                            @endif
                          
                            <br>
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
                                                    @if ($invoice->bank_account->account_name)
                                                    <strong>Account Name: </strong> {{ $invoice->bank_account->account_name }} <br>
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
                     <center> <footer style="   position: fixed; 
                        bottom: 0px; 
                        left: 0px; 
                        right: 0px;
                        height: 50px;">{{$invoice->footer}}</footer></center>  
                    </div>
                    <!--DO NOT DELETE THIS div. IT is responsible for showing footer always at the bottom-->
                    <div></div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('extra-js')
<script>
    window.addEventListener("load", window.print());
    </script>
@endsection

@endsection
