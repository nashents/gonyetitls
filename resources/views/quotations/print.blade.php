
@extends('layouts.main')
@section('extra-css')
@if (Auth::user()->employee->company)
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->employee->company->logo)!!}">
@elseif (Auth::user()->company)
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->company->logo)!!}">
@endif
@endsection
@section('title')
Quotation Print |@if (Auth::user()->employee->company)
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
                                <div class="col" style="padding-bottom: 5px">
                                    <a href="#"><img src="{{asset('images/uploads/'.$company->logo)}}" width="150" alt=""> </a>
                                </div>
                                <div class="col company-details">
                                  
                                     <h4 class="name" style="color:  {{$company->color ? $company->color : "#000000" }}" >
                                        {{$company->name}}
                                    </h4>
                                    <div>{{$company->street_address}}, {{$company->suburb}}, {{$company->city}} {{$company->country}}</div>
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
                                <center><h2>QUOTATION</h2>  </center>
                            </div>
                        </header>
                        <main>
                            <div class="row contacts" >
                                <div class="col invoice-to" >
                                    <div class="text-gray-light">BILL TO:</div>
                                    <h6 class="to"> <strong>Customer Name: </strong> {{$quotation->customer ? $quotation->customer->name : ""}}</h6>
                                  
                                    <div class="address" >
                                        <strong>Customer Address: </strong>
                                        @if (isset($quotation->customer->street_address) || isset($quotation->customer->suburb))
                                         {{$quotation->customer ? $quotation->customer->street_address : ""}} {{$quotation->customer ? $quotation->customer->suburb : ""}}, <br>  
                                        @endif
                                         {{$quotation->customer ? $quotation->customer->city : ""}} {{$quotation->customer ? $quotation->customer->country : ""}}
                                    </div>
                                 
                                    @if (isset($quotation->customer->email))
                                    <div class="email">
                                        <strong>Customer Email: </strong> {{$quotation->customer->email}}
                                    </div>
                                    @endif
                                    @if (isset($quotation->customer->email))
                                    <div class="email">
                                        <strong>Customer Phonenumber: </strong>{{$quotation->customer->phonenumber}}
                                    </div>
                                    @endif
                                    <div class="email">
                                     
                                        <strong>Customer VAT No:</strong> {{$quotation->customer ? $quotation->customer->vat_number : ""}}
                                      
                                    </div>
                                    <div class="email">
                                      
                                        <strong>Customer TIN No:</strong> {{$quotation->customer ? $quotation->customer->tin_number : ""}}
                                       
                                    </div>
                                </div>
                                <div class="col invoice-details">
                                    <div class="date" style="padding-bottom: 3px"> <strong>Document No.:</strong> {{$quotation->quotation_number}}</div>
                                    <div class="date" style="padding-bottom: 3px"><strong>Date:</strong> {{$quotation->date}}</div>
                                    <div class="date" style="padding-bottom: 3px"><strong>Valid Until:</strong> {{$quotation->expiry}}</div>
                                    <div class="date" style="padding-bottom: 3px"><strong>Currency:</strong> {{$quotation->currency ? $quotation->currency->name : ""}}</div>
                                </div>
                            </div>
                            <table class="table table-bordered quotation-table">
                                <colgroup>
                                  <col class="col-hs" style="width: 8%">
                                    <col class="col-description" style="width: 40%">
                                    <col class="col-qty" style="width: 8%">
                                    <col class="col-unit" style="width: 11%">
                                    <col class="col-total-excl" style="width: 11%">
                                    <col class="col-vat" style="width: 11%">
                                    <col class="col-total-incl" style="width: 11%">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th class="text-left"><strong>HS Code</strong></th>
                                        <th class="text-left"><strong>Description</strong></th>
                                        <th class="text-center"><strong>Qty</strong></th>
                                        <th class="text-center"><strong>Unit Price</strong></th>
                                        <th class="text-center"><strong>Total(Excl)</strong></th>
                                        <th class="text-center"><strong>VAT Amount</strong></th>
                                        <th class="text-center"><strong>Total(Incl)</strong></th>
                                    </tr>
                                </thead>
                                <tbody>
                                   
                                    @foreach ($quotation->quotation_items as $item)
                                    <tr>
                                        @php
                                            $tax = $item->tax;
                                        @endphp
                                        <td class="unit text-center"> 
                                            @if ($tax && $tax->hs_code)
                                                    {{$tax->hs_code}}
                                            @endif
                                        </td>
                                        <td class="text-left">
                                            @if ($quotation->for_trips == False)
                                            <strong>{{$item->product ? $item->product->name : ""}} {{$item->product ? $item->product->identification_number : ""}} {{$item->inventory ? $item->inventory->serial_number : ""}}</strong>  <br>
                                            @endif
                                            {{$item->description}}
                                        </td>
                                        <td class="unit text-center">{{ $item->qty }}</td>
                                        <td class="unit text-center">{{ $quotation->currency ? $item->quotation->currency->symbol : "" }}{{number_format($item->amount,2)}}</td>
                                        <td class="unit text-center">
                                            @if (isset($item->subtotal))
                                                {{ $quotation->currency ? $item->quotation->currency->symbol : "" }}{{number_format($item->subtotal,2)}} 
                                            @endif   
                                        </td> 
                                        <td class="unit text-center">
                                            {{ $quotation->currency ? $quotation->currency->symbol : "" }}{{number_format($item->tax_amount,2)}}
                                        </td>
                                      
                                     <td class="unit text-center">
                                    @if (isset($item->subtotal_incl))
                                        {{ $quotation->currency ? $item->quotation->currency->symbol : "" }}{{number_format($item->subtotal_incl,2)}} 
                                    @endif   
                                </td> 
                                    </tr>
        
                                    @endforeach
                                </tbody>
                                <tfoot >
                                    <tr >
                                        <td colspan="4"></td>
                                        <td colspan="2">SUB-TOTAL {{ $quotation->currency ? $quotation->currency->name : "" }} <small>(Excl)</small></td>
                                        <td class="">  
                                            @if ($quotation->subtotal)
                                                {{ $quotation->currency ? $quotation->currency->symbol : "" }}{{number_format($quotation->subtotal,2)}}  
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="4"></td>
                                        <td colspan="2"> VAT TOTAL</td>
                                        <td>{{ $quotation->currency ? $quotation->currency->symbol : "" }}{{number_format($quotation->tax_amount ? $quotation->tax_amount : 0,2)}}</td>
                                    </tr>
                                    @if ($quotation->discount)
                                        <tr>
                                            <td colspan="4"></td>
                                            <td colspan="2">DISCOUNT {{$quotation->discount->description}}</td>
                                            
                                            <td>
                                                @if ($quotation->discount->unit == "currency")
                                                    {{$quotation->currency ? $quotation->currency->symbol : ""}}{{number_format($quotation->discount->amount ? $quotation->discount->amount : 0,2)}}
                                                    @elseif($quotation->discount->unit == "percentage")
                                                    {{number_format($quotation->discount->amount ? $quotation->discount->amount : 0,2)}} %
                                                    @endif 
                                            </td>
                                        </tr>
                                    @endif
                                    @if ($quotation->additional_costs->count()>0)
                                        @foreach ($quotation->additional_costs as $additional_cost)
                                            <tr>
                                                <td colspan="4">ADD COST(s)</td>
                                                <td colspan="2">{{$additional_cost->cost_item ? strtoupper($additional_cost->cost_item->name) : ""}}</td>
                                                <td>{{ $quotation->currency ? $quotation->currency->symbol : "" }}{{number_format($additional_cost->total ? $additional_cost->total : 0,2)}}</td>
                                            </tr> 
                                        @endforeach
                                    @endif
                                    <tr>
                                        <td colspan="4"></td>
                                        <td colspan="2">QUOTATION TOTAL {{ $quotation->currency ? $quotation->currency->name : "" }} </td>
                                        <td>
                                            @if ($quotation->total)
                                                  {{ $quotation->currency ? $quotation->currency->symbol : "" }}{{number_format($quotation->total,2)}}
                                            @endif
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                         
                            @if ($quotation->memo)
                            <div class="notices">
                                <div><strong>Notes / Terms & Conditions</strong></div>
                                <div class="notice">{{$quotation->memo}}</div>
                            </div>
                            @endif
                           
                            @if ( $quotation->bank_accounts->count()>0)
                                
                          
                                    <table>
                                        <thead>
                                            <tr>
                                                <th class="text-left" style="background-color: white;"><strong>BANKING DETAILS</strong></th>      
                                            </tr>
                                        </thead>
                                        <tbody>
                                          
                                            <tr>
                                                @foreach ($quotation->bank_accounts as $bank_account)
                                                <td class="text-left "  scope="col" >
                                                    @if (isset($bank_account->name))
                                                        <strong>Bank: </strong>{{ $bank_account->name }} <br>
                                                    @endif
                                                    @if (isset($bank_account->branch))
                                                        <strong>Branch: </strong>{{ $bank_account->branch }} <br>
                                                    @endif
                                                    @if (isset($bank_account->branch_code))
                                                        <strong>Branch Code: </strong> {{ $bank_account->branch_code }} <br>
                                                    @endif
                                                    @if (isset($bank_account->swift_code))
                                                        <strong>Swift Code: </strong> {{ $bank_account->swift_code }} <br>
                                                    @endif
                                                    @if (isset($bank_account->type))
                                                         <strong>Account Type: </strong> {{ $bank_account->type }} <br>
                                                    @endif
                                                    @if ($bank_account->account_name)
                                                       <strong>Account Name: </strong> {{ $bank_account->account_name }} <br>
                                                       @endif
                                                    @if (isset($bank_account->account_number))
                                                        <strong>Account Number: </strong> {{ $bank_account->account_number }} <br>
                                                    @endif
                                                   
                                                  
                                                </td>
                                                @endforeach
                                               
                                            </tr>
                                    
                                        </tbody>
                                    </table>
                                    @endif
                                   
                        </main>
                        <center> 
                           <footer class="print-footer" style="text-align: center; margin-top: 20px;">
                                {{$quotation->footer}}
                                @if ($company->show_branding ?? true)
                                    <br>
                                    <strong style="font-size: 18px;">Powered By</strong>
                                    <img src="{{asset('images/logo.png')}}" alt="" style="width: 20%; height:20%">
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
        window.addEventListener("load", function () {
            window.print();
        });
    </script>
@endsection

