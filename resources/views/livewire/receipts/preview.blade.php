<div>
    <div id="print-area">
    <div id="invoice">
        <div class="toolbar hidden-print">
            <div class="text-end">
                <button type="button" onclick="goBack()" class="btn btn-default border-primary btn-wide btn-rounded"><i class="fa fa-arrow-left" style="color: black"></i> Back</button>
                <a href="{{route('receipts.print',$receipt->id)}}" class="btn btn-default border-primary btn-wide btn-rounded"><i class="fas fa-print" style="color: black"></i> Print</a>
                {{-- <a href="{{route('receipts.pdf', $receipt->id)}}" class="btn btn-default border-primary btn-wide btn-rounded"><i class="fa fa-file-pdf-o" style="color: red"></i> Export as PDF</a> --}}
            </div>
            <hr>
        </div>
        <div class="invoice overflow-auto">
            <div style="min-width: 600px">
                <header>
                    <div class="row">
                        <div class="col">
                            <a href="#"><img src="{{asset('images/uploads/'.$company->logo)}}" width="150" alt=""></a>
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
                        <center><h2>Receipt</h2>  </center>
                    </div>
                </header>
                <main>
                    <div class="row contacts">
                        <div class="col invoice-to">
                            <div class="text-gray-light">Receipt For:</div>
                            <h4 class="to">{{$receipt->payment->customer ? $receipt->payment->customer->name : ""}}</h4>
                          
                            <div class="address" >
                                @if (isset($receipt->payment->customer->street_address) || isset($receipt->payment->customer->suburb))
                                 {{$receipt->payment->customer ? $receipt->payment->customer->street_address : ""}} {{$receipt->payment->customer ? $receipt->payment->customer->suburb : ""}}, <br>  
                                @endif
                                 {{$receipt->payment->customer ? $receipt->payment->customer->city : ""}} {{$receipt->payment->customer ? $receipt->payment->customer->country : ""}}
                            </div>
                            
                            @if (isset($receipt->payment->customer->email))
                            <div class="email"><a href="mailto:{{$receipt->payment->customer->email}}">{{$receipt->payment->customer->email}}</a></div>
                            @endif
                            
                            <div class="email">
                                @if (isset($receipt->payment->customer->vat_number))
                                    VAT No.: {{$receipt->payment->customer ? $receipt->payment->customer->vat_number : ""}}
                                @endif
                            </div>
                            <div class="email">
                                @if (isset($receipt->payment->customer->tin_number))
                                    TIN.: {{$receipt->payment->customer ? $receipt->payment->customer->tin_number : ""}}
                                @endif
                            </div>
                        </div>
                        <div class="col invoice-details">
                            <div class="date" style="padding-bottom: 3px"> <strong>Receipt Number: </strong>{{$receipt->receipt_number}}</div>
                            <div class="date" style="padding-bottom: 3px"> <strong>Payment Date:</strong> {{$receipt->date}}</div>
                            <div class="date" style="padding-bottom: 3px"> <strong>Currency:</strong> {{$receipt->currency ? $receipt->currency->name : ""}}</div>
                        </div>
                    </div>
                
                    <table>
                        <thead>
                            <tr>
                                <tr class="text-center">
                                    <th class="text-center"> <strong>Description</strong></th>
                                    <th class="text-center"><strong>Qty</strong></th>
                                    <th class="text-center"><strong>Price</strong></th>
                                    <th class="text-center"><strong>Total</strong><small>(Excl)</small></th>
                                    <th class="text-center"><strong>VAT AMT</strong></th>
                                    <th class="text-center"><strong>Total</strong><small>(Incl)</small></th>
                                </tr>
                            </tr>
                        </thead>
     
                        @if ($receipt->invoice)
                        <tbody>
                            @foreach ($invoice_items as $item)
                          
                                 <tr>
                                    <td class="text-center">
                                      @if ($item->description)
                                      {{$item->description}}
                                      @elseif ($item->trip_details)
                                      {{$item->trip_details}}
                                      @elseif ($item->inventory && $item->inventory->product)
                                      {{$item->inventory->product->name}}  {{$item->inventory->product->description}}
                                      @endif
                                  </td>
                                    <td class="qty text-center"> {{$item->qty}}</td>
                                    <td class="unit text-center">  {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($item->amount,2)}}</td>
                                    <td class="unit text-center">  {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($item->subtotal,2)}}</td>
                                    <td class="unit text-center">  {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($item->tax_amount,2)}}</td>
                                    <td class="unit text-center">  {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($item->subtotal_incl,2)}}</td>
                                </tr>

                            @endforeach
                           
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3"></td>
                                <td colspan="2">Sub Total {{ $invoice->currency ? $invoice->currency->name : "" }} <small>(Excl)</small></td>
                                <td class="unit text-center">  
                                    @if (isset($invoice->invoice_items))
                                        {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice->invoice_items->where('subtotal','!=',Null)->where('subtotal','!=','')->sum('subtotal'),2)}}  
                                    @endif
                                </td>
                            </tr>
                          
                            <tr>
                                <td colspan="3"></td>
                                <td colspan="2">VAT Total</td>
                                 
                                <td class="unit text-center">
                                    @if (isset($invoice->tax_amount) && $invoice->tax_amount > 0) 
                                        {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice->tax_amount,2)}}
                                    @else
                                        {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format(0,2)}}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3"></td>
                                <td colspan="2">Grand Total {{ $invoice->currency ? $invoice->currency->name : "" }} </td>
                                <td class="unit text-center">
                                    @if ($invoice->total)
                                          {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($invoice->total,2)}}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3"></td>
                                <td colspan="2">Amount Paid {{ $invoice->currency ? $invoice->currency->name : "" }}</td>
                                <td class="unit text-center">  {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($receipt->amount,2)}}</td>
                            </tr>
                            <tr>
                                <td colspan="3"></td>
                                <td colspan="2">Balance {{ $invoice->currency ? $invoice->currency->name : "" }}</td>
                                <td class="unit text-center">  {{ $invoice->currency ? $invoice->currency->symbol : "" }}{{number_format($receipt->balance,2)}}</td>
                            </tr>
                        </tfoot>
                        @elseif ($receipt->sale)
                        <tbody>
                            @foreach ($sale_items as $item)
                          
                                 <tr>
                                    <td class="text-center">
                                      @if ($item->inventory->product)
                                      {{$item->inventory->product->name}}  {{$item->inventory->product->description}}
                                      @endif
                                  </td>
                                    <td class="qty text-center"> {{$item->qty}}</td>
                                    <td class="unit text-center">  {{ $sale->currency ? $sale->currency->symbol : "" }}{{number_format($item->amount,2)}}</td>
                                    <td class="unit text-center">  {{ $sale->currency ? $sale->currency->symbol : "" }}{{number_format($item->subtotal,2)}}</td>
                                    <td class="unit text-center">  {{ $sale->currency ? $sale->currency->symbol : "" }}{{number_format($item->tax_amount,2)}}</td>
                                    <td class="unit text-center">  {{ $sale->currency ? $sale->currency->symbol : "" }}{{number_format($item->subtotal_incl,2)}}</td>
                                </tr>

                            @endforeach
                           
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3"></td>
                                <td colspan="2">Sub Total {{ $sale->currency ? $sale->currency->name : "" }} <small>(Excl)</small></td>
                                <td>  
                                    @if (isset($sale->sale_items))
                                        {{ $sale->currency ? $sale->currency->symbol : "" }}{{number_format($sale->sale_items->where('subtotal','!=',Null)->where('subtotal','!=','')->sum('subtotal'),2)}}  
                                    @endif
                                </td>
                            </tr>
                          
                            <tr>
                                <td colspan="3"></td>
                                <td colspan="2">VAT Total</td>
                                 
                                <td>
                                    @if (isset($sale->tax_amount) && $sale->tax_amount > 0) 
                                        {{ $sale->currency ? $sale->currency->symbol : "" }}{{number_format($sale->tax_amount,2)}}
                                    @else
                                        {{ $sale->currency ? $sale->currency->symbol : "" }}{{number_format(0,2)}}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3"></td>
                                <td colspan="2">Grand Total {{ $sale->currency ? $sale->currency->name : "" }} </td>
                                <td>
                                    @if ($sale->total)
                                          {{ $sale->currency ? $sale->currency->symbol : "" }}{{number_format($sale->total,2)}}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3"></td>
                                <td colspan="2">Amount Paid {{ $sale->currency ? $sale->currency->name : "" }}</td>
                                <td>  {{ $sale->currency ? $sale->currency->symbol : "" }}{{number_format($receipt->amount,2)}}</td>
                            </tr>
                            <tr>
                                <td colspan="3"></td>
                                <td colspan="2">Balance {{ $sale->currency ? $sale->currency->name : "" }}</td>
                                <td>  {{ $sale->currency ? $sale->currency->symbol : "" }}{{number_format($receipt->balance,2)}}</td>
                            </tr>
                        </tfoot>
                        @else
                        <tbody>
                            <tr>
                                <td class="text-center">Customer Deposit</td>
                                <td class="qty text-center">1</td>
                                <td class="unit text-center">{{ $receipt->currency ? $receipt->currency->symbol : "" }}{{number_format($receipt->amount,2)}}</td>
                                <td class="unit text-center">{{ $receipt->currency ? $receipt->currency->symbol : "" }}{{number_format($receipt->amount,2)}}</td>
                                <td class="unit text-center">{{ $receipt->currency ? $receipt->currency->symbol : "" }}{{number_format(0,2)}}</td>
                                <td class="unit text-center">{{ $receipt->currency ? $receipt->currency->symbol : "" }}{{number_format($receipt->amount,2)}}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3"></td>
                                <td colspan="2">Payment Method</td>
                                <td class="unit text-center">{{$receipt->payment ? $receipt->payment->mode_of_payment : ""}}</td>
                            </tr>
                            <tr>
                                <td colspan="3"></td>
                                <td colspan="2">Amount Paid {{ $receipt->currency ? $receipt->currency->name : "" }}</td>
                                <td class="unit text-center">{{ $receipt->currency ? $receipt->currency->symbol : "" }}{{number_format($receipt->amount,2)}}</td>
                            </tr>
                        </tfoot>
                        @endif

            
                      
              
                    </table>
            
                    <br>
                    @if ($receipt->receipt_memo)
                    <div class="notices">
                        <div><strong>Notes / Terms & Conditions</strong></div>
                        <div class="notice">{{$company->receipt_memo}}</div>
                    </div>
                    @endif           
                </main>
              
            <center> 
                <footer style=" bottom: 0px; left: 0px; right: 0px; ">
                    {{$receipt->footer}}
                    <br>
                    <strong style="font-size: 18px;">Powered By</strong> <img src="{{asset('images/logo.png')}}" alt="" style="width: 20%; height:20%">    
                </footer>
            </center>  
           
            </div>
            <!--DO NOT DELETE THIS div. IT is responsible for showing footer always at the bottom-->
            <div></div>
        </div>
    </div>
    </div>
</div>
