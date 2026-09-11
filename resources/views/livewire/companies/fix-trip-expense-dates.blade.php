<div>
    <div class="alert alert-warning">
        <strong><i class="fa fa-wrench"></i> Fix Trip Expense &amp; Bill Dates</strong>
        <p class="mb-0 mt-5">
            Sets each trip expense's <strong>date</strong>, and the <strong>bill_date</strong> of any bill linked to
            it, to the trip's <strong>start date</strong> — for every row across the system whose date doesn't
            already match (including ones with no date set). This is a straight bulk update — it does not go
            through the usual audit trail. Safe to re-run: rows already matching are skipped.
        </p>
    </div>

    @if ($done)
        <div class="alert alert-success">
            <strong><i class="fa fa-check"></i> Done.</strong>
            {{ $updatedCount }} trip expense(s) and {{ $updatedBillCount }} bill(s) updated.
        </div>
    @endif

    @if ($pendingCount === 0 && $pendingBillCount === 0)
        <div class="alert alert-info">All trip expense and bill dates already match their trip's start date.</div>
    @else
        <div class="panel-heading" style="padding-left:0;">
            <strong>{{ $pendingCount }}</strong> trip expense(s) and <strong>{{ $pendingBillCount }}</strong>
            bill(s) will be updated.
        </div>

        <div class="btn-group" role="group" style="float:right;">
            <button type="button" class="btn bg-gray btn-wide btn-rounded" wire:click="loadPreview">
                <i class="fa fa-refresh"></i> Refresh
            </button>
            <button type="button" class="btn btn-warning btn-wide btn-rounded" wire:click="run" wire:loading.attr="disabled"
                onclick="return confirm('Update {{ $pendingCount }} trip expense date(s) and {{ $pendingBillCount }} bill date(s) to match their trip start date?')">
                <i class="fa fa-wrench"></i> Run Update
            </button>
        </div>
        <br><br>
    @endif
</div>
