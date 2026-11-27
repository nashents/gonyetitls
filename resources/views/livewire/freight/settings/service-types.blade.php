<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Freight Service Types</h5>
                                <small style="color: green">The service types selectable when creating a Freight Job (e.g. Sea Freight, Air Freight, Customs Clearing Only). Only Active types are offered on new jobs.</small>
                            </div>
                        </div>
                        <div class="panel-body">
                            <form wire:submit.prevent="save" class="mb-20">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Name <span class="required text-danger">*</span></label>
                                            <input type="text" class="form-control" wire:model.defer="name">
                                            @error('name') <span class="text-danger error">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Description</label>
                                            <input type="text" class="form-control" wire:model.defer="description">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Status <span class="required text-danger">*</span></label>
                                            <select class="form-control" wire:model.defer="is_active">
                                                <option value="1">Active</option>
                                                <option value="0">Inactive</option>
                                            </select>
                                            @error('is_active') <span class="text-danger error">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <label>&nbsp;</label>
                                        <button type="submit" class="btn btn-primary btn-block" title="Save"><i class="fa fa-save"></i></button>
                                    </div>
                                    @if ($service_type_id)
                                        <div class="col-md-1">
                                            <label>&nbsp;</label>
                                            <a href="#" wire:click.prevent="resetForm" class="btn btn-default btn-block" title="Cancel"><i class="fa fa-times"></i></a>
                                        </div>
                                    @endif
                                </div>
                            </form>

                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr><th>Name</th><th>Description</th><th>Jobs</th><th>Status</th><th></th></tr>
                                </thead>
                                <tbody>
                                    @forelse ($serviceTypes as $type)
                                        <tr wire:key="freight-service-type-{{ $type->id }}" class="{{ $type->is_active ? '' : 'text-muted' }}">
                                            <td>{{ $type->name }}</td>
                                            <td>{{ $type->description }}</td>
                                            <td>{{ $type->freight_jobs_count }}</td>
                                            <td><span class="label label-{{ $type->is_active ? 'success' : 'default' }}">{{ $type->is_active ? 'Active' : 'Inactive' }}</span></td>
                                            <td style="white-space:nowrap">
                                                <a href="#" wire:click.prevent="edit({{ $type->id }})" class="btn btn-xs btn-default" title="Edit"><i class="fa fa-edit"></i></a>
                                                <a href="#" wire:click.prevent="toggleActive({{ $type->id }})" class="btn btn-xs btn-warning" title="{{ $type->is_active ? 'Deactivate (stop offering on new jobs)' : 'Activate' }}"><i class="fa fa-{{ $type->is_active ? 'eye-slash' : 'eye' }}"></i></a>
                                                <a href="#" wire:click.prevent="delete({{ $type->id }})" wire:confirm="Remove service type {{ $type->name }}?" class="btn btn-xs btn-danger" title="Delete"><i class="fa fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center">No service types configured yet.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                            @include('livewire.freight.partials.pagination', ['paginator' => $serviceTypes])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
