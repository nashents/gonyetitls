<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div>
                                @include('includes.messages')
                            </div>
                            <div class="panel-title">
                                <i class="fas fa-tasks"></i> Bulk Catch-up Payments
                            </div>
                        </div>
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">

                            <p class="text-muted">
                                Settle every unpaid/partial invoice and bill in a date range against a dedicated
                                "Opening Balance Equity" clearing account — no real cash is recorded as moving.
                                Use this once to clean up historical books before recording real payments going forward.
                            </p>

                            <form wire:submit.prevent="runPreview">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>From (optional)</label>
                                            <input type="date" class="form-control" wire:model="from">
                                            @error('from') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Settle everything up to</label>
                                            <input type="date" class="form-control" wire:model="until">
                                            @error('until') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Apply to</label><br>
                                            <label class="checkbox-inline"><input type="checkbox" value="invoices" wire:model="types"> Invoices</label>
                                            &nbsp;&nbsp;
                                            <label class="checkbox-inline"><input type="checkbox" value="bills" wire:model="types"> Bills</label>
                                            @error('types') <span class="error d-block" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button type="submit" class="btn bg-info btn-rounded btn-block" wire:loading.attr="disabled" wire:target="runPreview">
                                                <i class="fa fa-search"></i> Preview
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            @if ($preview)
                                <hr>
                                <h5>Preview — nothing has been written yet</h5>
                                <p class="text-muted">Review the numbers below, then confirm to run this in the background.</p>

                                @foreach (['invoices' => 'Invoices', 'bills' => 'Bills'] as $key => $label)
                                    @if ($preview[$key])
                                        <table class="table table-striped table-bordered table-sm">
                                            <thead><tr><th colspan="2" class="th-sm">{{ $label }}</th></tr></thead>
                                            <tbody>
                                                <tr><td>Total in range</td><td>{{ $preview[$key]['total'] }}</td></tr>
                                                <tr><td>Will be settled</td><td>{{ $preview[$key]['to_settle'] }}</td></tr>
                                                <tr><td>Need approval first (approved automatically before paying)</td><td>{{ $preview[$key]['needs_approval'] }}</td></tr>
                                                <tr><td>Missing currency (will default to USD)</td><td>{{ $preview[$key]['missing_currency'] }}</td></tr>
                                                <tr><td>Already zero/negative balance (skipped)</td><td>{{ $preview[$key]['already_settled'] }}</td></tr>
                                                <tr><td>Unreadable balance/total (skipped — needs manual review)</td><td>{{ $preview[$key]['data_issues'] }}</td></tr>
                                                <tr>
                                                    <td>Totals to be settled, by currency</td>
                                                    <td>
                                                        @forelse ($preview[$key]['totals_by_currency'] as $currencyId => $total)
                                                            {{ optional(\App\Models\Currency::find($currencyId))->name ?? $currencyId }}: {{ number_format($total, 2) }}<br>
                                                        @empty
                                                            —
                                                        @endforelse
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    @endif
                                @endforeach

                                <button type="button" class="btn bg-danger btn-rounded" wire:click="showConfirm">
                                    <i class="fa fa-check"></i> Confirm &amp; Run
                                </button>
                            @endif

                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="bulkCatchUpConfirmModal" tabindex="-1" role="dialog" aria-labelledby="bulkCatchUpConfirmModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="bulkCatchUpConfirmModalLabel">
                        <i class="fas fa-exclamation-triangle"></i> Confirm bulk catch-up
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </h4>
                </div>
                <div class="modal-body">
                    <p>
                        This will post real payments and journal entries for every unpaid/partial invoice
                        and bill in the previewed range, marking them as paid against a new equity
                        clearing account. This is <strong>not easily undone</strong>.
                    </p>
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="bulkCatchUpConfirmCheck" wire:model="confirmed">
                        <label class="form-check-label" for="bulkCatchUpConfirmCheck">I understand and want to proceed.</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i> Cancel</button>
                        <button type="button" class="btn bg-danger btn-wide btn-rounded" wire:click="run"><i class="fa fa-play"></i> Run</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
