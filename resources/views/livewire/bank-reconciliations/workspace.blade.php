<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div>@include('includes.messages')</div>
                            <div class="panel-title d-flex align-items-center justify-content-between">
                                <span>
                                    {{ $reconciliation->bank_account->name }}
                                    &mdash; {{ \Carbon\Carbon::parse($reconciliation->period_start)->format('d M Y') }}
                                    to {{ \Carbon\Carbon::parse($reconciliation->period_end)->format('d M Y') }}
                                    <span class="badge bg-{{ $reconciliation->status === 'completed' ? 'success' : 'warning' }} text-white">
                                        {{ ucfirst($reconciliation->status) }}
                                    </span>
                                </span>
                                <span>
                                    <a href="{{ route('bank-reconciliations.statement', $reconciliation->id) }}" class="btn btn-default btn-sm" target="_blank">
                                        <i class="fa fa-file-text"></i> Statement
                                    </a>
                                    <a href="{{ route('bank-reconciliations.statement.pdf', $reconciliation->id) }}" class="btn btn-default btn-sm">
                                        <i class="fa fa-file-pdf-o"></i> PDF
                                    </a>
                                    @if($reconciliation->status !== 'completed')
                                    <button type="button" class="btn btn-default btn-sm" wire:click="autoMatch" wire:loading.attr="disabled">
                                        <i class="fa fa-magic"></i> Auto-Match
                                    </button>
                                    <button type="button" class="btn btn-info btn-sm" wire:click="matchSelected" wire:loading.attr="disabled" @if(!$selectedStatementLineId || !$selectedBookLineId) disabled @endif>
                                        <i class="fa fa-link"></i> Match Selected
                                    </button>
                                    <button type="button" class="btn btn-success btn-sm" wire:click="complete" wire:loading.attr="disabled"
                                            onclick="return confirm('Complete this reconciliation? This locks every matched ledger line.')">
                                        <i class="fa fa-check-double"></i> Complete
                                    </button>
                                    @else
                                    <button type="button" class="btn btn-warning btn-sm" wire:click="reopen" wire:loading.attr="disabled"
                                            onclick="return confirm('Reopen this reconciliation? Matched ledger lines will be unlocked again.')">
                                        <i class="fa fa-unlock"></i> Reopen
                                    </button>
                                    @endif
                                </span>
                            </div>
                        </div>

                        <div class="panel-body p-20">
                            {{-- Summary --}}
                            <div class="row mb-3">
                                <div class="col-md-3"><strong>Statement Balance:</strong> {{ number_format($reconciliation->statement_closing_balance, 2) }}</div>
                                <div class="col-md-3"><strong>Book Balance:</strong> {{ number_format($reconciliation->book_closing_balance, 2) }}</div>
                                @if($reconciliation->status === 'completed')
                                <div class="col-md-3"><strong>Adjusted Bank:</strong> {{ number_format($reconciliation->adjusted_bank_balance, 2) }}</div>
                                <div class="col-md-3"><strong>Adjusted Book:</strong> {{ number_format($reconciliation->adjusted_book_balance, 2) }}</div>
                                @endif
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Bank Statement Lines</h5>
                                    <div style="overflow-x:auto; max-height:520px; overflow-y:auto;">
                                        <table class="table table-striped table-bordered table-sm">
                                            <thead>
                                                <tr>
                                                    <th></th>
                                                    <th>Date</th>
                                                    <th>Description</th>
                                                    <th class="text-right">Amount</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($statementLines as $line)
                                                <tr class="{{ $selectedStatementLineId == $line->id ? 'table-info' : '' }}">
                                                    <td>
                                                        @if($line->status === 'unmatched')
                                                        <input type="radio" wire:click="selectStatementLine({{ $line->id }})" @checked($selectedStatementLineId == $line->id)>
                                                        @endif
                                                    </td>
                                                    <td>{{ \Carbon\Carbon::parse($line->transaction_date)->format('d M Y') }}</td>
                                                    <td>{{ \Illuminate\Support\Str::limit($line->description, 40) }}</td>
                                                    <td class="text-right">{{ number_format($line->amount, 2) }}</td>
                                                    <td>
                                                        <span class="badge bg-{{ ['unmatched' => 'secondary', 'matched' => 'info', 'reconciled' => 'success'][$line->status] ?? 'secondary' }} text-white">
                                                            {{ ucfirst($line->status) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @if($line->status === 'matched')
                                                        <a href="#" wire:click.prevent="unmatch({{ $line->id }})" class="text-danger"><i class="fa fa-unlink"></i> Unmatch</a>
                                                        @elseif($line->status === 'unmatched')
                                                        <a href="#" wire:click.prevent="openAdjustmentModal({{ $line->id }})"><i class="fa fa-plus-square-o"></i> Adjustment</a>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="6" class="text-center text-muted py-3">No statement lines for this period.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <h5>Book (Ledger) Lines</h5>
                                    <div style="overflow-x:auto; max-height:520px; overflow-y:auto;">
                                        <table class="table table-striped table-bordered table-sm">
                                            <thead>
                                                <tr>
                                                    <th></th>
                                                    <th>Description</th>
                                                    <th class="text-right">Amount</th>
                                                    <th>Cleared</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($bookLines as $line)
                                                <tr class="{{ $selectedBookLineId == $line->id ? 'table-info' : '' }}">
                                                    <td>
                                                        @if(!$line->cleared_at)
                                                        <input type="radio" wire:click="selectBookLine({{ $line->id }})" @checked($selectedBookLineId == $line->id)>
                                                        @endif
                                                    </td>
                                                    <td>{{ \Illuminate\Support\Str::limit($line->description, 40) }}</td>
                                                    <td class="text-right">{{ number_format((float) $line->debit - (float) $line->credit, 2) }}</td>
                                                    <td>
                                                        @if($line->cleared_at)
                                                        <span class="badge bg-success text-white"><i class="fa fa-check"></i></span>
                                                        @else
                                                        <span class="text-muted">Outstanding</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @empty
                                                <tr><td colspan="4" class="text-center text-muted py-3">No ledger lines.</td></tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Adjustment Modal --}}
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="adjustmentModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fa fa-plus-square-o"></i> Record Adjustment Entry
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </h4>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Use this for statement lines that never made it into the books - bank charges, interest, direct debits, etc. This posts the missing journal entry and matches it in one step.</p>
                    <div class="form-group">
                        <label>Contra Account <span class="text-danger">*</span></label>
                        <select wire:model.defer="adjustmentContraAccountId" class="form-control @error('adjustmentContraAccountId') is-invalid @enderror">
                            <option value="">Select an account...</option>
                            @foreach($contraAccounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                        @error('adjustmentContraAccountId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" wire:model.defer="adjustmentDescription" class="form-control" placeholder="Optional - defaults to the statement line's description">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Cancel</button>
                    <button type="button" class="btn bg-success btn-wide btn-rounded" wire:click="createAdjustment" wire:loading.attr="disabled">
                        <span wire:loading wire:target="createAdjustment"><i class="fa fa-spinner fa-spin"></i></span>
                        Record & Match
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.addEventListener('show-adjustmentModal', () => $('#adjustmentModal').modal('show'));
    window.addEventListener('hide-adjustmentModal', () => $('#adjustmentModal').modal('hide'));
</script>
@endpush
