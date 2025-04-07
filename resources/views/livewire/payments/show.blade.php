
<div>
    <div class="row mt-30">
    <div class="col-md-10 col-md-offset-1">
        <!-- /.row -->
        <ul class="nav nav-tabs nav-justified" role="tablist">
            <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Payment Details</a></li>
            <li role="presentation" ><a href="#documents" aria-controls="documents" role="tab" data-toggle="tab">Documents</a></li>
        </ul>
        <div class="tab-content bg-white p-15">
            <div role="tabpanel" class="tab-pane active" id="basic">
                <table class="table table-striped">

                    <tbody class="text-center line-height-35 ">
                        <tr>
                            <th class="w-10 text-center line-height-35">Date</th>
                            <td class="w-20 line-height-35"> {{Carbon\Carbon::parse($payment->date)->format('d M Y')}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">RecordedBy</th>
                            <td class="w-20 line-height-35"> {{$payment->user ? $payment->user->name : ""}} {{$payment->user ? $payment->user->surname : ""}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Description</th>
                            <td class="w-20 line-height-35"> 
                                @if ($payment->invoice)
                                {{$payment->customer ? $payment->customer->name : ""}} Payment for invoice# <a href="{{route('invoices.show',$payment->invoice->id)}}" style="color: blue">{{$payment->invoice ? $payment->invoice->invoice_number : ""}}</a> <br>
                            @elseif ($payment->invoice_payment)
                                {{$payment->customer ? $payment->customer->name : ""}} Setoff payment for invoice# <a href="{{ route('invoices.show',$payment->invoice_payment->invoice_id) }}" style="color:blue">{{$payment->invoice_payment->invoice ? $payment->invoice_payment->invoice->invoice_number : ""}}</a>
                            @elseif($payment->bill)
                                Bill# <a href="{{route('bills.show',$payment->bill->id)}}" style="color: blue">{{$payment->bill ? $payment->bill->bill_number : ""}}</a> Payment to {{$payment->vendor ? $payment->vendor->name : ""}} <br>
                            @elseif ($payment->customer && !$payment->invoice_payment)
                                @php
                                    $customer_account = App\Models\Account::find($payment->customer_account_id);
                                @endphp
                                <a href="{{ route('customers.show',$payment->customer->id) }}" style="color:blue">{{$payment->customer ? $payment->customer->name : ""}}</a> payment into
                                @if ($customer_account)
                                    <a href="{{ route('accounts.show',$customer_account->id) }}" style="color: blue">{{ $customer_account->name }}</a> customer account
                                @endif
                                <br>
                            @elseif ($payment->recovery)
                            Recovery# <a href="{{ route('recoveries.show',$payment->recovery->id) }}" style="color:blue">{{$payment->recovery ? $payment->recovery->recovery_number : ""}}</a> payment. <br>
                            @endif
                           
                            @if ($payment->description)
                                {{$payment->description}}
                            @endif
                            </td>
                        </tr> 
                        @if ($payment->mode_of_payment)
                            <tr>
                                <th class="w-10 text-center line-height-35">Mode Of Payment</th>
                                <td class="w-20 line-height-35">{{$payment->mode_of_payment}}</td>
                            </tr>
                            @if ($payment->mode_of_payment == "OTHER")
                                <tr>
                                    <th class="w-10 text-center line-height-35">Other Explanation</th>
                                    <td class="w-20 line-height-35">{{$payment->specify_other}}</td>
                                </tr>
                            @endif
                            @if ($mode_of_payment == "Bank Payment" || $mode_of_payment == "Credit Card" || $mode_of_payment == "Paypal")
                            <tr>
                                <th class="w-10 text-center line-height-35">Bank Account</th>
                                <td class="w-20 line-height-35">{{$payment->bank_account ? $payment->bank_account->name : ""}} {{$payment->bank_account ? $payment->bank_account->account_number : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Reference Number</th>
                                <td class="w-20 line-height-35">{{$payment->reference_code }}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Proof Of Payment</th>
                                <td class="w-20 line-height-35"><a href="{{ asset('myfiles/documents/'.$payment->pop) }}"><i class="fas fa-file"></i>{{ $payment->pop }}</a></td>
                            </tr>
                           
                            @endif
                        @endif
                          
                          
                        <tr>
                            <th class="w-10 text-center line-height-35">Account</th>
                            <td class="w-20 line-height-35">{{$payment->account ? $payment->account->name : ""}}</td>
                        </tr>
                        <tr>
                            <th class="w-10 text-center line-height-35">Category</th>
                            <td class="w-20 line-height-35">
                                @if ($payment->transaction_category)
                                {{$payment->transaction_category}}
                                @elseif($payment->invoice)
                                Invoice# <a href="{{route('invoices.show',$payment->invoice->id)}}" style="color: blue">{{$payment->invoice ? $payment->invoice->invoice_number : ""}}</a> | Payment from {{$payment->customer ? $payment->customer->name : ""}}<br>
                                @elseif($payment->bill)
                                Bill# <a href="{{route('bills.show',$payment->bill->id)}}" style="color: blue">{{$payment->bill ? $payment->bill->bill_number : ""}}</a> Payment to {{$payment->vendor ? $payment->vendor->name : ""}} <br>
                                @endif
                            </td>
                        </tr>
                          
                            <tr>
                                <th class="w-10 text-center line-height-35">Currency</th>
                                <td class="w-20 line-height-35">{{$payment->currency ? $payment->currency->name : ""}}</td>
                            </tr>
                            @if ($payment->exchange_rate)
                            <tr>
                                <th class="w-10 text-center line-height-35">Currency conversion:</th>
                                <td class="w-20 line-height-35"> {{ Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->name : "" }} {{ Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->symbol : "" }}{{ number_format($payment->exchange_amount,2)}} @ {{$payment->exchange_rate}} </td>
                            </tr> 
                            @endif
                            <tr>
                                <th class="w-10 text-center line-height-35">Amount</th>
                                <td class="w-20 line-height-35">
                                    @if ($payment->amount)
                                    {{ $payment->currency ? $payment->currency->symbol : "" }}{{ number_format($payment->amount,2)}}
                                    @endif
                                </td>
                            </tr>
                            @if ($payment->exchange_amount)
                            <tr>
                                <th class="w-10 text-center line-height-35">Total in {{ Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->name : "" }}</th>
                                <td class="w-20 line-height-35">{{ Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->symbol : "" }}{{number_format($payment->exchange_amount,2)}}</td>
                            </tr> 
                            @endif
                            @if ($payment->mode_of_payment == "CASH")
                            <tr>
                                <th class="w-10 text-center line-height-35">Denomitions</th>
                                <td class="w-20 line-height-35">@if ($payment->denominations->count()>0)
                                    @foreach ($payment->denominations as $denomination)
                                    {{ $denomination->quantity }} {{ $payment->currency ? $payment->currency->symbol : "" }}{{ $denomination->denomination }} Note(s) ,
                                    @endforeach        

                                @endif
                            </td>
                            </tr>
                            @endif
                            
                           
                            @if ($payment->notes)
                            <tr>
                                <th class="w-10 text-center line-height-35">Notes</th>
                                <td class="w-20 line-height-35">
                                   
                                    {{$payment->notes}}
                                    
                                </td>
                            </tr>
                            @endif
                    </tbody>
                </table>
              
            </div>
            <div role="tabpanel" class="tab-pane" id="documents">
                @livewire('documents.index', ['id' => $payment->id,'category' =>'payment'])
              </div>

              <div class="row">
                <div class="col-md-12">
                    <div class="btn-group pull-right mt-10" >
                       <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                    </div>
                </div>
                </div>

            <!-- /.section-title -->
        </div>
    </div>
    </div>
</div>
