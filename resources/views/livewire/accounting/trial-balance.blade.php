<div>
    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label class="small text-muted">From</label>
                        <input type="date" class="form-control" wire:model.debounce.500ms="date_from" />
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label class="small text-muted">To</label>
                        <input type="date" class="form-control" wire:model.debounce.500ms="date_to" />
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group mb-0">
                        <label class="small text-muted">Search Account</label>
                        <input type="text" class="form-control" wire:model.debounce.500ms="search" placeholder="Search..." />
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-check mt-3">
                        <input type="checkbox" class="form-check-input" id="hide_zero" wire:model="hide_zero_balances" />
                        <label class="form-check-label small" for="hide_zero">Hide zero balances</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Balance Status --}}
    <div class="mb-3">
        @if ($isBalanced)
            <span class="badge badge-success px-3 py-2">
                <i class="fas fa-check-circle"></i> Trial Balance is Balanced
            </span>
        @else
            <span class="badge badge-danger px-3 py-2">
                <i class="fas fa-exclamation-triangle"></i>
                Out of Balance by {{ number_format(abs($totals['debit'] - $totals['credit']), 2) }}
            </span>
        @endif
    </div>

    {{-- Trial Balance Table --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-balance-scale"></i> Trial Balance</h5>
            <small class="text-muted">
                {{ \Carbon\Carbon::parse($date_from)->format('d M Y') }} —
                {{ \Carbon\Carbon::parse($date_to)->format('d M Y') }}
            </small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width: 40%">Account</th>
                            <th style="width: 20%">Type</th>
                            <th style="width: 15%" class="text-right">Debit</th>
                            <th style="width: 15%" class="text-right">Credit</th>
                            <th style="width: 10%" class="text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($groupedLines as $groupName => $lines)
                            {{-- Group Header --}}
                            <tr style="background-color: #f8f9fa; border-top: 2px solid #dee2e6;">
                                <td colspan="5" class="font-weight-bold text-uppercase small py-2 pl-3">
                                    <i class="fas fa-folder text-primary mr-1"></i>
                                    {{ $groupName }}
                                </td>
                            </tr>

                            {{-- Account Lines --}}
                            @foreach ($lines as $line)
                            <tr>
                                <td class="pl-4">{{ $line->account_name }}</td>
                                <td class="small text-muted">{{ $line->account_type_name }}</td>
                                <td class="text-right">
                                    @if ($line->total_debit > 0)
                                        {{ number_format($line->total_debit, 2) }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if ($line->total_credit > 0)
                                        {{ number_format($line->total_credit, 2) }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-right font-weight-bold">
                                    @php $balance = $line->total_debit - $line->total_credit; @endphp
                                    <span class="{{ $balance < 0 ? 'text-danger' : ($balance > 0 ? 'text-success' : 'text-muted') }}">
                                        {{ $balance < 0 ? '(' . number_format(abs($balance), 2) . ')' : number_format($balance, 2) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach

                            {{-- Group Subtotal --}}
                            <tr style="background-color: #f1f3f5;">
                                <td colspan="2" class="text-right small font-weight-bold pr-3">
                                    {{ $groupName }} Total
                                </td>
                                <td class="text-right font-weight-bold small">
                                    {{ number_format($lines->sum('total_debit'), 2) }}
                                </td>
                                <td class="text-right font-weight-bold small">
                                    {{ number_format($lines->sum('total_credit'), 2) }}
                                </td>
                                <td class="text-right font-weight-bold small">
                                    @php $groupBalance = $lines->sum('total_debit') - $lines->sum('total_credit'); @endphp
                                    <span class="{{ $groupBalance < 0 ? 'text-danger' : 'text-success' }}">
                                        {{ $groupBalance < 0 ? '(' . number_format(abs($groupBalance), 2) . ')' : number_format($groupBalance, 2) }}
                                    </span>
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No journal entries found for the selected period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    {{-- Grand Totals --}}
                    <tfoot>
                        <tr style="border-top: 3px double #343a40;">
                            <td colspan="2" class="font-weight-bold text-uppercase">Grand Total</td>
                            <td class="text-right font-weight-bold">
                                {{ number_format($totals['debit'], 2) }}
                            </td>
                            <td class="text-right font-weight-bold">
                                {{ number_format($totals['credit'], 2) }}
                            </td>
                            <td class="text-right font-weight-bold">
                                @if ($isBalanced)
                                    <span class="text-success">0.00</span>
                                @else
                                    <span class="text-danger">
                                        {{ number_format(abs($totals['debit'] - $totals['credit']), 2) }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>