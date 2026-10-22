{{-- Authorize modals for freight jobs (AuthorizesFreightJobs trait). Opened via the app-wide
     show-/hide-authorizationModal and show-/hide-bulkyAuthorizationModal events (layouts/app). --}}
<div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal text-left" id="authorizationModal" tabindex="-1" role="dialog" aria-labelledby="freightAuthLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="freightAuthLabel"><i class="fas fa-gavel"></i> Authorize Freight Job {{ $authorizing_job_number }} <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
            </div>
            <form wire:submit.prevent="saveAuthorization()">
                <div class="modal-body">
                    <p class="text-muted">Approving a draft job moves it to Confirmed. Until a job is approved it can't move past Draft or raise bills and invoices.</p>
                    <div class="form-group">
                        <label>Decision</label>
                        <select class="form-control" wire:model.defer="authorize">
                            <option value="">Select Decision</option>
                            <option value="approved">Approve</option>
                            <option value="rejected">Reject</option>
                        </select>
                        @error('authorize') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label>Comment <small class="text-muted">(required when rejecting)</small></label>
                        <textarea class="form-control" wire:model.defer="comments" cols="30" rows="3"></textarea>
                        @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded" wire:loading.attr="disabled" wire:target="saveAuthorization"><i class="fa fa-gavel"></i>Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal text-left" id="bulkyAuthorizationModal" tabindex="-1" role="dialog" aria-labelledby="freightBulkAuthLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="freightBulkAuthLabel"><i class="fas fa-gavel"></i> Bulk Authorize Freight Jobs <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
            </div>
            <form wire:submit.prevent="bulkAuthorize()">
                <div class="modal-body">
                    <p><strong>{{ count($selectedRows ?? []) }}</strong> freight job(s) selected. Only jobs still pending are changed.</p>
                    <div class="form-group">
                        <label>Decision</label>
                        <select class="form-control" wire:model.defer="bulk_authorize">
                            <option value="">Select Decision</option>
                            <option value="approved">Approve</option>
                            <option value="rejected">Reject</option>
                        </select>
                        @error('bulk_authorize') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label>Comment</label>
                        <textarea class="form-control" wire:model.defer="bulk_comments" cols="30" rows="3"></textarea>
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
