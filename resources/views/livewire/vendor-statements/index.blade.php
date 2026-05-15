<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">

                        <div>
                            @include('includes.messages')
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel">
                                    <div class="panel-heading">
                                        <div class="panel-title">
                                            <h5>Vendor Statements</h5>
                                        </div>
                                    </div>
                                    <div class="panel-body">

                                        {{-- Filters --}}
                                        <div class="row">
                                            <div class="col-lg-3">
                                                <div class="input-group">
                                                    <span class="input-group-addon">Vendors</span>
                                                    <select wire:model="selectedVendor" class="form-control">
                                                        <option value="">Select Vendor</option>
                                                        @foreach ($vendors as $v)
                                                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-lg-3">
                                                <div class="input-group">
                                                    <span class="input-group-addon">Type</span>
                                                    <select wire:model="selectedType" class="form-control">
                                                        <option value="">Select Type</option>
                                                        <option value="Outstanding Bills">Outstanding Bills</option>
                                                        <option value="Account Activity">Account Activity</option>
                                                    </select>
                                                </div>
                                            </div>

                                            @if ($selectedType === 'Account Activity')
                                                <div class="col-lg-2">
                                                    <div class="input-group">
                                                        <span class="input-group-addon">From</span>
                                                        <input type="date" wire:model="from" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="col-lg-2">
                                                    <div class="input-group" style="margin-left:15px;">
                                                        <span class="input-group-addon">To</span>
                                                        <input type="date" wire:model="to" class="form-control">
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <br><br>

                                        {{-- Action Buttons --}}
                                        @if ($bills && $bills->count() > 0)
                                            <div class="row">
                                                <div class="col-md-4"></div>
                                                <div class="col-md-6">
                                                    <div class="col-lg-2">
                                                        <div class="btn-group">
                                                            @if ($selectedVendor && $selectedType === 'Outstanding Bills')
                                                                <a href="{{ route('vendor_statements.preview.outstanding', ['selectedVendor' => $selectedVendor, 'selectedType' => $selectedType]) }}"
                                                                   class="btn btn-default border-primary btn-wide btn-rounded">
                                                                    <i class="fas fa-file-invoice"></i> Preview
                                                                </a>
                                                            @elseif ($selectedVendor && $selectedType === 'Account Activity' && $from && $to)
                                                                <a href="{{ route('vendor_statements.preview.account', ['selectedVendor' => $selectedVendor, 'selectedType' => $selectedType, 'from' => $from, 'to' => $to]) }}"
                                                                   class="btn btn-default border-primary btn-wide btn-rounded">
                                                                    <i class="fas fa-file-invoice"></i> Preview
                                                                </a>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    @if ($selectedVendor && $selectedType === 'Outstanding Bills')
                                                        <div class="col-lg-2">
                                                            <div class="btn-group">
                                                                <button wire:click="exportVendorStatementExcel()"
                                                                        class="btn btn-default border-primary btn-wide btn-rounded">
                                                                    <i class="fas fa-download"></i> Excel
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="col-md-2"></div>
                                            </div>
                                        @endif

                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Results --}}
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">

                            @if ($selectedType === 'Outstanding Bills')

                                <table class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <caption>
                                        Outstanding Bills for {{ $vendor?->name }}
                                    </caption>
                                    <thead>
                                        <tr>
                                            <th>Bill#</th>
                                            <th>Vendor</th>
                                            <th>Bill Date</th>
                                            <th>Due Date</th>
                                            <th>Currency</th>
                                            <th>Bill Total</th>
                                            <th>Amount Paid</th>
                                            <th>Amount Due</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($bills ?? [] as $bill)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('bills.show', $bill->id) }}" style="color:blue">
                                                        {{ $bill->bill_number }}
                                                    </a>
                                                </td>
                                                <td>{{ $bill->vendor?->name }}</td>
                                                <td>{{ $bill->bill_date }}</td>
                                                <td>{{ $bill->due_date }}</td>
                                                <td>{{ $bill->currency?->name }}</td>
                                                <td>{{ $bill->currency?->symbol }}{{ number_format($bill->total, 2) }}</td>
                                                <td>{{ $bill->currency?->symbol }}{{ number_format($bill->payments->sum('amount'), 2) }}</td>
                                                <td>{{ $bill->currency?->symbol }}{{ number_format($bill->balance, 2) }}</td>
                                                <td>
                                                    <span class="label label-{{ $bill->status === 'Paid' ? 'success' : ($bill->status === 'Partial' ? 'warning' : 'danger') }}">
                                                        {{ $bill->status }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center">
                                                    <img style="width:25%; height:25%;" src="{{ asset('images/nodata.png') }}" alt="">
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>

                            @elseif ($selectedType === 'Account Activity')

                                @forelse ($statementByCurrency as $currencyId => $data)
                                    @php $currency = $data['currency']; @endphp
                                    <table class="table table-striped table-bordered table-sm table-responsive" id="billsTable-{{ $currencyId }}" cellspacing="0" width="100%">
                                        <caption>
                                            Account Activity for {{ $vendor?->name }} &mdash; {{ $currency?->fullname }} ({{ $currency?->name }})
                                        </caption>
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Item</th>
                                                <th>Currency</th>
                                                <th>Amount</th>
                                                <th>Balance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- Opening Balance --}}
                                            @if ($data['opening_balance'] !== null)
                                                <tr>
                                                    <td colspan="2"><strong>{{ \Carbon\Carbon::parse($from)->format('F j, Y') }}</strong></td>
                                                    <td colspan="2"><strong>Opening Balance {{ $currency?->name }}</strong></td>
                                                    <td><strong>{{ $currency?->symbol }}{{ number_format($data['opening_balance'], 2) }}</strong></td>
                                                </tr>
                                            @endif

                                            {{-- Transactions --}}
                                            @forelse ($data['results'] as $result)
                                                @php
                                                    $bill    = \App\Models\Bill::where('bill_number', $result->number)->where('authorization', 'approved')->first();
                                                    $payment = \App\Models\Payment::where('payment_number', $result->number)->first();
                                                @endphp
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($result->transaction_date)->format('F j, Y') }}</td>
                                                    <td>
                                                        @if ($bill)
                                                            <a href="{{ route('bills.show', $bill->id) }}" target="_blank" style="color:blue">
                                                                Bill# {{ $result->number }}
                                                            </a><br>
                                                            Due {{ $bill->expiry }}
                                                        @elseif ($payment)
                                                            <a href="{{ route('payments.show', $payment->id) }}" target="_blank" style="color:blue">
                                                                {{ $result->number }}
                                                            </a> Payment made for
                                                            @if ($payment->bill)
                                                                <a href="{{ route('bills.show', $payment->bill->id) }}" target="_blank" style="color:blue">
                                                                    Bill# {{ $payment->bill->bill_number }}
                                                                </a>
                                                            @elseif ($payment->bill_payment?->bill)
                                                                <a href="{{ route('bills.show', $payment->bill_payment->bill->id) }}" target="_blank" style="color:blue">
                                                                    Bill# {{ $payment->bill_payment->bill->bill_number }}
                                                                </a>
                                                            @endif
                                                        @endif
                                                    </td>
                                                    <td>{{ $currency?->name }}</td>
                                                    <td>{{ $currency?->symbol }}{{ number_format($result->amount, 2) }}</td>
                                                    <td>{{ $currency?->symbol }}{{ number_format($result->balance, 2) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center">No transactions found.</td>
                                                </tr>
                                            @endforelse

                                            {{-- Closing Balance --}}
                                            @if ($data['closing_balance'] !== null)
                                                <tr>
                                                    <td colspan="2"><strong>{{ \Carbon\Carbon::parse($to)->format('F j, Y') }}</strong></td>
                                                    <td colspan="2"><strong>Closing Balance {{ $currency?->name }}</strong></td>
                                                    <td><strong>{{ $currency?->symbol }}{{ number_format($data['closing_balance'], 2) }}</strong></td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                @empty
                                    @include('partials.empty-statement')
                                @endforelse

                            @else
                                @include('partials.empty-statement')
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
