<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div>@include('includes.messages')</div>
                            <div class="panel-title">Bank Accounts</div>
                        </div>

                        <div class="panel-body p-20">
                            <div style="overflow-x:auto;">
                                <table class="table table-striped table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th>Bank Account</th>
                                            <th>GL Account</th>
                                            <th>Currency</th>
                                            <th>Last Reconciled Through</th>
                                            <th>Unmatched Statement Lines</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($bankAccounts as $bankAccount)
                                        <tr>
                                            <td><strong>{{ $bankAccount->name }}</strong></td>
                                            <td>
                                                @if($bankAccount->account)
                                                    {{ $bankAccount->account->code }} - {{ $bankAccount->account->name }}
                                                @else
                                                    <span class="text-danger">Not linked - edit and re-save the bank account</span>
                                                @endif
                                            </td>
                                            <td>{{ $bankAccount->currency?->name }}</td>
                                            <td>
                                                @if($bankAccount->last_reconciliation)
                                                    {{ \Carbon\Carbon::parse($bankAccount->last_reconciliation->period_end)->format('d M Y') }}
                                                    <span class="badge bg-{{ $bankAccount->last_reconciliation->status === 'completed' ? 'success' : 'warning' }} text-white">
                                                        {{ ucfirst(str_replace('_', ' ', $bankAccount->last_reconciliation->status)) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">Never reconciled</span>
                                                @endif
                                            </td>
                                            <td class="text-center">{{ $bankAccount->unmatched_count }}</td>
                                            <td class="w-10 line-height-35 table-dropdown">
                                                <div class="dropdown">
                                                    <button class="btn btn-default dropdown-toggle btn-sm" type="button" data-toggle="dropdown">
                                                        <i class="fa fa-bars"></i> <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-right">
                                                        <li>
                                                            <a href="#" data-toggle="modal" data-target="#importStatementModal"
                                                               onclick="document.getElementById('importBankAccountId').value = '{{ $bankAccount->id }}'; document.getElementById('importBankAccountName').innerText = '{{ $bankAccount->name }}';">
                                                                <i class="fa fa-upload color-info"></i> Import Statement
                                                            </a>
                                                        </li>
                                                        @if($bankAccount->account)
                                                        <li>
                                                            <a href="#" data-toggle="modal" data-target="#startReconciliationModal"
                                                               onclick="document.getElementById('startBankAccountId').value = '{{ $bankAccount->id }}'; document.getElementById('startBankAccountName').innerText = '{{ $bankAccount->name }}';">
                                                                <i class="fa fa-check-double color-success"></i> Start Reconciliation
                                                            </a>
                                                        </li>
                                                        @endif
                                                        @if($bankAccount->last_reconciliation)
                                                        <li>
                                                            <a href="{{ route('bank-reconciliations.workspace', $bankAccount->last_reconciliation->id) }}">
                                                                <i class="fa fa-eye color-default"></i> Open Latest Reconciliation
                                                            </a>
                                                        </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <img src="{{ asset('images/nodata.png') }}" style="max-width:200px;" alt="No data">
                                                <p class="text-muted mt-2">No bank accounts found.</p>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Import Statement Modal --}}
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="importStatementModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fa fa-upload"></i> Import Statement - <span id="importBankAccountName"></span>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </h4>
                </div>
                <form action="{{ route('bank-reconciliations.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="bank_account_id" id="importBankAccountId">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Statement File (CSV, OFX or MT940/.sta)</label>
                            <input type="file" class="form-control" name="file" required>
                            @error('file') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        <small class="text-muted">CSV files need a header row with at least Date and Description columns (Debit/Credit or a signed Amount column, Reference and Balance are optional).</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Cancel</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-upload"></i>Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Start Reconciliation Modal --}}
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="startReconciliationModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fa fa-check-double"></i> Start Reconciliation - <span id="startBankAccountName"></span>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </h4>
                </div>
                <form action="{{ route('bank-reconciliations.start') }}" method="POST">
                    @csrf
                    <input type="hidden" name="bank_account_id" id="startBankAccountId">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Period Start <span class="text-danger">*</span></label>
                            <input type="date" name="period_start" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Period End <span class="text-danger">*</span></label>
                            <input type="date" name="period_end" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Statement Closing Balance <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="statement_closing_balance" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Cancel</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-check-double"></i>Start</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
