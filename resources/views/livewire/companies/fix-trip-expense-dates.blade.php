<div>
    <div class="alert alert-warning">
        <strong><i class="fa fa-wrench"></i> Fix Trip Expense Dates</strong>
        <p class="mb-0 mt-5">
            Sets each trip expense's <strong>date</strong> to its trip's <strong>start date</strong>, for every
            trip expense across the system whose date doesn't already match (including ones with no date set).
            This is a straight bulk update — it does not go through the usual audit trail.
        </p>
    </div>

    @if ($done)
        <div class="alert alert-success">
            <strong><i class="fa fa-check"></i> Done.</strong> {{ $updatedCount }} trip expense(s) updated.
        </div>
    @endif

    @if ($pendingCount === 0)
        <div class="alert alert-info">All trip expense dates already match their trip's start date.</div>
    @else
        <div class="panel-heading" style="padding-left:0;">
            <strong>{{ $pendingCount }}</strong> trip expense(s) will be updated.
        </div>

        <div class="btn-group" role="group" style="float:right;">
            <button type="button" class="btn bg-gray btn-wide btn-rounded" wire:click="loadPreview">
                <i class="fa fa-refresh"></i> Refresh
            </button>
            <button type="button" class="btn btn-warning btn-wide btn-rounded" wire:click="run" wire:loading.attr="disabled"
                onclick="return confirm('Update {{ $pendingCount }} trip expense date(s) to match their trip start date?')">
                <i class="fa fa-wrench"></i> Run Update
            </button>
        </div>
        <br><br>
    @endif
</div>
