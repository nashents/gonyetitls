<!doctype html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
 
  <title>Quotation Template</title>
 

@include('includes.css')

</head>
<body>

<div class="container">
    <div class="card">
        <div class="card-body">
            <div id="invoice"  style="font-size: 16px">
                <div class="invoice overflow-auto" >
                    <div  style="margin-left: -30px; margin-right:-30px" >
                        <header style="margin-top:-25px; padding-top:-25px; padding-bottom:10px" >
                            <div class="row">
                                <div class="col" style="padding-bottom: 5px">
    								<img src="{{asset('images/uploads/'.$company->logo)}}" width="150" >
                                </div>
                                <div class="col company-details" style="margin-top:-100px;">
                                     <h4 class="name" style="color:  {{$company->color ? $company->color : "#000000" }}" >
                                        {{$company->name}}
                                    </h4>
                                    <div>{{$company->street_address}}, {{$company->suburb}}, {{$company->city}} {{$company->country}}</div>
                                    <div>{{$company->phonenumber}}
                                    </div>
                                    <div>{{$company->email}}</div>
                                </div>
                            </div>
                        </header>
                        <main>
                            <div class="row contacts"  style="margin-bottom: 20px" >
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
                               
                                    <div class="col invoice-details" style="margin-top:-120px;" >
                                        <div class="date" style="padding-bottom: 3px"> <strong>Document No.:</strong> {{$quotation->quotation_number}}</div>
                                        <div class="date" style="padding-bottom: 3px"><strong>Date:</strong> {{$quotation->date}}</div>
                                        <div class="date" style="padding-bottom: 3px"><strong>Valid Until:</strong> {{$quotation->expiry}}</div>
                                        <div class="date" style="padding-bottom: 3px"><strong>Currency:</strong> {{$quotation->currency ? $quotation->currency->name : ""}}</div>
                                    </div>
                                </div>
                           
                                <table>
                                    <thead>
                                        <tr>
                                            <th class="text-left"> <strong>HS Code</strong></th>
                                            <th class="text-left"> <strong>Description</strong></th>
                                            <th class="text-center"> <strong>Qty</strong></th>
                                            <th class="text-center"><strong>Unit Price</th> 
                                            <th class="text-center"><strong>Total(Excl)</strong></th>
                                            <th class="text-center"><strong>VAT Amount</strong></th>
                                            <th class="text-center"><strong>Total(Incl)</strong></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                       
                                        @foreach ($quotation->quotation_items as $item)
                                        <tr>
                                            @php
                                                $tax = App\Models\Account::find($item->tax_id);
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
                       
                    </div>
                    <!--DO NOT DELETE THIS div. IT is responsible for showing footer always at the bottom-->
                    <div> </div>
                    <center><footer style="   position: fixed; 
                        bottom: -60px; 
                        left: 0px; 
                        right: 0px;
                        height: 50px;">{{$quotation->footer}}</footer></center> 
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>