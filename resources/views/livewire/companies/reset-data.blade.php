<div>
    <div class="alert alert-danger">
        <strong><i class="fa fa-exclamation-triangle"></i> Start Afresh — Wipe Captured Data</strong>
        <p class="mb-0 mt-5">
            This permanently deletes <strong>all transactional/captured data system-wide</strong> — trips, invoices,
            quotations, receipts, payments, journal entries, bank statements, attendance, payroll, SHEQ records,
            inventory movements, tickets, and similar activity — across the whole system, not just one company.
        </p>
        <p class="mb-0 mt-5">
            Base modules are <strong>not</strong> touched: vehicles, trailers, horses, drivers, employees, customers,
            vendors, consignees, loading/offloading points, destinations, and all configuration/lookup data (roles,
            chart of accounts, vehicle/trailer types, currencies, etc.).
        </p>
        <p class="mb-0 mt-5">
            The chart of accounts, bank accounts, and similar master records are kept, but their <strong>stored
            balances are reset to 0</strong> (e.g. account/bank balances, fuel balances, tyre balances) since the
            transactions behind those balances are being wiped.
        </p>
        <p class="mb-0 mt-5">
            The dashboard's cached KPIs (revenue, pending authorizations, fuel stock, etc.) are also cleared so the
            dashboard reflects the reset immediately instead of showing stale figures for up to 10 minutes.
        </p>
        <p class="mb-0 mt-5"><strong>This action cannot be undone. Take a database backup before proceeding.</strong></p>
    </div>

    @if ($done)
        <div class="alert alert-success">
            <strong><i class="fa fa-check"></i> Reset complete.</strong> Summary below and in storage/logs/laravel.log.
        </div>
        <div class="panel-body p-0" style="overflow-x:auto; max-height: 300px; overflow-y: auto;">
            <table class="table table-striped table-bordered table-sm">
                <thead>
                    <tr><th>Table</th><th>Result</th></tr>
                </thead>
                <tbody>
                    @foreach ($results as $table => $result)
                        <tr>
                            <td>{{ $table }}</td>
                            <td class="{{ str_starts_with($result, 'FAILED') ? 'text-danger' : 'text-success' }}">{{ $result }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <hr>
    @endif

    @if (!empty($unclassified))
        <div class="alert alert-warning">
            <strong>Note:</strong> the tables below have data but aren't classified as either "wipe" or "keep" yet, so they were left untouched. Ask a developer to review them if they should be included.
            <ul class="mb-0">
                @foreach ($unclassified as $table => $count)
                    <li>{{ $table }}: {{ $count }} rows</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($total === 0 && empty($balances))
        <div class="alert alert-info">There is currently no captured data or non-zero balances to reset.</div>
    @else
        @if ($total > 0)
            <div class="panel-heading" style="padding-left:0;">
                <strong>{{ $total }}</strong> rows across {{ count($preview) }} modules will be deleted:
            </div>
            <div class="panel-body p-0" style="overflow-x:auto; max-height: 350px; overflow-y: auto;">
                @foreach ($preview as $group => $data)
                    <details class="mb-10">
                        <summary><strong>{{ $group }}</strong> ({{ $data['total'] }} rows)</summary>
                        <table class="table table-striped table-bordered table-sm">
                            <tbody>
                                @foreach ($data['tables'] as $table => $count)
                                    <tr><td>{{ $table }}</td><td class="w-10">{{ $count }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </details>
                @endforeach
            </div>
        @endif

        @if (!empty($balances))
            <div class="panel-heading" style="padding-left:0;">
                These stored balances will be reset to 0 (rows are kept, only the balance changes):
            </div>
            <div class="panel-body p-0" style="overflow-x:auto; max-height: 200px; overflow-y: auto;">
                <table class="table table-striped table-bordered table-sm">
                    <tbody>
                        @foreach ($balances as $column => $count)
                            <tr><td>{{ $column }}</td><td class="w-10">{{ $count }} row(s)</td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <hr>

        <form wire:submit.prevent="runReset">
            <div class="form-group">
                <label for="confirmText">Type <strong>RESET</strong> to confirm you want to permanently delete the data (and zero the balances) above</label>
                <input type="text" id="confirmText" class="form-control" wire:model.defer="confirmText" autocomplete="off">
                @error('confirmText') <span class="text-danger error">{{ $message }}</span> @enderror
            </div>
            <div class="btn-group" role="group" style="float:right;">
                <button type="button" class="btn bg-gray btn-wide btn-rounded" wire:click="loadPreview">
                    <i class="fa fa-refresh"></i> Refresh
                </button>
                <button type="submit" class="btn btn-danger btn-wide btn-rounded">
                    <i class="fa fa-trash"></i> Permanently Wipe Data
                </button>
            </div>
            <br><br>
        </form>
    @endif
</div>
