<div>
    <div id="invoice">
        <x-loading/>
        <div class="toolbar hidden-print">
            <div class="text-end">
                <button type="button" onclick="goBack()" class="btn btn-default border-primary btn-wide btn-rounded"><i class="fa fa-arrow-left" style="color:black"></i> Back</button>
                {{-- <a href="#" wire:click="sendEmail({{$invoice->id}})" class="btn btn-default border-primary btn-wide btn-rounded"><i class="fa fa-envelope" style="color:red"></i> Send</a> --}}
                <a href="{{route('invoices.print',$invoice->id)}}" class="btn btn-default border-primary btn-wide btn-rounded"><i class="fa fa-print" style="color:black"></i> Print</a>
                <a href="{{route('invoices.pdf', $invoice->id)}}" class="btn btn-default border-primary btn-wide btn-rounded"><i class="fa fa-file-pdf-o" style="color:red"></i> Export as PDF</a>
            </div>
            <hr>
        </div>
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
                  
                    <div style="padding-top: 25px; padding-bottom:15px">
                        <center><h2>{{ $invoice->fiscalize == TRUE ? " FISCAL TAX INVOICE" : "INVOICE"}} </h2>  </center>
                    </div>
                       
                               
                
                  
                    
                </header>
                <main>
                    <div class="row contacts">
                        <div class="col invoice-to" >
                            <div class="text-gray-light">BILL TO:</div>
                            <h6 class="to"> <strong>Customer Name: </strong> {{$invoice->customer ? $invoice->customer->name : ""}}</h6>
                          
                            <div class="address" >
                                <strong>Customer Address: </strong>
                                @if (isset($invoice->customer->street_address) || isset($invoice->customer->suburb))
                                 {{$invoice->customer ? $invoice->customer->street_address : ""}} {{$invoice->customer ? $invoice->customer->suburb : ""}}, <br>  
                                @endif
                                 {{$invoice->customer ? $invoice->customer->city : ""}} {{$invoice->customer ? $invoice->customer->country : ""}}
                            </div>
                         
                            @if (isset($invoice->customer->email))
                            <div class="email">
                                <strong>Customer Email: </strong><a href="mailto:{{$invoice->customer->email}}">{{$invoice->customer->email}}</a>
                            </div>
                            @endif
                            @if (isset($invoice->customer->email))
                            <div class="email">
                                <strong>Customer Phonenumber: </strong>{{$invoice->customer->phonenumber}}
                            </div>
                            @endif
                            <div class="email">
                             
                                <strong>Customer VAT No:</strong> {{$invoice->customer ? $invoice->customer->vat_number : ""}}
                              
                            </div>
                            <div class="email">
                              
                                <strong>Customer TIN No:</strong> {{$invoice->customer ? $invoice->customer->tin_number : ""}}
                               
                            </div>
                        </div>
                        <div class="col invoice-details">
                            @if ($invoice->fiscalize == TRUE)
                            <div class="date" style="padding-bottom: 3px"> <strong>Document No.:</strong> {{$invoice->invoice_number}}</div>
                            @else   
                            <div class="date" style="padding-bottom: 3px"> <strong>Invoice No.:</strong> {{$invoice->invoice_number}}</div>
                            @endif
                            @if ($invoice->sales_order_number)
                            <div class="date" style="padding-bottom: 3px"> <strong>S.O No.:</strong> {{$invoice->sales_order_number}}</div>
                            @endif
                            @if ($invoice->purchase_order_number)
                            <div class="date" style="padding-bottom: 3px"> <strong>P.O No.:</strong> {{$invoice->purchase_order_number}}</div>
                            @endif
                            @if ($invoice->pat_number)
                            <div class="date" style="padding-bottom: 3px"> <strong>PAT No.:</strong> {{$invoice->pat_number}}</div>
                            @endif
                            <div class="date" style="padding-bottom: 3px"><strong>Invoice Date:</strong> {{$invoice->date}}</div>
                            @if ($invoice->expiry)
                            <div class="date" style="padding-bottom: 3px"><strong>Payment Due:</strong> {{$invoice->expiry}}</div>
                            @endif
                            <div class="date" style="padding-bottom: 3px"><strong>Currency:</strong> {{$invoice->currency ? $invoice->currency->name : ""}}</div>
                            
                        </div>
                    </div>

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
                                        @if ($invoice_item->product)
                                        <strong>{{$invoice_item->product ? $invoice_item->product->name : ""}}</strong>  <br>
                                        {{$invoice_item->product ? $invoice_item->product->description : ""}}
                                        @elseif ($invoice_item->trip)
                                        {{$invoice_item->description ? $invoice_item->description : $invoice_item->trip_details}}
                                        @endif
                                           
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
                                    @if (isset($invoice->invoice_items))
                                        {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice->invoice_items->where('subtotal','!=',Null)->where('subtotal','!=','')->sum('subtotal'),2)}}  
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
                            @if ($invoice->discount)
                                <tr>
                                    <td colspan="3"></td>
                                    <td colspan="2">DISCOUNT {{$invoice->discount->description}}</td>
                                    
                                    <td>
                                         @if ($invoice->discount->unit == "currency")
                                            {{$invoice->currency ? $invoice->currency->symbol : ""}}{{number_format($invoice->discount->amount ? $invoice->discount->amount : 0,2)}}
                                            @elseif($invoice->discount->unit == "percentage")
                                            {{number_format($invoice->discount->amount ? $invoice->discount->amount : 0,2)}} %
                                            @endif 
                                    </td>
                                </tr>
                            @endif
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
             
                   
                    @if ($invoice->memo)
                    <div class="notices">
                        <div><strong>Notes / Terms & Conditions</strong></div>
                        <div class="notice">{{$invoice->memo}}</div>
                    </div>
                    @endif
                  
                    <br>
                    @if ($invoice->bank_accounts->count()>0)
                            <table>
                                <thead>
                                    <tr>
                                        <th  style="background-color: white;"><strong>Banking Details</strong></th>      
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        @foreach ($invoice->bank_accounts as $bank_account)
                                        <td class="text-left "  scope="col" >
                                            @if ($bank_account->name )
                                            <strong>Bank: </strong>{{ $bank_account->name }} <br> 
                                            @endif
                                            @if ($bank_account->branch)
                                            <strong>Branch: </strong>{{ $bank_account->branch }} <br>
                                            @endif
                                            @if ($bank_account->branch_code)
                                            <strong>Branch Code: </strong> {{ $bank_account->branch_code }} <br>
                                            @endif
                                            @if ($bank_account->swift_code)
                                            <strong>Swift Code: </strong> {{ $bank_account->swift_code }} <br>
                                            @endif
                                            @if ($bank_account->type)
                                            <strong>Account Type: </strong> {{ $bank_account->type }} <br> 
                                            @endif
                                            @if ($bank_account->account_name)
                                            <strong>Account Name: </strong> {{ $bank_account->account_name }} <br>
                                            @endif
                                           @if ($bank_account->account_number)
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
                    <footer style=" bottom: 0px; left: 0px; right: 0px; ">
                        {{$invoice->footer}}
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
