{{--
    Bulk authorization modal for pending lists. Component needs $selectedRows, $bulk_authorize,
    $bulk_comments and bulkAuthorize(); opened/closed via show-/hide-bulkyAuthorizationModal (layouts).
    $label e.g. "GRV(s)"; optional $note shown above the form.
--}}
<div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="bulkyAuthorizationModal" tabindex="-1" role="dialog" aria-labelledby="bulkAuthLabel" data-backdrop-color="blue">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="bulkAuthLabel"><i class="fas fa-gavel"></i> Bulk Authorize {{ $label }} <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
            </div>
            <form wire:submit.prevent="bulkAuthorize()">
                <div class="modal-body">
                    <p><strong>{{ count($selectedRows ?? []) }}</strong> {{ $label }} selected. Each one is authorized separately; any that can't be approved are skipped and listed with the reason.</p>
                    @if (!empty($note))
                        <p class="text-muted">{{ $note }}</p>
                    @endif
                    <div class="form-group">
                        <label for="bulk_authorize">Decision</label>
                        <select id="bulk_authorize" class="form-control" wire:model.defer="bulk_authorize">
                            <option value="">Select Decision</option>
                            <option value="approved">Approve</option>
                            <option value="rejected">Reject</option>
                        </select>
                        @error('bulk_authorize') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="bulk_comments">Comment</label>
                        <textarea id="bulk_comments" class="form-control" wire:model.defer="bulk_comments" cols="30" rows="3"></textarea>
                    </div>
                    <div class="form-group">
                        <input type="checkbox" wire:model.defer="stay_on_page" class="line-style" id="stay_on_page_bulk" />
                        <label for="stay_on_page_bulk" class="radio-label">Stay on this page after authorizing (don't redirect to Approved/Rejected)</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded" wire:loading.attr="disabled" wire:target="bulkAuthorize"><i class="fa fa-gavel"></i>Authorize Selected</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
