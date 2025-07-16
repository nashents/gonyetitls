<div>
    <div class="row mt-30">
    
        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1" >

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Invoice Details</a></li>
                <li role="presentation"><a href="#invoice_items" aria-controls="invoice_items" role="tab" data-toggle="tab">Invoice Items</a></li>
                <li role="presentation"><a href="#payments" aria-controls="payments" role="tab" data-toggle="tab">Payments</a></li>
            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">

                        <tbody class="text-center line-height-35 ">
                            <tr>
                                <th class="w-10 text-center line-height-35">Invoice#</th>
                                <td class="w-20 line-height-35">{{$invoice->invoice_number}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">CreatedBy</th>
                                <td class="w-20 line-height-35">{{$invoice->user ? $invoice->user->name : ""}} {{$invoice->user ? $invoice->user->surname : ""}} </td>
                            </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Customer</th>
                                    <td class="w-20 line-height-35">{{$invoice->customer ? $invoice->customer->name : ""}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Invoice Date</th>
                                    <td class="w-20 line-height-35">{{$invoice->date}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Payment Due</th>
                                    <td class="w-20 line-height-35">{{$invoice->expiry}}</td>
                                </tr>
                              
                                <tr>
                                    <th class="w-10 text-center line-height-35">Currency</th>
                                    <td class="w-20 line-height-35">{{$invoice->currency ? $invoice->currency->name : ""}}</td>
                                </tr>
                                @if ($invoice->exchange_rate)
                                <tr>
                                    <th class="w-10 text-center line-height-35">Currency conversion:</th>
                                    <td class="w-20 line-height-35"> {{ Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->name : "" }} {{ Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->symbol : "" }}{{ number_format($invoice->exchange_amount,2)}} @ {{ $invoice->exchange_rate}} </td>
                                </tr> 
                                @endif
                                <tr>
                                    <th class="w-10 text-center line-height-35">Subtotal</th>
                                    <td class="w-20 line-height-35">
                                        @if ($invoice->subtotal)
                                        {{$invoice->currency ? $invoice->currency->symbol : ""}}{{number_format($invoice->subtotal,2)}}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Tax Amount</th>
                                    <td class="w-20 line-height-35">
                                        {{$invoice->currency ? $invoice->currency->symbol : ""}}{{number_format($invoice->tax_amount ? $invoice->tax_amount : 0,2)}}
                                    </td>
                                </tr>
                                @if ($invoice->discount)
                                    <tr>
                                        <th class="w-10 text-center line-height-35">Discount</th>
                                        <td class="w-20 line-height-35">
                                            {{$invoice->discount->description}}
                                            @if ($invoice->discount->unit == "currency")
                                            {{$invoice->currency ? $invoice->currency->symbol : ""}}{{number_format($invoice->discount->amount ? $invoice->discount->amount : 0,2)}}
                                            @elseif($invoice->discount->unit == "percentage")
                                            {{number_format($invoice->discount->amount ? $invoice->discount->amount : 0,2)}} %
                                            @endif 
                                        </td>
                                    </tr>
                                @endif
                               
                              
                                <tr>
                                    <th class="w-10 text-center line-height-35">Total</th>
                                    <td class="w-20 line-height-35">
                                        @if ($invoice->total)
                                        {{$invoice->currency ? $invoice->currency->symbol : ""}}{{number_format($invoice->total,2)}}
                                        @endif
                                    </td>
                                </tr>
                                @if ($invoice->exchange_amount)
                                <tr>
                                    <th class="w-10 text-center line-height-35">Total in {{ Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->name : "" }}</th>
                                    <td class="w-20 line-height-35">{{ Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->symbol : "" }}{{$invoice->exchange_amount}}</td>
                                </tr> 
                                @endif
                                <tr>
                                    <th class="w-10 text-center line-height-35">Authorization</th>
                                    <td class="w-20 line-height-35"><span class="badge bg-{{($invoice->authorization == 'approved') ? 'success' : (($invoice->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($invoice->authorization == 'approved') ? 'approved' : (($invoice->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                </tr>
                                @if ($invoice->comments)
                                <tr>
                                    <th class="w-10 text-center line-height-35">Authorization Comments</th>
                                    <td class="w-20 line-height-35">{{$invoice->comments}}</td>
                                </tr>
                                @endif
                                @if ($invoice->memo)
                                <tr>
                                    <th class="w-10 text-center line-height-35">Memo</th>
                                    <td class="w-20 line-height-35">{{$invoice->memo}}</td>
                                </tr>   
                                @endif
                                @if ($invoice->footer)
                                <tr>
                                    <th class="w-10 text-center line-height-35">Footer</th>
                                    <td class="w-20 line-height-35">{{$invoice->footer}}</td>
                                </tr>
                                @endif
                        </tbody>
                    </table>
                </div>
                <div role="tabpanel" class="tab-pane" id="invoice_items">
                  @livewire('invoices.invoice-items', ['id' => $invoice->id])
                </div> 
                <div role="tabpanel" class="tab-pane" id="payments">
                    @php
                        $payments = $invoice->payments
                    @endphp
                          <table id="paymentsTable" class="table  table-spaymented table-bordered table-sm table-responsive" cellspacing="0" width="100%" style=" width:100%; height:100%;">
                            <thead >
                                <th class="th-sm">Payment#
                                </th>
                                <th class="th-sm">MOP
                                </th>
                                <th class="th-sm">Ccy
                                </th>
                                <th class="th-sm">Amt
                                </th>
                                <th class="th-sm">Bal
                                </th>
                                <th class="th-sm">Actions
                                </th>

                              </tr>
                            </thead>
                       
                            @if ($payments)
                            <tbody>
                                @forelse ($payments as $payment)
                              <tr>
                                
                                <td>{{ucfirst($payment->payment_number)}}</td>
                                <td>{{$payment->mode_of_payment}}</td>
                                <td>{{$payment->currency ? $payment->currency->name : ""}}</td>
                                <td>
                                    @if ($payment->amount)
                                    {{$payment->currency ? $payment->currency->symbol : ""}}{{number_format($payment->amount,2)}}
                                    @endif
                                </td>
                                <td>
                                   @if ($payment->bill)
                                   {{$payment->currency ? $payment->currency->symbol : ""}}{{number_format($payment->bill->balance,2)}} 
                                   @elseif ($payment->invoice)
                                   {{$payment->currency ? $payment->currency->symbol : ""}}{{number_format($payment->invoice->balance,2)}} 
                                   @elseif ($payment->invoice)
                                   {{$payment->currency ? $payment->currency->symbol : ""}}{{number_format($payment->invoice->balance,2)}} 
                                    @endif
                                </td>
                                 <td class="w-10 line-height-35 table-dropdown">
                                    <div class="dropdown">
                                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fa fa-bars"></i>
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a href="{{route('payments.show', $payment->id)}}"><i class="fas fa-eye color-default"></i>View</a></li>
                                           @if ($payment->receipt)
                                           <li><a href="{{route('receipts.preview',$payment->receipt->id)}}"  ><i class="fas fa-receipt color-primary"></i> Receipt</a></li>
                                           @endif     
                                        </ul>
                                    </div>
                                </td>
                              </tr>
                             @empty
                                  <tr>
                                    <td colspan="6">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Invoice Payments Recorded....
                                        </div>
                                       
                                    </td>
                                  </tr>  
                                @endforelse
                            </tbody>
                            @else
                            <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                            @endif
                          

                          </table>
                  </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="btn-group pull-right mt-10" >
                           <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                        </div>
                    </div>
                    </div>

            </div>
        </div>
        <!-- /.col-md-9 -->
    </div>
   
</div>
