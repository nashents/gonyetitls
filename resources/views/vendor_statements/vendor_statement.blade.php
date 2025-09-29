<!doctype html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
 
  <title>Customer Statement</title>
 

@include('includes.css')

</head>
<body>

<div class="container">
    <div class="card">
        <div class="card-body">
            <div id="invoice"  style="font-size: 16px">
                <div class="invoice overflow-auto">
                    <div style="margin-left: -30px; margin-right:-30px">
                        <header style="margin-top:-25px; padding-bottom:10px">
                            <div class="row">
                                <div class="col" style="margin-top:-15px;">
                                    <a href="javascript:;">
                                                    <img src="{{asset('images/uploads/'.$company->logo)}}" width="150" alt="">
                                                </a>
                                               
                                </div>
                                <div class="col company-details" style="margin-top:-120px;">
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
                                    <br>
                                    <h3 class="to" >Statement Of Account</h3>
                                    <div style="margin-top:-10px;">{{$selectedType}}</div>
                                    <br>
                                </div>
                            </div>
                        </header>
                        @php
                            $currencies = App\Models\Currency::all();
                            $billed_currencies = App\Models\Bill::where('vendor_id',$vendor->id)->where('authorization','approved')->get()->pluck('currency_id')->toArray();
                        @endphp
                        @foreach ($currencies as $currency)
                            @if (isset($billed_currencies))
                            @if (in_array($currency->id, $billed_currencies))
                
                        @if ($selectedType == "Outstanding Bills")
                        <main>
                            <div class="row contacts">
                                <div class="col invoice-to" >
                                    <div class="text-gray-light">BILL TO:</div>
                                    <h5 class="to">{{$vendor->name}}</h5>
                                    <div class="address" >
                                        {{$vendor->street_address}}
                                        @if ($vendor->suburb)
                                            {{$vendor->suburb ? $vendor->suburb."," : ""}} <br>
                                        @endif 
                                        {{$vendor->city ? $vendor->city."," : ""}} {{$vendor->country}}
                                    </div>
                                    <div class="email"><a href="mailto:{{$vendor->email}}">{{$vendor->email}}</a>
                                    </div>
                                </div>
                                <div class="col invoice-details" style="margin-top:-150px;">
                                    <h3 class="to" >{{$currency->fullname}} {{$currency->name}}</h3>
                                    <div class="date" style="padding-bottom: 3px; padding-top: -10px"> <strong>As of {{date('F j, Y')}}</strong> </div>
                                    @php
                                        $balance = App\Models\Bill::where('vendor_id',$vendor->id)->where('currency_id',$currency->id)
                                        ->where('status', 'Unpaid')
                                        ->where('authorization','approved')
                                        ->orWhere('vendor_id',$vendor->id)->where('currency_id',$currency->id)
                                        ->where('authorization','approved')
                                        ->where('status', 'Partial')
                                        ->get()->sum('balance');
                                    @endphp
        
                                    @if (isset($balance))
                                    <div class="date" style="padding-bottom: 3px" ><strong>Outstanding Balance ({{$currency->name}})</strong> {{$currency->symbol}}{{$balance}}</div>
                                    @endif
                                </div>
                            </div> 
                            <table>
                                <thead>
                                    <tr>
                                        <th class="text-center"><strong>Bill#</strong></th>
                                        <th class="text-center"><strong>Bill Date</strong></th>
                                        <th class="text-center"><strong>Due Date</strong></th>
                                        <th class="text-center"><strong>Currency</strong></th>
                                        <th class="text-center"><strong>Total</strong></th>
                                        <th class="text-center"><strong>Paid</strong></th>
                                        <th class="text-center"><strong>Due</strong></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($bills as $bill)
                                    @php
                                    $expiry = $bill->expiry;
                                    $now = new DateTime();
                                    $expiry_date = new DateTime($expiry);
                                    @endphp
                                         <tr>
                                            <td class="text-center"> <a href="{{ route('bills.show',$bill->id) }}">{{ $bill->bill_number}}</a> </td>
                                            <td class="text-center">{{ date('F j, Y', strtotime($bill->date)) }} </td>
                                            <td class="text-center">{{ date('F j, Y', strtotime($bill->expiry)) }}
                                                 @if ($now >= $expiry_date)
                                                Overdue
                                            @endif
                                            </td>
                                            <td class="text-center"> {{$bill->currency ? $bill->currency->name : ""}}</td>
                                            <td class="text-center">
                                                @if ($bill->total)
                                                {{ $bill->currency ? $bill->currency->symbol : "" }}{{number_format($bill->total,2)}}        
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($bill->payments)
                                                {{ $bill->currency ? $bill->currency->symbol : "" }}{{number_format($bill->payments->sum('amount'),2)}}
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($bill->balance)
                                                {{ $bill->currency ? $bill->currency->symbol : "" }}{{number_format($bill->balance,2)}}        
                                                @endif
                                            </td>
                                        </tr>
        
                                    @endforeach
                                   
                                </tbody>
                                <tfoot>
                                    @php
                                        $balance = App\Models\Bill::where('vendor_id',$vendor->id)->where('currency_id',$currency->id)
                                        ->where('status', 'Unpaid')
                                        ->where('authorization','approved')
                                        ->orWhere('vendor_id',$vendor->id)->where('currency_id',$currency->id)
                                        ->where('status', 'Partial')
                                        ->where('authorization','approved')
                                        ->get()->sum('balance');
                                    @endphp
                                 @if (isset($balance))
                                 <tr>
                                     <td colspan="3" ></td>
                                     <td colspan="3" class="text-center">Outstanding Balance ({{$currency->name}})</td>
                                     <td class="text-center">  {{$currency->symbol}}{{number_format($balance,2)}}</td>
                                 </tr>
                                 @endif
                                   
                                  
                                </tfoot>
                            </table>
                           
                        </main>
                        @elseif ($selectedType == "Account Activity")
                        <main>
                            <div class="row contacts">
                                <div class="col invoice-to" >
                                    <div class="text-gray-light">BILL TO:</div>
                                    <h4 class="to">{{$vendor->name}}</h4>
                                    <div class="address" >
                                        {{$vendor->street_address}}
                                        @if ($vendor->suburb)
                                            {{$vendor->suburb ? $vendor->suburb."," : ""}} <br>
                                        @endif 
                                        {{$vendor->city ? $vendor->city."," : ""}} {{$vendor->country}}
                                    </div>
                                    <div class="email"><a href="mailto:{{$vendor->email}}">{{$vendor->email}}</a>
                                    </div>
                                </div>
                                <div class="col invoice-details">
                                    <h5 class="to" >{{$currency->fullname}} {{$currency->name}}</h5>
                                    <div class="date" style="padding-bottom: 3px"> <strong>From: </strong>{{ date('F j, Y', strtotime($from)) }}</div>
                                    <div class="date" style="padding-bottom: 3px"> <strong>To: </strong>{{ date('F j, Y', strtotime($to)) }}</div>
                                    <hr>
                                               @php
                                                // $from = new DateTime($from);
                                                // $to = new DateTime($to);
                                                $opening_balance = App\Models\Bill::where('date','<=',$from)
                                                                        ->where('authorization','approved')
                                                                        ->where('vendor_id',$selectedCustomer)
                                                                        ->where('currency_id', $currency->id)
                                                                        ->whereRaw('accrual_balance REGEXP "^-?[0-9]+(\.[0-9]+)?$"')
                                                                        ->where('accrual_balance', function ($query) {
                                                                            $query->selectRaw('MAX(CAST(accrual_balance AS DECIMAL(10,2)))')->from('bills');
                                                                        })
                                                                        ->first();
                                                                    
                                               $closing_balance = App\Models\Bill::where('date','<=',$to)
                                                                        ->where('authorization','approved')
                                                                        ->where('vendor_id',$vendor->id)
                                                                        ->where('currency_id', $currency->id)
                                                                        ->whereRaw('balance REGEXP "^-?[0-9]+(\.[0-9]+)?$"')
                                                                        ->get()->sum('balance');
        
                                               $billed = App\Models\Bill::where('vendor_id',$vendor->id)->where('authorization','approved')->where('currency_id',$currency->id)->where('date','>=',$from)->where('date','<=',$to)->whereRaw('total REGEXP "^-?[0-9]+(\.[0-9]+)?$"')->get()->sum('total');
                                               $paid = App\Models\Payment::where('vendor_id',$vendor->id)->where('currency_id',$currency->id)->whereBetween('date',[$from, $to])->whereRaw('amount REGEXP "^-?[0-9]+(\.[0-9]+)?$"')->get()->sum('amount');
                                            @endphp
                                            
                                                <div class="date" style="padding-bottom: 3px" ><strong>Opening Balance({{$currency->name}}) on {{ date('F j, Y', strtotime($from)) }}</strong> {{$currency->symbol}}{{ number_format($opening_balance ? $opening_balance->accrual : 0,2) }}</div>
                                           
                                            @if (isset($billed))
                                            <div class="date" style="padding-bottom: 3px" ><strong>Billd({{$currency->name}})</strong> {{$currency->symbol}}{{ number_format($billed,2) }}</div>
                                            @endif
                                            @if (isset($paid))
                                                    <div class="date" style="padding-bottom: 3px" ><strong>Paid({{$currency->name}})</strong> {{$currency->symbol}}{{ number_format($paid,2) }}</div>
                                                @endif
                                          
                                                <div class="date" style="padding-bottom: 3px" ><strong>Closing Balance({{$currency->name}}) on {{ date('F j, Y', strtotime($to)) }}</strong> {{$currency->symbol}}{{ number_format($closing_balance ? $closing_balance : 0,2) }}</div>
                                             
                                </div>
                            </div> 
                            <table>
                                <thead>
                                    <tr>
                                        <td colspan="2"  class="text-center"><strong>{{ date('F j, Y', strtotime($from)) }}</strong></td>
                                        <td colspan="3" class="text-center"><strong>Opening Balance {{ $currency->name }}</strong></td>
                                        <td  class="text-center"><strong>{{ $currency->symbol }}{{  number_format($opening_balance ? $opening_balance->accrual_balance : 0 ,2) }}</strong></td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><strong>Date</strong></th>
                                        <th class="text-center"> <strong>Item</strong></th>
                                        <th class="text-center"> <strong>Currency</strong></th>
                                        <th class="text-center"> <strong>Amount</strong></th>
                                        <th class="text-center"><strong>Bill Bal</strong></th>
                                        <th class="text-center"><strong>Accrual Balance</strong></th>
                                </thead>
                                <tbody>
                                    @if (isset($results))
                                        @foreach ($results->sortBy('created_at')->sortBy('transaction_date') as $result)
                                            @php
                                                $currency = App\Models\Currency::find($result->currency_id);
                                                
                                            @endphp
                                            <tr>
                                                <td class="text-center">{{ date('F j, Y', strtotime($result->transaction_date)) }}</td>
                                                <td class="text-center">
                                                        @php
                                                              if ($result->transaction_type === 'bill') {
                                                                $bill = \App\Models\Bill::where('bill_number', $result->number)
                                                                            ->where('authorization', 'approved')
                                                                            ->first();
                                                                // use $bill
                                                            } elseif ($result->transaction_type === 'payment') {
                                                                $payment = \App\Models\Payment::where('payment_number', $result->number)
                                                                            ->first();
                                                                // use $payment
                                                            }
                                                            
                                                            $accrual_balance = App\Models\Bill::where('authorization','approved')->where('vendor_id',$vendor->id)->where('currency_id', $currency->id)->whereRaw('balance REGEXP "^-?[0-9]+(\.[0-9]+)?$"')->get()->sum('balance');
                                                        @endphp
                                                        @if ($result->transaction_type === 'bill')
                                                            <a href="{{ route('bills.show',$bill->id) }}" target="_blank" rel="noopener noreferrer" style="color: blue">Bill# {{ $result->number }} </a><br>
                                                            Due {{ $bill->expiry }}
                                                        @elseif ($result->transaction_type === 'payment')
                                                        <a href="{{ route('payments.show',$payment->id) }}" target="_blank" rel="noopener noreferrer" style="color: blue">{{ $result->number }}</a> Payment  made for 
                                                        @if (isset($payment->bill))
                                                        <a href="{{ route('bills.show',$payment->bill->id) }}" target="_blank" rel="noopener noreferrer" style="color: blue">Bill# {{ $payment->bill ? $payment->bill->bill_number : "" }} </a> 
                                                        @else
                                                            @foreach ($payment->bill_payments as $bill_payment)
                                                                <a href="{{ route('bills.show',$bill_payment->bill->id) }}" target="_blank" rel="noopener noreferrer" style="color: blue">Bill# {{ $bill_payment->bill ? $bill_payment->bill->bill_number : "" }} </a> 
                                                                @if (!$loop->last)
                                                                    ,
                                                                @endif
                                                                
                                                            @endforeach
                                                        @endif
                                                      
                                                            <br>
                                                            {{ $payment->notes }} 
                                                        @endif
                                                    </td>
                                                <td class="text-center">{{ $currency->name}}</td>
                                                <td class="text-center">{{ $currency->symbol}}{{ number_format($result->amount,2) }}</td>
                                                <td class="text-center">{{ $currency->symbol}}{{ number_format($result->balance,2) }}</td>
                                                <td class="text-center">{{ $currency->symbol}}{{ number_format($result->accrual_balance ? $result->accrual_balance : 0,2) }}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                  
                                    
                                      
                                        <tr>
                                            <td colspan="2"  class="text-center"><strong>{{ date('F j, Y', strtotime($from)) }}</strong></td>
                                            <td colspan="3" class="text-center"><strong>Closing Balance {{ $currency->name }}</strong></td>
                                            <td  class="text-center"><strong>{{ $currency->symbol }}{{  number_format($closing_balance ? $closing_balance : 0,2) }}</strong></td>
                                        </tr>
                                      
                                </tbody>
                            </table>
                           
                        </main>
                        @endif
                        @endif
                        @endif
                        @endforeach
                    </div>
                    <!--DO NOT DELETE THIS div. IT is responsible for showing footer always at the bottom-->
                    <div></div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
