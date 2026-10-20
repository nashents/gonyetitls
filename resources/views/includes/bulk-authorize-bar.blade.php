{{-- Bulk authorization bar for pending lists. Component needs $selectedRows + showBulkAuthorize(). $label e.g. "GRV(s)". --}}
@if (!empty($selectedRows))
    <div class="row" style="margin-bottom:12px;">
        <div class="col-md-12">
            <button type="button" wire:click="showBulkAuthorize()" class="btn btn-default border-primary btn-rounded btn-wide">
                <i class="fa fa-gavel"></i> Bulk Authorize
            </button>
            <span style="margin-left:10px;">{{ count($selectedRows) }} {{ $label }} selected.</span>
            <a href="#" wire:click.prevent="$set('selectedRows', [])" style="margin-left:10px;">Clear selection</a>
        </div>
    </div>
@endif
