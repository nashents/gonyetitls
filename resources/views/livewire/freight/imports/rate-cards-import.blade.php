<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Import Rate Cards</h5>
                                <small style="color: green">Bulk-upload buy/sell rate cards from Excel. Unresolvable vendors/customers/currencies are skipped and reported below, not silently dropped.</small>
                            </div>
                        </div>
                        <div class="panel-body">
                            <a href="#" wire:click.prevent="downloadTemplate" class="btn btn-xs btn-default mb-20"><i class="fa fa-download"></i> Download Template</a>

                            <form wire:submit.prevent="import">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label>Excel File (.xls, .xlsx)</label>
                                            <input type="file" class="form-control" wire:model="file">
                                            @error('file') <span class="text-danger error">{{ $message }}</span> @enderror
                                            <div wire:loading wire:target="file">Uploading...</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label>&nbsp;</label>
                                        <button type="submit" class="btn btn-primary btn-block" wire:loading.attr="disabled" wire:target="import">
                                            <i class="fa fa-upload"></i> Import
                                        </button>
                                    </div>
                                </div>
                            </form>

                            @if ($summary)
                                <div class="alert {{ $summary['rows_skipped'] ? 'alert-warning' : 'alert-success' }} mt-20">
                                    {{ $summary['rows_created'] }} rate card(s) imported, {{ $summary['rows_skipped'] }} row(s) skipped.
                                </div>
                            @endif

                            @if (!empty($skippedRows))
                                <h6 class="underline mt-20 mb-10"><strong>Skipped Rows</strong></h6>
                                <table class="table table-condensed table-bordered">
                                    <thead>
                                        <tr><th>Row</th><th>Reason</th><th>Details</th></tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($skippedRows as $skipped)
                                            <tr>
                                                <td>{{ $skipped['row'] }}</td>
                                                <td>{{ ucwords(str_replace('_', ' ', $skipped['reason'])) }}</td>
                                                <td>{{ collect($skipped)->except(['row', 'reason'])->map(fn($v, $k) => "{$k}: {$v}")->implode(', ') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif

                            <h6 class="underline mt-20 mb-10"><strong>Recent Imports</strong></h6>
                            <table class="table table-condensed table-bordered">
                                <thead>
                                    <tr><th>File</th><th>By</th><th>Created</th><th>Skipped</th><th>Status</th><th>When</th></tr>
                                </thead>
                                <tbody>
                                    @forelse ($recentLogs as $log)
                                        <tr>
                                            <td>{{ $log->original_filename }}</td>
                                            <td>{{ $log->user?->name }}</td>
                                            <td>{{ $log->rows_created }}</td>
                                            <td>{{ $log->rows_skipped }}</td>
                                            <td><span class="label label-{{ $log->status === 'failed' ? 'danger' : ($log->rows_skipped ? 'warning' : 'success') }}">{{ ucwords(str_replace('_', ' ', $log->status)) }}</span></td>
                                            <td>{{ $log->created_at->format('d M Y H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center">No imports run yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
