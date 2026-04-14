<div>
    <div id="print-area">
    <div id="invoice">
        <x-loading/>
        <div class="toolbar hidden-print">
            <div class="text-end">
                <button type="button" onclick="goBack()" class="btn btn-default border-primary btn-wide btn-rounded">
                    <i class="fa fa-arrow-left" style="color:black"></i> Back
                </button>
                <a href="javascript:void(0)" onclick="printSection()" class="btn btn-default border-primary btn-wide btn-rounded">
                    <i class="fa fa-print" style="color: black"></i> Print
                </a>

                @if (isset($selectedCustomer) && (isset($selectedType) && $selectedType == "Outstanding Invoices"))
                    <a href="{{ route('customer_statements.pdf.outstanding', ['selectedCustomer' => $selectedCustomer, 'selectedType' => $selectedType]) }}"
                       class="btn btn-default border-primary btn-wide btn-rounded">
                        <i class="fa fa-file-pdf-o" style="color:red"></i> Export as PDF
                    </a>
                    <a href="{{ route('customer_statements.email.outstanding', ['selectedCustomer' => $selectedCustomer, 'selectedType' => $selectedType]) }}"
                       class="btn btn-default border-primary btn-wide btn-rounded">
                        <i class="fa fa-envelope" style="color:red"></i> Send Email
                    </a>
                @elseif(isset($selectedCustomer) && (isset($selectedType) && $selectedType == "Account Activity") && isset($from) && isset($to))
                    <a href="{{ route('customer_statements.pdf.account', ['selectedCustomer' => $selectedCustomer, 'selectedType' => $selectedType, 'from' => $from, 'to' => $to]) }}"
                       class="btn btn-default border-primary btn-wide btn-rounded">
                        <i class="fa fa-file-pdf-o" style="color:red"></i> Export as PDF
                    </a>
                    <a href="{{ route('customer_statements.email.account', ['selectedCustomer' => $selectedCustomer, 'selectedType' => $selectedType, 'from' => $from, 'to' => $to]) }}"
                       class="btn btn-default border-primary btn-wide btn-rounded">
                        <i class="fa fa-envelope" style="color:red"></i> Send Email
                    </a>
                @endif
            </div>
            <hr>
        </div>

        <div class="invoice overflow-auto">
            @include('includes.messages')
            <div style="min-width: 600px">
                {{-- ===================== HEADER ===================== --}}
                <header>
                    <div class="row">
                        <div class="col">
                            <a href="javascript:;">
                                <img src="{{ asset('images/uploads/' . $company->logo) }}" width="150" alt="">
                            </a>
                        </div>
                        <div class="col company-details">
                            <h4 class="name">
                                <a target="_blank" href="javascript:;"
                                   style="color: {{ Auth::user()->employee->company ? Auth::user()->employee->company->color : Auth::user()->company->color }}">
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
                                @if ($company->third_phonenumber) | {{ $company->third_phonenumber }} @endif
                            </div>
                            <div>{{ $company->email }}</div>
                            @if ($company->second_email) <div>{{ $company->second_email }}</div> @endif
                            @if ($company->third_email)  <div>{{ $company->third_email }}</div><br> @endif
                            @if (isset($company->vat_number)) <div>VAT No.: {{ $company->vat_number }}</div> @endif
                            @if (isset($company->tin_number)) <div>TIN.: {{ $company->tin_number }}</div> @endif
                            <br>
                            <h4 class="to">Statement Of Account</h4>
                            <div><strong>{{ $selectedType }}</strong></div>
                            <br>
                        </div>
                    </div>
                </header>

                @php
                    $currencies         = App\Models\Currency::all();
                    $invoiced_currencies = App\Models\Invoice::where('customer_id', $customer->id)
                                            ->where('authorization', 'approved')
                                            ->pluck('currency_id')
                                            ->toArray();
                @endphp

                @foreach ($currencies as $currency)
                    @if (isset($invoiced_currencies) && in_array($currency->id, $invoiced_currencies))

                        {{-- =================== OUTSTANDING INVOICES =================== --}}
                        @if ($selectedType == "Outstanding Invoices")
                        <main>
                            <div class="row contacts">
                                <div class="col invoice-to">
                                    <div class="text-gray-light">BILL TO:</div>
                                    <h5 class="to">{{ $customer->name }}</h5>
                                    <div class="address">
                                        {{ $customer->street_address }}
                                        @if ($customer->suburb)
                                            {{ $customer->suburb }},<br>
                                        @endif
                                        {{ $customer->city ? $customer->city . ',' : '' }} {{ $customer->country }}
                                    </div>
                                    <div class="email">
                                        <a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a>
                                    </div>
                                </div>
                                <div class="col invoice-details">
                                    <h5 class="to">{{ $currency->fullname }} {{ $currency->name }}</h5>
                                    <div class="date" style="padding-bottom: 3px">
                                        <strong>As of {{ date('F j, Y') }}</strong>
                                    </div>
                                    @php
                                        $outstanding_balance = App\Models\Invoice::where('customer_id', $customer->id)
                                            ->where('currency_id', $currency->id)
                                            ->where('authorization', 'approved')
                                            ->where(function ($q) {
                                                $q->where('status', 'Unpaid')
                                                  ->orWhere('status', 'Partial');
                                            })
                                            ->sum('balance');
                                    @endphp
                                    @if (isset($outstanding_balance))
                                        <div class="date" style="padding-bottom: 3px">
                                            <strong>Outstanding Balance ({{ $currency->name }})</strong>
                                            {{ $currency->symbol }}{{ number_format($outstanding_balance, 2) }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <table>
                                <thead>
                                    <tr>
                                        <th class="text-center"><strong>Invoice#</strong></th>
                                        <th class="text-center"><strong>Invoice Date</strong></th>
                                        <th class="text-center"><strong>Due Date</strong></th>
                                        <th class="text-center"><strong>Currency</strong></th>
                                        <th class="text-center"><strong>Total</strong></th>
                                        <th class="text-center"><strong>Paid</strong></th>
                                        <th class="text-center"><strong>Due</strong></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($invoices as $invoice)
                                        @if ($invoice->currency_id == $currency->id)
                                            @php
                                                $now         = new DateTime();
                                                $expiry_date = new DateTime($invoice->expiry);
                                            @endphp
                                            <tr>
                                                <td class="text-center">
                                                    <a href="{{ route('invoices.show', $invoice->id) }}">
                                                        {{ $invoice->invoice_number }}
                                                    </a>
                                                </td>
                                                <td class="text-center">{{ date('F j, Y', strtotime($invoice->date)) }}</td>
                                                <td class="text-center">
                                                    {{ date('F j, Y', strtotime($invoice->expiry)) }}
                                                    @if ($now >= $expiry_date) <span class="label label-danger">Overdue</span> @endif
                                                </td>
                                                <td class="text-center">{{ $invoice->currency ? $invoice->currency->name : '' }}</td>
                                                <td class="text-center">
                                                    @if ($invoice->total)
                                                        {{ $invoice->currency ? $invoice->currency->symbol : '' }}{{ number_format($invoice->total, 2) }}
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if ($invoice->payments)
                                                        {{ $invoice->currency ? $invoice->currency->symbol : '' }}{{ number_format($invoice->payments->sum('amount'), 2) }}
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if ($invoice->balance)
                                                        {{ $invoice->currency ? $invoice->currency->symbol : '' }}{{ number_format($invoice->balance, 2) }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    @php
                                        $footer_balance = App\Models\Invoice::where('customer_id', $customer->id)
                                            ->where('currency_id', $currency->id)
                                            ->where('authorization', 'approved')
                                            ->where(function ($q) {
                                                $q->where('status', 'Unpaid')
                                                  ->orWhere('status', 'Partial');
                                            })
                                            ->sum('balance');
                                    @endphp
                                    @if (isset($footer_balance))
                                        <tr>
                                            <td colspan="3"></td>
                                            <td colspan="3" class="text-center">Outstanding Balance ({{ $currency->name }})</td>
                                            <td class="text-center">
                                                {{ $currency->symbol }}{{ number_format($footer_balance, 2) }}
                                            </td>
                                        </tr>
                                    @endif
                                </tfoot>
                            </table>
                        </main>

                        {{-- =================== ACCOUNT ACTIVITY =================== --}}
                        @elseif ($selectedType == "Account Activity")
                        <main>
                            @php
                                /*
                                 * Use the pre-computed accrual_balance snapshots passed in from the
                                 * component — same logic as the Index blade, so figures always match.
                                 */
                                $opening_balance = $openingBalances[$currency->id] ?? 0.00;
                                $closing_balance = $closingBalances[$currency->id] ?? 0.00;

                                $invoiced = App\Models\Invoice::where('customer_id', $customer->id)
                                    ->where('authorization', 'approved')
                                    ->where('currency_id', $currency->id)
                                    ->where('date', '>=', $from)
                                    ->where('date', '<=', $to)
                                    ->whereRaw('total REGEXP "^-?[0-9]+(\\.[0-9]+)?$"')
                                    ->sum('total');

                                $paid = App\Models\Payment::where('customer_id', $customer->id)
                                    ->where('currency_id', $currency->id)
                                    ->whereBetween('date', [$from, $to])
                                    ->whereRaw('amount REGEXP "^-?[0-9]+(\\.[0-9]+)?$"')
                                    ->sum('amount');
                            @endphp

                            <div class="row contacts">
                                <div class="col invoice-to">
                                    <div class="text-gray-light">BILL TO:</div>
                                    <h4 class="to">{{ $customer->name }}</h4>
                                    <div class="address">
                                        {{ $customer->street_address }}
                                        @if ($customer->suburb)
                                            {{ $customer->suburb }},<br>
                                        @endif
                                        {{ $customer->city ? $customer->city . ',' : '' }} {{ $customer->country }}
                                    </div>
                                    <div class="email">
                                        <a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a>
                                    </div>
                                </div>
                                <div class="col invoice-details">
                                    <h5 class="to">{{ $currency->fullname }} {{ $currency->name }}</h5>
                                    <div class="date" style="padding-bottom: 3px">
                                        <strong>From: </strong>{{ date('F j, Y', strtotime($from)) }}
                                    </div>
                                    <div class="date" style="padding-bottom: 3px">
                                        <strong>To: </strong>{{ date('F j, Y', strtotime($to)) }}
                                    </div>
                                    <hr>
                                    <div class="date" style="padding-bottom: 3px">
                                        <strong>Opening Balance ({{ $currency->name }}) on {{ date('F j, Y', strtotime($from)) }}</strong>
                                        {{ $currency->symbol }}{{ number_format($opening_balance, 2) }}
                                    </div>
                                    @if (isset($invoiced))
                                        <div class="date" style="padding-bottom: 3px">
                                            <strong>Invoiced ({{ $currency->name }})</strong>
                                            {{ $currency->symbol }}{{ number_format($invoiced, 2) }}
                                        </div>
                                    @endif
                                    @if (isset($paid))
                                        <div class="date" style="padding-bottom: 3px">
                                            <strong>Paid ({{ $currency->name }})</strong>
                                            {{ $currency->symbol }}{{ number_format($paid, 2) }}
                                        </div>
                                    @endif
                                    <div class="date" style="padding-bottom: 3px">
                                        <strong>Closing Balance ({{ $currency->name }}) on {{ date('F j, Y', strtotime($to)) }}</strong>
                                        {{ $currency->symbol }}{{ number_format($closing_balance, 2) }}
                                    </div>
                                </div>
                            </div>

                            <table>
                                <thead>
                                    <tr>
                                        <td colspan="2" class="text-center">
                                            <strong>{{ date('F j, Y', strtotime($from)) }}</strong>
                                        </td>
                                        <td colspan="3" class="text-center">
                                            <strong>Opening Balance {{ $currency->name }}</strong>
                                        </td>
                                        <td class="text-center">
                                            <strong>{{ $currency->symbol }}{{ number_format($opening_balance, 2) }}</strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-center"><strong>Date</strong></th>
                                        <th class="text-center"><strong>Item</strong></th>
                                        <th class="text-center"><strong>Currency</strong></th>
                                        <th class="text-center"><strong>Amount</strong></th>
                                        <th class="text-center"><strong>Invoice Bal</strong></th>
                                        <th class="text-center"><strong>Accrual Balance</strong></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($results))
                                        @foreach ($results->where('currency_id', $currency->id)->sortBy('created_at')->sortBy('transaction_date') as $result)
                                            @php
                                                $row_currency = App\Models\Currency::find($result->currency_id);

                                                if ($result->transaction_type === 'invoice') {
                                                    $invoice = App\Models\Invoice::where('invoice_number', $result->number)
                                                                ->where('authorization', 'approved')
                                                                ->first();
                                                } elseif ($result->transaction_type === 'payment') {
                                                    $payment = App\Models\Payment::where('payment_number', $result->number)
                                                                ->first();
                                                }
                                            @endphp
                                            <tr>
                                                <td class="text-center">
                                                    {{ date('F j, Y', strtotime($result->transaction_date)) }}
                                                </td>
                                                <td class="text-center">
                                                    @if ($result->transaction_type === 'invoice' && isset($invoice))
                                                        <a href="{{ route('invoices.show', $invoice->id) }}"
                                                           target="_blank" rel="noopener noreferrer" style="color: blue">
                                                            Invoice# {{ $result->number }}
                                                        </a><br>
                                                        Due {{ $invoice->expiry }}

                                                    @elseif ($result->transaction_type === 'payment' && isset($payment))
                                                        <a href="{{ route('payments.show', $payment->id) }}"
                                                           target="_blank" rel="noopener noreferrer" style="color: blue">
                                                            {{ $result->number }}
                                                        </a> Payment
                                                        @if (isset($payment->invoice))
                                                            made for
                                                            <a href="{{ route('invoices.show', $payment->invoice->id) }}"
                                                               target="_blank" rel="noopener noreferrer" style="color: blue">
                                                                Invoice# {{ $payment->invoice->invoice_number }}
                                                            </a>
                                                        @elseif ($payment->invoice_payments && $payment->invoice_payments->count() > 0)
                                                            made for
                                                            @foreach ($payment->invoice_payments as $invoice_payment)
                                                                <a href="{{ route('invoices.show', $invoice_payment->invoice->id) }}"
                                                                   target="_blank" rel="noopener noreferrer" style="color: blue">
                                                                    Invoice# {{ $invoice_payment->invoice ? $invoice_payment->invoice->invoice_number : '' }}
                                                                </a>
                                                                @if (!$loop->last), @endif
                                                            @endforeach
                                                        @endif
                                                        <br>
                                                        {{ $payment->notes }}
                                                    @endif
                                                </td>
                                                <td class="text-center">{{ $row_currency->name }}</td>
                                                <td class="text-center">
                                                    {{ $row_currency->symbol }}{{ number_format($result->amount, 2) }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $row_currency->symbol }}{{ number_format($result->balance, 2) }}
                                                </td>
                                                <td class="text-center">
                                                    {{ $row_currency->symbol }}{{ number_format($result->accrual_balance ?? 0, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif

                                    {{-- Closing balance row --}}
                                    <tr>
                                        <td colspan="2" class="text-center">
                                            <strong>{{ date('F j, Y', strtotime($to)) }}</strong>
                                        </td>
                                        <td colspan="3" class="text-center">
                                            <strong>Closing Balance {{ $currency->name }}</strong>
                                        </td>
                                        <td class="text-center">
                                            <strong>{{ $currency->symbol }}{{ number_format($closing_balance, 2) }}</strong>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </main>
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