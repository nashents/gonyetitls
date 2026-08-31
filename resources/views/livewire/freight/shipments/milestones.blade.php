<div>
    <div class="mb-10">
        <a href="#" class="btn btn-xs btn-info" data-toggle="modal" data-target="#addMilestoneModal{{ $job->id }}">
            <i class="fa fa-plus"></i> Record Milestone
        </a>
    </div>

    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Shipment</th>
                <th>Container</th>
                <th>Milestone</th>
                <th>Status</th>
                <th>Planned</th>
                <th>Actual</th>
                <th>Customer Visible</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($milestones as $milestone)
                <tr>
                    <td>{{ $milestone->shipment?->shipment_number }}</td>
                    <td>{{ $milestone->shipping_container?->container_number }}</td>
                    <td>{{ $milestone->milestone_name }}</td>
                    <td>
                        @if ($milestone->status == 'completed')
                            <span class="label label-success label-wide">Completed</span>
                        @else
                            <span class="label label-warning label-wide">{{ ucfirst($milestone->status) }}</span>
                        @endif
                    </td>
                    <td>{{ $milestone->planned_at?->format('d M Y H:i') }}</td>
                    <td>{{ $milestone->actual_at?->format('d M Y H:i') }}</td>
                    <td>
                        <a href="#" wire:click.prevent="toggleVisibility({{ $milestone->id }})" title="{{ $milestone->is_customer_visible ? 'Visible to customer - click to hide' : 'Hidden from customer - click to show' }}">
                            <i class="fa {{ $milestone->is_customer_visible ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                        </a>
                    </td>
                    <td>
                        @if ($milestone->status != 'completed')
                            <a href="#" wire:click.prevent="complete({{ $milestone->id }})" class="btn btn-xs btn-success"><i class="fa fa-check"></i> Complete</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center">No milestones recorded for this job yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Record Milestone Modal -->
    <div class="modal fade" id="addMilestoneModal{{ $job->id }}" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form wire:submit.prevent="store">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        <h4 class="modal-title">Record Milestone</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Shipment <span class="required text-danger">*</span></label>
                            <select class="form-control" wire:model="shipment_id">
                                <option value="">Select Shipment</option>
                                @foreach ($job->shipments as $shipment)
                                    <option value="{{ $shipment->id }}">{{ $shipment->shipment_number }}</option>
                                @endforeach
                            </select>
                            @error('shipment_id') <span class="text-danger error">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Milestone Code <span class="required text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="milestone_code" placeholder="e.g. customs_entry_submitted">
                            @error('milestone_code') <span class="text-danger error">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Milestone Name <span class="required text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model="milestone_name" placeholder="e.g. Customs Entry Submitted">
                            @error('milestone_name') <span class="text-danger error">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>Planned Date</label>
                            <input type="datetime-local" class="form-control" wire:model="planned_at">
                        </div>
                        <div class="checkbox">
                            <label><input type="checkbox" wire:model="is_customer_visible"> Visible to customer in the freight portal</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn bg-gray" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        window.addEventListener('hide-addMilestoneModal-{{ $job->id }}', event => {
            $('#addMilestoneModal{{ $job->id }}').modal('hide');
        })
    </script>
</div>
