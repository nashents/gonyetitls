@php
    $bills = $bills ?? collect();
    $forPdf = $forPdf ?? false;
    $appliedAmount = function ($bill) use ($payment) {
        if ($payment->bill_id && (int) $payment->bill_id === (int) $bill->id) {
            return $payment->amount;
        }
        $bill_payment = $payment->bill_payments->firstWhere('bill_id', $bill->id);
        return $bill_payment->amount ?? 0;
    };
@endphp
<header @if ($forPdf) style="margin-top:-25px; padding-bottom:10px" @endif>
    <div class="row">
        <div class="col" @if ($forPdf) style="margin-top:-15px;" @endif>
            <a href="javascript:;">
                <img src="{{asset('images/uploads/'.$company->logo)}}" width="150" alt="">
            </a>
        </div>
        <div class="col company-details" @if ($forPdf) style="margin-top:-120px;" @endif>
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
            <h3 class="to" >Remittance Advice</h3>
            <div style="margin-top:-10px;">Payment {{$payment->payment_number}}</div>
            <br>
        </div>
    </div>
</header>
<main>
    <div class="row contacts">
        <div class="col invoice-to" >
            <div class="text-gray-light">PAID TO:</div>
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
        <div class="col invoice-details" @if ($forPdf) style="margin-top:-150px;" @endif>
            <h3 class="to" >{{$payment->currency ? $payment->currency->fullname : ""}} {{$payment->currency ? $payment->currency->name : ""}}</h3>
            <div class="date" style="padding-bottom: 3px;"><strong>Payment Date</strong> {{ date('F j, Y', strtotime($payment->date)) }}</div>
            <div class="date" style="padding-bottom: 3px;"><strong>Payment #</strong> {{ $payment->payment_number }}</div>
            @if ($payment->mode_of_payment)
            <div class="date" style="padding-bottom: 3px;"><strong>Mode Of Payment</strong> {{ $payment->mode_of_payment }}</div>
            @endif
            @if ($payment->reference_code)
            <div class="date" style="padding-bottom: 3px;"><strong>Reference #</strong> {{ $payment->reference_code }}</div>
            @endif
            <div class="date" style="padding-bottom: 3px" ><strong>Amount Remitted ({{$payment->currency ? $payment->currency->name : ""}})</strong> {{$payment->currency ? $payment->currency->symbol : ""}}{{ number_format($payment->amount,2) }}</div>
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
                <th class="text-center"><strong>Applied</strong></th>
                <th class="text-center"><strong>Balance</strong></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($bills as $bill)
                <tr>
                    <td class="text-center"> <a href="{{ route('bills.show',$bill->id) }}">{{ $bill->bill_number }}</a> </td>
                    <td class="text-center">{{ date('F j, Y', strtotime($bill->date)) }} </td>
                    <td class="text-center">{{ $bill->expiry ? date('F j, Y', strtotime($bill->expiry)) : "" }}</td>
                    <td class="text-center"> {{$bill->currency ? $bill->currency->name : ""}}</td>
                    <td class="text-center">
                        @if ($bill->total)
                        {{ $bill->currency ? $bill->currency->symbol : "" }}{{number_format($bill->total,2)}}
                        @endif
                    </td>
                    <td class="text-center">
                        {{ $bill->currency ? $bill->currency->symbol : "" }}{{number_format($appliedAmount($bill),2)}}
                    </td>
                    <td class="text-center">
                        @if (isset($bill->balance))
                        {{ $bill->currency ? $bill->currency->symbol : "" }}{{number_format($bill->balance,2)}}
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">No bills linked to this payment</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" ></td>
                <td class="text-center">Total Remitted</td>
                <td class="text-center"><strong>{{$payment->currency ? $payment->currency->symbol : ""}}{{number_format($payment->amount,2)}}</strong></td>
            </tr>
        </tfoot>
    </table>
    @if ($payment->notes)
    <div class="date" style="padding-top: 10px"><strong>Notes:</strong> {{ $payment->notes }}</div>
    @endif
</main>
