<div>
    <div id="invoice">
        <x-loading/>

        {{-- Toolbar --}}
        <div class="toolbar hidden-print">
            <div class="text-end">
                <button type="button" onclick="goBack()" class="btn btn-default border-primary btn-wide btn-rounded">
                    <i class="fa fa-arrow-left" style="color:black"></i> Back
                </button>

                @if ($selectedVendor && $selectedType === 'Outstanding Bills')
                    <a href="{{ route('vendor_statements.print.outstanding', ['selectedVendor' => $selectedVendor, 'selectedType' => $selectedType]) }}" class="btn btn-default border-primary btn-wide btn-rounded">
                        <i class="fa fa-print" style="color:black"></i> Print
                    </a>
                    <a href="{{ route('vendor_statements.pdf.outstanding', ['selectedVendor' => $selectedVendor, 'selectedType' => $selectedType]) }}" class="btn btn-default border-primary btn-wide btn-rounded">
                        <i class="fa fa-file-pdf-o" style="color:red"></i> Export as PDF
                    </a>
                    <a href="{{ route('vendor_statements.email.outstanding', ['selectedVendor' => $selectedVendor, 'selectedType' => $selectedType]) }}" class="btn btn-default border-primary btn-wide btn-rounded">
                        <i class="fa fa-envelope" style="color:red"></i> Send Email
                    </a>
                @elseif ($selectedVendor && $selectedType === 'Account Activity' && $from && $to)
                    <a href="{{ route('vendor_statements.print.account', ['selectedVendor' => $selectedVendor, 'selectedType' => $selectedType, 'from' => $from, 'to' => $to]) }}" class="btn btn-default border-primary btn-wide btn-rounded">
                        <i class="fa fa-print" style="color:black"></i> Print
                    </a>
                    <a href="{{ route('vendor_statements.pdf.account', ['selectedVendor' => $selectedVendor, 'selectedType' => $selectedType, 'from' => $from, 'to' => $to]) }}" class="btn btn-default border-primary btn-wide btn-rounded">
                        <i class="fa fa-file-pdf-o" style="color:red"></i> Export as PDF
                    </a>
                    <a href="{{ route('vendor_statements.email.account', ['selectedVendor' => $selectedVendor, 'selectedType' => $selectedType, 'from' => $from, 'to' => $to]) }}" class="btn btn-default border-primary btn-wide btn-rounded">
                        <i class="fa fa-envelope" style="color:red"></i> Send Email
                    </a>
                @endif
            </div>
            <hr>
        </div>

        <div class="invoice overflow-auto">
            @include('includes.messages')

            <div style="min-width: 600px">

                {{-- Header --}}
                <header>
                    <div class="row">
                        <div class="col">
                            <a href="javascript:;">
                                <img src="{{ asset('images/uploads/' . $company->logo) }}" width="150" alt="">
                            </a>
                        </div>
                        <div class="col company-details">
                            <h4 class="name">
                                <a target="_blank" href="javascript:;" style="color: {{ Auth::user()->employee->company?->color ?? Auth::user()->company->color }}">
                                    {{ $company->name }}
                                </a>
                            </h4>
                            <div>
                                {{ $company->street_address }} {{ $company->suburb }}<br>
                                {{ $company->city }}, {{ $company->country }}
                            </div>
                            <div>
                                {{ $company->phonenumber }}
                                @if ($company->second_phonenumber) | {{ $company->second_phonenumber }} @endif
                                @if ($company->third_phonenumber)  | {{ $company->third_phonenumber }}  @endif
                            </div>
                            <div>{{ $company->email }}</div>
                            @if ($company->second_email) <div>{{ $company->second_email }}</div> @endif
                            @if ($company->third_email)  <div>{{ $company->third_email }}</div><br> @endif
                            @if ($company->vat_number)   <div>VAT No.: {{ $company->vat_number }}</div> @endif
                            @if ($company->tin_number)   <div>TIN.: {{ $company->tin_number }}</div>    @endif
                            <br>
                            <h4 class="to">Statement Of Account</h4>
                            <div><strong>{{ $selectedType }}</strong></div>
                            <br>
                        </div>
                    </div>
                </header>

                {{-- Outstanding Bills --}}
                @if ($selectedType === 'Outstanding Bills')

                    @php $billCurrencies = $bills ? $bills->pluck('currency_id')->unique() : collect(); @endphp

                    @foreach ($billCurrencies as $currencyId)
                        @php
                            $currency      = $bills->firstWhere('currency_id', $currencyId)?->currency;
                            $currencyBills = $bills->where('currency_id', $currencyId);
                            $outstandingBal = $currencyBills->sum('balance');
                        @endphp

                        @if (!$currency) @continue @endif

                        <main>
                            <div class="row contacts">
                                <div class="col invoice-to">
                                    <div class="text-gray-light">BILL TO:</div>
                                    <h5 class="to">{{ $vendor->name }}</h5>
                                    <div class="address">
                                        {{ $vendor->street_address }}
                                        @if ($vendor->suburb) {{ $vendor->suburb }},<br> @endif
                                        {{ $vendor->city ? $vendor->city . ',' : '' }} {{ $vendor->country }}
                                    </div>
                                    <div class="email">
                                        <a href="mailto:{{ $vendor->email }}">{{ $vendor->email }}</a>
                                    </div>
                                </div>
                                <div class="col invoice-details">
                                    <h5 class="to">{{ $currency->fullname }} {{ $currency->name }}</h5>
                                    <div class="date" style="padding-bottom:3px">
                                        <strong>As of {{ now()->format('F j, Y') }}</strong>
                                    </div>
                                    <div class="date" style="padding-bottom:3px">
                                        <strong>Outstanding Balance ({{ $currency->name }})</strong>
                                        {{ $currency->symbol }}{{ number_format($outstandingBal, 2) }}
                                    </div>
                                </div>
                            </div>

                            <table>
                                <thead>
                                    <tr>
                                        <th class="text-center">Bill#</th>
                                        <th class="text-center">Bill Date</th>
                                        <th class="text-center">Due Date</th>
                                        <th class="text-center">Currency</th>
                                        <th class="text-center">Total</th>
                                        <th class="text-center">Paid</th>
                                        <th class="text-center">Due</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($currencyBills as $bill)
                                        @php
                                            $expiryDate = \Carbon\Carbon::parse($bill->expiry);
                                        @endphp
                                        <tr>
                                            <td class="text-center">
                                                <a href="{{ route('bills.show', $bill->id) }}">{{ $bill->bill_number }}</a>
                                            </td>
                                            <td class="text-center">{{ \Carbon\Carbon::parse($bill->bill_date)->format('F j, Y') }}</td>
                                            <td class="text-center">
                                                {{ $expiryDate->format('F j, Y') }}
                                                @if (now()->gte($expiryDate))
                                                    <span class="text-danger">Overdue</span>
                                                @endif
                                            </td>
                                            <td class="text-center">{{ $bill->currency?->name }}</td>
                                            <td class="text-center">{{ $bill->currency?->symbol }}{{ number_format($bill->total, 2) }}</td>
                                            <td class="text-center">{{ $bill->currency?->symbol }}{{ number_format($bill->payments->sum('amount'), 2) }}</td>
                                            <td class="text-center">{{ $bill->currency?->symbol }}{{ number_format($bill->balance, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3"></td>
                                        <td colspan="3" class="text-center">Outstanding Balance ({{ $currency->name }})</td>
                                        <td class="text-center">{{ $currency->symbol }}{{ number_format($outstandingBal, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </main>
                    @endforeach

                {{-- Account Activity --}}
                @elseif ($selectedType === 'Account Activity')

                    @forelse ($statementByCurrency as $currencyId => $data)
                        @php $currency = $data['currency']; @endphp

                        <main>
                            <div class="row contacts">
                                <div class="col invoice-to">
                                    <div class="text-gray-light">BILL TO:</div>
                                    <h4 class="to">{{ $vendor->name }}</h4>
                                    <div class="address">
                                        {{ $vendor->street_address }}
                                        @if ($vendor->suburb) {{ $vendor->suburb }},<br> @endif
                                        {{ $vendor->city ? $vendor->city . ',' : '' }} {{ $vendor->country }}
                                    </div>
                                    <div class="email">
                                        <a href="mailto:{{ $vendor->email }}">{{ $vendor->email }}</a>
                                    </div>
                                </div>
                                <div class="col invoice-details">
                                    <h5 class="to">{{ $currency?->fullname }} {{ $currency?->name }}</h5>
                                    <div class="date" style="padding-bottom:3px">
                                        <strong>From:</strong> {{ \Carbon\Carbon::parse($from)->format('F j, Y') }}
                                    </div>
                                    <div class="date" style="padding-bottom:3px">
                                        <strong>To:</strong> {{ \Carbon\Carbon::parse($to)->format('F j, Y') }}
                                    </div>
                                    <hr>
                                    <div class="date" style="padding-bottom:3px">
                                        <strong>Opening Balance ({{ $currency?->name }}) on {{ \Carbon\Carbon::parse($from)->format('F j, Y') }}</strong>
                                        {{ $currency?->symbol }}{{ number_format($data['opening_balance'], 2) }}
                                    </div>
                                    <div class="date" style="padding-bottom:3px">
                                        <strong>Billed ({{ $currency?->name }})</strong>
                                        {{ $currency?->symbol }}{{ number_format($data['total_billed'], 2) }}
                                    </div>
                                    <div class="date" style="padding-bottom:3px">
                                        <strong>Paid ({{ $currency?->name }})</strong>
                                        {{ $currency?->symbol }}{{ number_format($data['total_paid'], 2) }}
                                    </div>
                                    <div class="date" style="padding-bottom:3px">
                                        <strong>Closing Balance ({{ $currency?->name }}) on {{ \Carbon\Carbon::parse($to)->format('F j, Y') }}</strong>
                                        {{ $currency?->symbol }}{{ number_format($data['closing_balance'], 2) }}
                                    </div>
                                </div>
                            </div>

                            <table>
                                <thead>
                                    <tr>
                                        <td colspan="2" class="text-center">
                                            <strong>{{ \Carbon\Carbon::parse($from)->format('F j, Y') }}</strong>
                                        </td>
                                        <td colspan="3" class="text-center">
                                            <strong>Opening Balance {{ $currency?->name }}</strong>
                                        </td>
                                        <td class="text-center">
                                            <strong>{{ $currency?->symbol }}{{ number_format($data['opening_balance'], 2) }}</strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-center">Date</th>
                                        <th class="text-center">Item</th>
                                        <th class="text-center">Currency</th>
                                        <th class="text-center">Amount</th>
                                        <th class="text-center">Bill Bal</th>
                                        <th class="text-center">Accrual Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($data['results'] as $result)
                                        @php
                                            $bill    = null;
                                            $payment = null;
                                            if ($result->transaction_type === 'bill') {
                                                $bill = \App\Models\Bill::where('bill_number', $result->number)
                                                            ->where('authorization', 'approved')
                                                            ->first();
                                            } else {
                                                $payment = \App\Models\Payment::where('payment_number', $result->number)->first();
                                            }
                                        @endphp
                                        <tr>
                                            <td class="text-center">
                                                {{ \Carbon\Carbon::parse($result->transaction_date)->format('F j, Y') }}
                                            </td>
                                            <td class="text-center">
                                                @if ($result->transaction_type === 'bill' && $bill)
                                                    <a href="{{ route('bills.show', $bill->id) }}" target="_blank" style="color:blue">
                                                        Bill# {{ $result->number }}
                                                    </a><br>
                                                    Due {{ $bill->expiry }}
                                                @elseif ($result->transaction_type === 'payment' && $payment)
                                                    <a href="{{ route('payments.show', $payment->id) }}" target="_blank" style="color:blue">
                                                        {{ $result->number }}
                                                    </a> Payment
                                                    @if ($payment->bill)
                                                        made for
                                                        <a href="{{ route('bills.show', $payment->bill->id) }}" target="_blank" style="color:blue">
                                                            Bill# {{ $payment->bill->bill_number }}
                                                        </a>
                                                    @elseif ($payment->bill_payments->count() > 0)
                                                        made for
                                                        @foreach ($payment->bill_payments as $bp)
                                                            <a href="{{ route('bills.show', $bp->bill->id) }}" target="_blank" style="color:blue">
                                                                Bill# {{ $bp->bill?->bill_number }}
                                                            </a>@if (!$loop->last), @endif
                                                        @endforeach
                                                    @endif
                                                    <br>{{ $payment->notes }}
                                                @endif
                                            </td>
                                            <td class="text-center">{{ $currency?->name }}</td>
                                            <td class="text-center">{{ $currency?->symbol }}{{ number_format($result->amount, 2) }}</td>
                                            <td class="text-center">{{ $currency?->symbol }}{{ number_format($result->balance, 2) }}</td>
                                            <td class="text-center">{{ $currency?->symbol }}{{ number_format($result->accrual_balance ?? 0, 2) }}</td>
                                        </tr>
                                    @endforeach

                                    <tr>
                                        <td colspan="2" class="text-center">
                                            <strong>{{ \Carbon\Carbon::parse($to)->format('F j, Y') }}</strong>
                                        </td>
                                        <td colspan="3" class="text-center">
                                            <strong>Closing Balance {{ $currency?->name }}</strong>
                                        </td>
                                        <td class="text-center">
                                            <strong>{{ $currency?->symbol }}{{ number_format($data['closing_balance'], 2) }}</strong>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </main>
                    @empty
                        @include('partials.empty-statement')
                    @endforelse

                @endif

            </div>
            <div></div>
        </div>
    </div>
</div>
