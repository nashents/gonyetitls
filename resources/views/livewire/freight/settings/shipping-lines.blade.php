<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Shipping Lines</h5>
                                <small style="color: green">The carriers selectable on Containers, Free Day Policies and Rate Tiers — not the full vendor list.</small>
                            </div>
                        </div>
                        <div class="panel-body">
                            <form wire:submit.prevent="save" class="mb-20">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Name</label>
                                            <input type="text" class="form-control" wire:model="name">
                                            @error('name') <span class="text-danger error">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label>Vendor <small style="color:green">(optional — for billing/accounting)</small></label>
                                            <select class="form-control" wire:model="vendor_id">
                                                <option value="">Select Vendor</option>
                                                @foreach ($vendors as $vendor)
                                                    <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('vendor_id') <span class="text-danger error">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <label>&nbsp;</label>
                                        <div class="checkbox" style="margin-top: 8px;">
                                            <label><input type="checkbox" wire:model="is_active"> Active</label>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <label>&nbsp;</label>
                                        <button type="submit" class="btn btn-primary btn-block"><i class="fa fa-save"></i></button>
                                    </div>
                                </div>
                            </form>

                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr><th>Name</th><th>Vendor</th><th>Active</th><th></th></tr>
                                </thead>
                                <tbody>
                                    @forelse ($shippingLines as $line)
                                        <tr>
                                            <td>{{ $line->name }}</td>
                                            <td>{{ $line->vendor->name ?? '—' }}</td>
                                            <td>{{ $line->is_active ? 'Yes' : 'No' }}</td>
                                            <td>
                                                <a href="#" wire:click.prevent="edit({{ $line->id }})" class="btn btn-xs btn-default"><i class="fa fa-edit"></i></a>
                                                <a href="#" wire:click.prevent="delete({{ $line->id }})" wire:confirm="Remove this shipping line?" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center">No shipping lines configured yet.</td></tr>
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
