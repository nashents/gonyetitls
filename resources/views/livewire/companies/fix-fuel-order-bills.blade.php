<div>
    <div class="alert alert-warning">
        <strong><i class="fa fa-wrench"></i> Fix Fuel Order Bills</strong>
        <p class="mb-0 mt-5">
            Repairs bills created when a "Once Off Buy" fuel order attached to a trip was approved, before a fix to
            that flow: the bill's <strong>authorization</strong> was left at "pending" instead of "approved", and its
            bill expense line was missing an <strong>amount</strong> (subtotal_incl / exchange_amount). Both issues
            made these bills invisible on the Horses and Transporters Profit &amp; Loss reports. This is a straight
            bulk update — it does not go through the usual audit trail. Safe to re-run: rows already fixed are
            skipped.
        </p>
    </div>

    @if ($done)
        <div class="alert alert-success">
            <strong><i class="fa fa-check"></i> Done.</strong>
            {{ $updatedAuthCount }} bill(s) marked approved, {{ $updatedAmountCount }} bill expense line(s) given an amount.
        </div>
    @endif

    @if ($pendingAuthCount === 0 && $pendingAmountCount === 0)
        <div class="alert alert-info">No affected fuel order bills found.</div>
    @else
        <div class="panel-heading" style="padding-left:0;">
            <strong>{{ $pendingAuthCount }}</strong> bill(s) stuck pending and
            <strong>{{ $pendingAmountCount }}</strong> bill expense line(s) missing an amount will be fixed.
        </div>

        <div class="btn-group" role="group" style="float:right;">
            <button type="button" class="btn bg-gray btn-wide btn-rounded" wire:click="loadPreview">
                <i class="fa fa-refresh"></i> Refresh
            </button>
            <button type="button" class="btn btn-warning btn-wide btn-rounded" wire:click="run" wire:loading.attr="disabled"
                onclick="return confirm('Fix {{ $pendingAuthCount }} bill(s) and {{ $pendingAmountCount }} bill expense line(s)?')">
                <i class="fa fa-wrench"></i> Run Fix
            </button>
        </div>
        <br><br>
    @endif
</div>
