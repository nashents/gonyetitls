<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8 col-md-offset-2">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Charge Types</h5>
                                <small style="color: green">The categories used on Freight Cost/Charge lines (e.g. Ocean Freight, Documentation Fee, Port Handling).</small>
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
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Description</label>
                                            <input type="text" class="form-control" wire:model="description">
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
                                    <tr><th>Name</th><th>Description</th><th>Locked</th><th></th></tr>
                                </thead>
                                <tbody>
                                    @forelse ($chargeTypes as $type)
                                        <tr>
                                            <td>{{ $type->name }}</td>
                                            <td>{{ $type->description }}</td>
                                            <td>{{ $type->is_locked ? 'Yes' : 'No' }}</td>
                                            <td>
                                                @unless ($type->is_locked)
                                                    <a href="#" wire:click.prevent="edit({{ $type->id }})" class="btn btn-xs btn-default"><i class="fa fa-edit"></i></a>
                                                    <a href="#" wire:click.prevent="delete({{ $type->id }})" wire:confirm="Remove this charge type?" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></a>
                                                @endunless
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center">No charge types configured yet.</td></tr>
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
