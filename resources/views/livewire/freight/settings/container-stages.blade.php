<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Container Stages</h5>
                                <small style="color: green">The lifecycle a container moves through on a Freight Job (Containers &gt; Next Stage / Record milestone), in order.
                                    Charge Types that track free time start and stop their clocks on these stages, on the date the stage is recorded with.</small>
                            </div>
                        </div>
                        <div class="panel-body">
                            <form wire:submit.prevent="save" class="mb-20">
                                <h6 class="underline mb-10"><strong>{{ $stage_id ? 'Edit Stage' : 'Add Custom Stage' }}</strong></h6>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Name <span class="required" style="color: red">*</span></label>
                                            <input type="text" class="form-control" wire:model.defer="name" placeholder="e.g. Fumigated">
                                            @error('name') <span class="text-danger error">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Comes After @unless ($editing_system)<span class="required" style="color: red">*</span>@endunless</label>
                                            @if ($editing_system)
                                                <input type="text" class="form-control" value="Fixed (system stage)" disabled>
                                            @else
                                                <select class="form-control" wire:model.defer="after_code">
                                                    <option value="">Select Stage</option>
                                                    @foreach ($stages as $s)
                                                        @if ($s->id !== $stage_id)
                                                            <option value="{{ $s->code }}">{{ $loop->iteration }}. {{ $s->name }}</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                            @endif
                                            @error('after_code') <span class="text-danger error">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Explanation</label>
                                            <textarea class="form-control" rows="1" wire:model.defer="description" placeholder="What has happened to the container at this stage"></textarea>
                                            @error('description') <span class="text-danger error">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <label>&nbsp;</label>
                                        <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-save"></i></button>
                                    </div>
                                    @if ($stage_id)
                                        <div class="col-md-1">
                                            <label>&nbsp;</label>
                                            <a href="#" wire:click.prevent="resetForm" class="btn btn-default btn-block">Cancel</a>
                                        </div>
                                    @endif
                                </div>
                                @if ($editing_system)
                                    <small style="color: #c77a00;"><i class="fa fa-cube"></i> System stage: you can rename it and change its explanation; its position is fixed because the built-in Port Storage / Demurrage / Detention clocks depend on it.</small>
                                @endif
                            </form>

                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr><th>#</th><th>Stage</th><th>What it means</th><th>Clock effect</th><th>Type</th><th>Active</th><th></th></tr>
                                </thead>
                                <tbody>
                                    @foreach ($stages as $stage)
                                        @php $effect = $effects[$stage->code] ?? []; @endphp
                                        <tr wire:key="container-stage-{{ $stage->id }}" class="{{ $stage->is_active ? '' : 'text-muted' }}">
                                            <td>{{ $loop->iteration }}</td>
                                            <td><strong>{{ $stage->name }}</strong><br><small class="text-muted">{{ $stage->code }}</small></td>
                                            <td>{{ $stage->description }}</td>
                                            <td>
                                                @foreach ($effect['starts'] ?? [] as $charge)
                                                    <span class="label label-success" title="Clock starts">&#9654; Starts {{ $charge }}</span><br>
                                                @endforeach
                                                @foreach ($effect['stops'] ?? [] as $charge)
                                                    <span class="label label-danger" title="Clock stops">&#9632; Stops {{ $charge }}</span><br>
                                                @endforeach
                                                @if (empty($effect))
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($stage->is_system)
                                                    <span class="label label-default">System</span>
                                                @else
                                                    <span class="label label-info">Custom</span>
                                                @endif
                                            </td>
                                            <td>{{ $stage->is_active ? 'Yes' : 'No' }}</td>
                                            <td style="white-space:nowrap">
                                                @if ($stage->id)
                                                    <a href="#" wire:click.prevent="edit({{ $stage->id }})" class="btn btn-xs btn-default" title="Edit"><i class="fa fa-edit"></i></a>
                                                    @unless ($stage->is_system)
                                                        <a href="#" wire:click.prevent="toggleActive({{ $stage->id }})" class="btn btn-xs btn-warning" title="{{ $stage->is_active ? 'Deactivate' : 'Activate' }}"><i class="fa fa-{{ $stage->is_active ? 'eye-slash' : 'eye' }}"></i></a>
                                                        <a href="#" wire:click.prevent="delete({{ $stage->id }})" wire:confirm="Remove this stage?" class="btn btn-xs btn-danger" title="Delete"><i class="fa fa-trash"></i></a>
                                                    @endunless
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <small class="text-muted">
                                Deactivated stages are skipped by <em>Next Stage</em> and hidden from the dropdowns, but stay on containers that already reached them.
                                Demurrage and Detention are independent estimates for different carrier billing structures &mdash; do not sum them.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
