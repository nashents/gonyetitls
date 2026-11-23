<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Customs Offices</h5>
                                <small style="color: green">The offices selectable on a Customs Declaration. Offices added from the declaration form appear here too.</small>
                            </div>
                        </div>
                        <div class="panel-body">
                            <form wire:submit.prevent="save" class="mb-20">
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label>Name <span class="required" style="color: red">*</span></label>
                                            <input type="text" class="form-control" wire:model.defer="name" placeholder="e.g. Beitbridge">
                                            @error('name') <span class="text-danger error">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Country</label>
                                            <select class="form-control" wire:model.defer="country_id">
                                                <option value="">Select Country</option>
                                                @foreach ($countries as $country)
                                                    <option value="{{ $country->id }}">{{ $country->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('country_id') <span class="text-danger error">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-{{ $office_id ? '1' : '3' }}">
                                        <label>&nbsp;</label>
                                        <button type="submit" class="btn btn-primary btn-block" title="Save"><i class="fa fa-save"></i></button>
                                    </div>
                                    @if ($office_id)
                                        <div class="col-md-2">
                                            <label>&nbsp;</label>
                                            <a href="#" wire:click.prevent="resetForm" class="btn btn-default btn-block">Cancel</a>
                                        </div>
                                    @endif
                                </div>
                                @if ($office_id)
                                    <div style="font-size:12px; line-height:1.4; color:#31708f;"><i class="fa fa-info-circle"></i> Renaming also updates the declarations that use this office.</div>
                                @endif
                            </form>

                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr><th>Name</th><th>Country</th><th>Declarations</th><th>Active</th><th></th></tr>
                                </thead>
                                <tbody>
                                    @forelse ($offices as $office)
                                        <tr wire:key="customs-office-{{ $office->id }}" class="{{ $office->is_active ? '' : 'text-muted' }}">
                                            <td>{{ $office->name }}</td>
                                            <td>{{ $office->country->name ?? '—' }}</td>
                                            <td>{{ $usage[$office->name] ?? 0 }}</td>
                                            <td>
                                                <span class="label label-{{ $office->is_active ? 'success' : 'default' }}">{{ $office->is_active ? 'Yes' : 'No' }}</span>
                                            </td>
                                            <td style="white-space:nowrap">
                                                <a href="#" wire:click.prevent="edit({{ $office->id }})" class="btn btn-xs btn-default" title="Edit"><i class="fa fa-edit"></i></a>
                                                <a href="#" wire:click.prevent="toggleActive({{ $office->id }})" class="btn btn-xs btn-warning" title="{{ $office->is_active ? 'Deactivate (hide from the dropdown)' : 'Activate' }}"><i class="fa fa-{{ $office->is_active ? 'eye-slash' : 'eye' }}"></i></a>
                                                <a href="#" wire:click.prevent="delete({{ $office->id }})" wire:confirm="Remove customs office {{ $office->name }}?" class="btn btn-xs btn-danger" title="Delete"><i class="fa fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center">No customs offices yet. Add one above or from a Customs Declaration.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
