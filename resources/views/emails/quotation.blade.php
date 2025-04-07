
@extends('layouts.main')
@section('extra-css')
@if (Auth::user()->employee->company)
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->employee->company->logo)!!}">
@elseif (Auth::user()->company)
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->company->logo)!!}">
@endif
@endsection
@section('title')
Quotation Preview |@if (Auth::user()->employee->company)
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
                                    <a href="#"><img src="{{asset('images/uploads/'.$company->logo)}}" width="100" alt=""></a>
                                </div>
                                <div class="col company-details" >
                                    <h6 class="name" >
                                        <a target="_blank" href="#" style="color:  {{Auth::user()->employee->company ? Auth::user()->employee->company->color : Auth::user()->company->color }}">{{$company->name}}</a>
                                    </h6>
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
                                    <div class="text-gray-light">BILL TO:</div>
                                    <h6 class="to">{{$quotation->customer->name}}</h6>
                                    <div class="address">{{$quotation->customer->street_address}} {{$quotation->customer->suburb}}, {{$quotation->customer->city}}, {{$quotation->customer->country}}</div>
                                    <div class="email"><a href="mailto:{{$quotation->customer->email}}">{{$quotation->customer->email}}</a>
                                    </div>
                                </div>
                                <div class="col invoice-details" >
                                    <h6 class="invoice-id">Quotation# {{$quotation->quotation_number}}</h6>
                                    <h6 class="to">{{Str::limit($quotation->subheading,20,'...')}}</h6>
                                    <div class="date">Date of Invoice: {{$quotation->date}}</div>
                                    <div class="date">Due Date: {{$quotation->expiry}}</div>
                                </div>
                            </div>
                            <table>
                                <thead>
                                    <tr>
                                        <th class="text-left"><strong>#</strong></th>
                                        <th class="text-left"> <strong>Trip Details</strong></th>
                                        <th class="text-left"> <strong>Info</strong></th>
                                        <th class="text-right"><strong>Weight</strong><small>(Tons)</small></th>
                                        <th class="text-right"><strong>Rate</strong></th>
                                        <th class="text-right"><strong>Freight</strong></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $n = 1;
                                    @endphp
                                    @foreach ($quotation->quotation_products as $product)
                                    <tr>
                                        <td class="unit" style="font-size:15px">{{$n++}}</td>
                                        @php
                                        $from = App\Models\Destination::find($product->from);
                                        $to = App\Models\Destination::find($product->to);
                                        @endphp
                                        <td class="text-left" style="font-size:15px">
                                        <strong>Cargo:</strong> {{ucfirst($product->cargo->group)}} {{$product->cargo->name}}, <strong>From:</strong> {{$from->country ? $from->country->name : ""}}  {{$from->city}} <strong>To:</strong> {{$to->country ? $to->country->name : ""}} {{$to->city}}, <strong>Loading Point:</strong> {{$product->loading_point ? $product->loading_point->name : ""}} <strong>Offloading Point:</strong> {{$product->offloading_point ? $product->offloading_point->name : ""}}       
                                        </td>
                                        <td class="text-left" style="font-size:15px">
                                            {{ Str::limit($product->description,100) }}
                                        </td>
                                        <td class="qty" style="font-size:15px"> {{$product->weight}}</td>
                                        <td class="unit" style="font-size:15px">${{number_format($product->rate,2)}}</td>
                                        <td class="unit" style="font-size:15px">${{number_format($product->freight,2)}}</td>
                                    </tr>

                                    @endforeach


                                </tbody>
                                <tfoot >
                                    <tr >
                                        <td colspan="2"></td>
                                        <td colspan="3">SUBTOTAL</td>
                                        <td>${{number_format($quotation->subtotal,2)}}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"></td>
                                        <td colspan="3">TAX {{$company->vat}}%</td>
                                          @php
                                             $value = ($company->vat/100)*$quotation->subtotal;
                                          @endphp
                                        <td>${{number_format($value,2)}}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"></td>
                                        <td colspan="3">GRAND TOTAL</td>
                                        <td>${{number_format($quotation->total,2)}}</td>
                                    </tr>
                                </tfoot>
                            </table>
                            <br>
                            {{-- <div class="thanks">Thank YOU!!</div> --}}
                            <div class="notices">
                                <div><strong>MEMO</strong></div>
                                <div class="notice">{{$company->quotation_memo}}.</div>
                            </div>
                            <br>
                            <br>

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
                       <center><footer>{{$company->quotation_footer}}</footer></center> 
                    </div>
                    <!--DO NOT DELETE THIS div. IT is responsible for showing footer always at the bottom-->
                    <div></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
