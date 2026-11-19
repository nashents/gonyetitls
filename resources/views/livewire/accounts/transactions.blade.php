<div>
    @php
        $sym = $currency ? $currency->name.' '.$currency->symbol : '';
        $reportParams = ['account_id' => $account_id, 'from' => $from, 'to' => $to, 'view' => 'details', 'currency' => $own_currency ? $currency_mode : 'reporting'];
    @endphp

    <div class="row" style="margin-bottom: 10px">
        <div class="col-md-3">
            <div class="input-group">
                <span class="input-group-addon">From</span>
                <input type="date" wire:model.debounce.500ms="from" class="form-control">
            </div>
        </div>
        <div class="col-md-3">
            <div class="input-group">
                <span class="input-group-addon">To</span>
                <input type="date" wire:model.debounce.500ms="to" class="form-control">
            </div>
        </div>
        @if ($own_currency)
        <div class="col-md-3">
            <div class="btn-group">
                <button wire:click.prevent="$set('currency_mode', 'reporting')" class="btn btn-default {{ $currency_mode !== 'account' ? 'border-primary' : '' }}" type="button">Reporting Currency</button>
                <button wire:click.prevent="$set('currency_mode', 'account')" class="btn btn-default {{ $currency_mode === 'account' ? 'border-primary' : '' }}" type="button">{{ $own_currency->name }}</button>
            </div>
        </div>
        @endif
        <div class="col-md-3 pull-right text-right">
            <a href="{{ route('reports.account_transactions.print', $reportParams) }}" target="_blank" class="btn btn-default"><i class="fa fa-print"></i> Print</a>
            <a href="{{ route('reports.account_transactions.pdf', $reportParams) }}" target="_blank" class="btn btn-default"><i class="fa fa-file-pdf-o"></i> PDF</a>
            <a href="{{ route('reports.account_transactions.excel', $reportParams) }}" class="btn btn-default"><i class="fa fa-file-excel-o"></i> Excel</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-bordered table-sm" width="100%">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Journal#</th>
                    <th>Description</th>
                    <th>Source</th>
                    <th class="text-right">Debit</th>
                    <th class="text-right">Credit</th>
                    <th class="text-right">Balance</th>
                </tr>
            </thead>
            <tbody>
                <tr style="background-color: #f0f0f0">
                    <td colspan="6"><strong>Closing Balance ({{ \Carbon\Carbon::parse($to)->format('d M Y') }})</strong></td>
                    <td class="text-right"><strong>{{ $sym }}{{ $this->fmt($closing_balance) }}</strong></td>
                </tr>
                @forelse ($transactions as $t)
                <tr wire:key="acct-txn-{{ $loop->index }}-{{ $t['journal_number'] }}">
                    <td>{{ \Carbon\Carbon::parse($t['date'])->format('d M Y') }}</td>
                    <td>{{ $t['journal_number'] }}</td>
                    <td>{{ $t['description'] }}</td>
                    <td>
                        @if ($t['invoice_id'])
                            <a href="{{ route('invoices.show', $t['invoice_id']) }}" style="color: blue">Invoice</a>
                        @elseif ($t['bill_id'])
                            <a href="{{ route('bills.show', $t['bill_id']) }}" style="color: blue">Bill</a>
                        @elseif ($t['credit_note_id'])
                            <a href="{{ route('credit_notes.show', $t['credit_note_id']) }}" style="color: blue">Credit Note</a>
                        @elseif ($t['payment_id'])
                            <a href="{{ route('payments.show', $t['payment_id']) }}" style="color: blue">Payment</a>
                        @else
                            {{ $t['reference'] }}
                        @endif
                    </td>
                    <td class="text-right">{{ $t['debit'] != 0 ? $sym.$this->fmt($t['debit']) : '' }}</td>
                    <td class="text-right">{{ $t['credit'] != 0 ? $sym.$this->fmt($t['credit']) : '' }}</td>
                    <td class="text-right">{{ $sym }}{{ $this->fmt($t['running_balance']) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted">No transactions posted to this account in this period.</td>
                </tr>
                @endforelse
                <tr style="background-color: #f0f0f0">
                    <td colspan="6"><strong>Opening Balance ({{ \Carbon\Carbon::parse($from)->format('d M Y') }})</strong></td>
                    <td class="text-right"><strong>{{ $sym }}{{ $this->fmt($opening_balance) }}</strong></td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-right">Period Totals</th>
                    <th class="text-right">{{ $sym }}{{ $this->fmt($total_debit) }}</th>
                    <th class="text-right">{{ $sym }}{{ $this->fmt($total_credit) }}</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
