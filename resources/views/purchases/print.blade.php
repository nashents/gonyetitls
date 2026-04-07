@extends('layouts.main')
@section('extra-css')
@if (Auth::user()->employee->company)
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->employee->company->logo)!!}">
@elseif (Auth::user()->company)
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->company->logo)!!}">
@endif
@endsection
@section('title')
Purchase Order Print | @if (Auth::user()->employee->company)
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
                                                    <img src="{{asset('images/uploads/'.$company->logo)}}" width="200" alt="">
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
                            
                        </header>
                        <main>
                            <div class="row contacts">
                                <div class="col invoice-to" >
                                    <div class="text-gray-light">BILL TO:</div>
                                    <h4 class="to">{{$purchase->vendor ? $purchase->vendor->name : ""}}</h4>
                                  
                                    <div class="address" >
                                        @if (isset($purchase->vendor->street_address) || isset($purchase->vendor->suburb))
                                         {{$purchase->vendor ? $purchase->vendor->street_address : ""}} {{$purchase->vendor ? $purchase->vendor->suburb : ""}}, <br>  
                                        @endif
                                         {{$purchase->vendor ? $purchase->vendor->city : ""}} {{$purchase->vendor ? $purchase->vendor->country : ""}}
                                    </div>
                                    
                                    @if (isset($purchase->vendor->email))
                                    <div class="email"><a href="mailto:{{$purchase->vendor->email}}">{{$purchase->vendor->email}}</a></div>
                                    @endif
                                    
                                    <div class="email">
                                     
                                            VAT No.: {{$purchase->vendor ? $purchase->vendor->vat_number : ""}}
                                      
                                    </div>
                                    <div class="email">
                                      
                                            TIN.: {{$purchase->vendor ? $purchase->vendor->tin_number : ""}}
                                       
                                    </div>
                                </div>
                                <div class="col invoice-details">
                                    <div class="date" style="padding-bottom: 3px"> <strong>Purchase Order No.:</strong> {{$purchase->purchase_number}}</div>
                                    <div class="date" style="padding-bottom: 3px"><strong>Order Date:</strong> {{$purchase->date}}</div>
                                    <div class="date" style="padding-bottom: 3px"><strong>Order Expiry:</strong> {{$purchase->expiry}}</div>
                                    <div class="date" style="padding-bottom: 3px"><strong>Currency:</strong> {{$purchase->currency ? $purchase->currency->name : ""}}</div>
                                    
                                </div>
                            </div>
                        
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
                                   
                                    @foreach ($purchase_products as $purchase_product)
                                         <tr >
                                            <td class="text-center">
                                                @if ($purchase_product)
                                                    {{ $purchase_product->product?->brand?->name ?? '' }}
                                                    {{ $purchase_product->product?->name ?? '' }}
                                                    {{ $purchase_product->product?->description ?? '' }}
                                                @endif
                                            </td>
                                            <td class="unit text-center"> 
                                               {{$purchase_product->qty }}
                                            </td>
                                            <td class="unit text-center"> 
                                                @if ($purchase_product->amount)
                                                    {{ $purchase->currency ? $purchase->currency->symbol : "" }}{{number_format($purchase_product->amount,2)}}
                                                @endif    
                                            </td>
                                            <td class="unit text-center">
                                                @if ($purchase_product->subtotal)
                                                    {{ $purchase->currency ? $purchase->currency->symbol : "" }}{{number_format($purchase_product->subtotal,2)}}
                                                @endif
                                            </td>
        
                                            <td class="unit text-center">
                                                @if (isset($purchase_product->tax_amount) &&$purchase_product->tax_amount > 0)
                                                    {{ $purchase->currency ? $purchase->currency->symbol : "" }}{{number_format($purchase_product->tax_amount,2)}}
                                                @elseif(isset($purchase->vat) && $purchase->vat > 0) 
                                                    @php
                                                        $tax_amount =$purchase_product->subtotal * ($purchase->vat/100);
                                                    @endphp
                                                    @if (isset($tax_amount))
                                                        {{ $purchase->currency ? $purchase->currency->symbol : "" }}{{number_format($tax_amount,2)}}
                                                    @endif
                                                @else
                                                    {{ $purchase->currency ? $purchase->currency->symbol : "" }}{{number_format(0,2)}}
                                                @endif
                                            </td>
                                          
                                            <td class="unit text-center">
                                                @if (isset($purchase_product->subtotal_incl))
                                                    {{ $purchase->currency ? $purchase->currency->symbol : "" }}{{number_format($purchase_product->subtotal_incl,2)}}
                                                @elseif(isset($tax_amount))
                                                    @php
                                                        $subtotal_incl = $purchase_product->subtotal + $tax_amount;
                                                    @endphp
                                                    @if (isset($subtotal_incl))
                                                        {{ $purchase->currency ? $purchase->currency->symbol : "" }}{{number_format($subtotal_incl,2)}}
                                                    @endif
                                                @else
                                                {{ $purchase->currency ? $purchase->currency->symbol : "" }}{{number_format($purchase_product->subtotal,2)}}
                                                @endif
                                            </td>
                                        </tr>
                                      
                                    @endforeach
                                   
                                </tbody>
                                <tfoot>
        
                                    <tr>
                                        <td colspan="3"></td>
                                        <td colspan="2">SUB-TOTAL {{ $purchase->currency ? $purchase->currency->name : "" }} <small>(Excl)</small></td>
                                        <td class="text-center">  
                                            @if (isset($purchase->purchase_products))
                                                {{ $purchase->currency ? $purchase->currency->symbol : "" }}{{number_format($purchase->purchase_products->where('subtotal','!=',Null)->where('subtotal','!=','')->sum('subtotal'),2)}}  
                                            @endif
                                        </td>
                                    </tr>
                                  
                                    <tr>
                                        <td colspan="3"></td>
                                        <td colspan="2">VAT TOTAL</td>
                                         
                                        <td class="text-center">
                                            @if (isset($purchase->tax_amount) && $purchase->tax_amount > 0) 
                                                {{ $purchase->currency ? $purchase->currency->symbol : "" }}{{number_format($purchase->tax_amount,2)}}
                                            @else
                                                {{ $purchase->currency ? $purchase->currency->symbol : "" }}{{number_format(0,2)}}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3"></td>
                                        <td colspan="2">PURCHASE ORDER TOTAL {{ $purchase->currency ? $purchase->currency->name : "" }} </td>
                                        <td class="text-center">
                                            @if ($purchase->total)
                                                  {{ $purchase->currency ? $purchase->currency->symbol : "" }}{{number_format($purchase->total,2)}}
                                            @endif
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        
                        </main>
                     
                        <center> 
                            <footer style=" position:fixed; bottom: 0px; left: 0px; right: 0px; ">
                                
                                <br>
                                <strong style="font-size: 18px;">Powered By</strong> <img src="{{asset('images/basilmark-logo.png')}}" alt="" style="width: 20%; height:20%">    
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

@section('extra-js')
<script>
    window.addEventListener("load", window.print());
    </script>
@endsection

@endsection
