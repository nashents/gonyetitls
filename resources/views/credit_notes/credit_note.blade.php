<!doctype html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
 
  <title>Credit Note</title>
 

@include('includes.css')

</head>
<body>

<div class="container">
    <div class="card">
        <div class="card-body">
                <div class="invoice overflow-auto">
                    <div style="min-width: 500px" >
                        <header>
                            <div class="row" style="margin-top:-50px;">
                                <div class="col">
    								<img src="{{asset('images/uploads/'.$company->logo)}}" width="150" >
                                </div>
                                <div class="col company-details" style="margin-top:-90px;">
                                    <h4 class="name" >
                                        <a target="_blank" href="javascript:;" style="color:  {{Auth::user()->employee->company ? Auth::user()->employee->company->color : Auth::user()->company->color }}">
                                            {{$company->name}}
                                        </a>
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
                        </header>
                        <main>
                            <div class="row contacts" >
                                <div class="col invoice-to" >
                                    <div class="text-gray-light">BILL TO:</div>
                                    <h4 class="to"> <strong>Customer Name: </strong> {{$credit_note->customer ? $credit_note->customer->name : ""}}</h4>
                                    
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
                           
                                <div class="col invoice-details"  style="margin-top:-120px;">
                                    @if (Auth::user()->employee->company->fiscalize == TRUE)
                                    <div class="date" style="padding-bottom: 3px"> <strong>Document No.:</strong> {{$credit_note->credit_note_number}}</div>
                                    @else   
                                    <div class="date" style="padding-bottom: 3px"> <strong>Credit Note No.:</strong> {{$credit_note->credit_note_number}}</div>
                                    @endif
                                    <div class="date" style="padding-bottom: 3px"> <strong>Reference No.:</strong> {{$credit_note->invoice ? $credit_note->invoice->invoice_number : ""}}</div>
                                    @if ($credit_note->subheading)
                                    <div class="date" style="padding-bottom: 3px"> {{$credit_note->subheading}}</div>
                                    @endif
                                    <div class="date" style="padding-bottom: 3px"><strong>Date:</strong> {{$credit_note->date}}</div>
                                    @if ($credit_note->expiry)
                                    <div class="date" style="padding-bottom: 3px"><strong>Payment Due:</strong> {{$credit_note->expiry}}</div>
                                    @endif
                                    <div class="date" style="padding-bottom: 3px"><strong>Currency:</strong> {{$credit_note->currency ? $credit_note->currency->name : ""}}</div>
                                    
                                </div>
                               
                            </div>
                            <table style="margin-top: 50px;">
                                <thead>
                                    <tr class="text-center">
                                        <th class="text-center"> <strong>HS Code</strong></th>
                                        <th class="text-center"> <strong>Description</strong></th>
                                        <th class="text-center"><strong>Qty</strong></th>
                                        <th class="text-center"><strong>Price</strong></th>
                                        <th class="text-center"><strong>Total</strong><small>(Excl)</small></th>
                                        <th class="text-center"><strong>VAT AMT</strong></th>
                                        <th class="text-center"><strong>Total</strong><small>(Incl)</small></th>
                                    </tr>
                                </thead>
                                <tbody>
                  
                                    @foreach ($credit_note_items as $credit_note_item)
                                         <tr>
                                             @php
                                                $tax = App\Models\Account::find($credit_note_item->tax_id);
                                            @endphp
                                            <td class="unit text-center"> 
                                                @if ($tax && $tax->hs_code)
                                                    {{$tax->hs_code}}
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($credit_note_item->product)
                                                <strong>{{$credit_note_item->product ? $credit_note_item->product->name : ""}}</strong>  <br>
                                                {{$credit_note_item->product ? $credit_note_item->product->description : ""}}
                                                @elseif ($credit_note_item->trip)
                                                {{$credit_note_item->description ? $credit_note_item->description : $credit_note_item->trip_details}}
                                                @endif
                                                   
                                            </td>
                                            <td class="unit text-center"> {{$credit_note_item->qty}}</td>
                                            <td class="unit text-center">
                                                @if ($credit_note_item->amount)
                                                {{ $credit_note->currency ? $credit_note->currency->symbol : "" }}{{number_format($credit_note_item->amount,2)}}        
                                                @endif
                                            </td>
                                            <td class="unit text-center">
                                                @if ($credit_note_item->subtotal)
                                                    {{ $credit_note->currency ? $credit_note->currency->symbol : "" }}{{number_format($credit_note_item->subtotal,2)}}
                                                @endif
                                            </td>
        
                                            <td class="unit text-center">
                                                @if (isset($credit_note->tax_amount) && $credit_note->tax_amount > 0)
                                                    {{ $credit_note->currency ? $credit_note->currency->symbol : "" }}{{number_format($credit_note_item->tax_amount,2)}}
                                                @else
                                                    {{ $credit_note->currency ? $credit_note->currency->symbol : "" }}{{number_format(0,2)}}
                                                @endif
                                            </td>
                                          
                                            <td class="unit text-center">
                                                @if (isset($credit_note_item->subtotal_incl))
                                                    {{ $credit_note->currency ? $credit_note->currency->symbol : "" }}{{number_format($credit_note_item->subtotal_incl,2)}}
                                                @endif
                                            </td>
                                        </tr>
        
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4"></td>
                                        <td colspan="2">SUB-TOTAL {{ $credit_note->currency ? $credit_note->currency->name : "" }} <small>(Excl)</small></td>
                                        <td>  
                                            @if (isset($credit_note->invoice_items))
                                                {{ $credit_note->currency ? $credit_note->currency->symbol : "" }}{{number_format($credit_note->invoice_items->where('subtotal','!=',Null)->where('subtotal','!=','')->sum('subtotal'),2)}}  
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="4"></td>
                                        <td colspan="2">VAT TOTAL</td>
                                         
                                        <td>
                                            @if (isset($credit_note->tax_amount) && $credit_note->tax_amount > 0) 
                                            {{ $credit_note->currency ? $credit_note->currency->symbol : "" }}{{number_format($credit_note->tax_amount,2)}}
                                            @else
                                            {{ $credit_note->currency ? $credit_note->currency->symbol : "" }}{{number_format(0,2)}}
                                            @endif
                                        </td>
                                    </tr>
                                     @if ($credit_note->discount)
                                        <tr>
                                            <td colspan="4"></td>
                                            <td colspan="2">DISCOUNT {{$credit_note->discount->description}}</td>
                                            
                                            <td>
                                                @if ($credit_note->discount->unit == "currency")
                                                    {{$credit_note->currency ? $credit_note->currency->symbol : ""}}{{number_format($credit_note->discount->amount ? $credit_note->discount->amount : 0,2)}}
                                                    @elseif($credit_note->discount->unit == "percentage")
                                                    {{number_format($credit_note->discount->amount ? $credit_note->discount->amount : 0,2)}} %
                                                    @endif 
                                            </td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td colspan="4"></td>
                                        <td colspan="2">CREDIT NOTE TOTAL {{ $credit_note->currency ? $credit_note->currency->name : "" }} </td>
                                        <td>
                                            @if ($credit_note->total)
                                                  {{ $credit_note->currency ? $credit_note->currency->symbol : "" }}{{number_format($credit_note->total,2)}}
                                            @endif
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                            <br>
                            @if ($credit_note->reason)
                            <div class="notices">
                                <div><strong>REASON FOR CREDIT: </strong></div>
                                <div class="notice">{{$credit_note->reason}}</div>
                            </div>
                            @endif
                            @if ($credit_note->memo)
                            <div class="notices">
                                <div><strong>Terms & Conditions</strong></div>
                                <div class="notice">{{$credit_note->memo}}</div>
                            </div>
                            @endif
                           
                            <br>
                            <br>
                        </main>
                     
                    </div>
                    <center> <footer style="   position: fixed; 
                        bottom: 0px; 
                        left: 0px; 
                        right: 0px;
                        height: 50px;">{{$credit_note->footer}}</footer></center>  
                    <!--DO NOT DELETE THIS div. IT is responsible for showing footer always at the bottom-->
                    <div> </div>
                    {{-- <center><footer>{{$company->quotation_footer}}</footer></center>  --}}
                </div>
           
        </div>
    </div>
</div>

</body>
</html>