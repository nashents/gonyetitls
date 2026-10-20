<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div>
                                @include('includes.messages')
                            </div>

                            <div class="panel-title" style="display:flex; justify-content:space-between; align-items:center; gap:10px; flex-wrap:wrap;">
                                <a href="#" wire:click.prevent="create" class="btn btn-default"><i class="fa fa-plus-square-o"></i> Unit of Measure</a>
                                <input type="text" class="form-control" style="max-width:260px;" wire:model.debounce.300ms="search" placeholder="Search name or abbreviation..." />
                            </div>
                            <p class="text-muted" style="margin:8px 0 0;"><i class="fa fa-lock"></i> System units are seeded with the app and can't be edited or deleted. Add your own units for anything missing.</p>
                        </div>
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">

                            <table class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Name</th>
                                    <th class="th-sm">Abbreviation</th>
                                    <th class="th-sm">Description</th>
                                    <th class="th-sm">Type</th>
                                    <th class="th-sm">Action</th>
                                  </tr>
                                </thead>
                                <tbody>
                                    @forelse ($units as $unit)
                                  <tr wire:key="uom-{{ $unit->id }}">
                                    <td>{{ $unit->name }}</td>
                                    <td>{{ $unit->abbreviation }}</td>
                                    <td>{{ $unit->description }}</td>
                                    <td>
                                        @if ($unit->is_system)
                                            <span class="label label-default"><i class="fa fa-lock"></i> System</span>
                                        @else
                                            <span class="label label-success">Custom</span>
                                        @endif
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        @if ($unit->is_system)
                                            <span class="text-muted"><i class="fa fa-lock"></i> Locked</span>
                                        @else
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#" wire:click.prevent="edit({{ $unit->id }})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" wire:click.prevent="confirmDelete({{ $unit->id }})"><i class="fa fa-trash color-danger"></i> Delete</a></li>
                                            </ul>
                                        </div>
                                        @endif
                                    </td>
                                  </tr>
                                    @empty
                                  <tr>
                                    <td colspan="5" class="text-center text-muted">No units of measure found.</td>
                                  </tr>
                                    @endforelse
                                </tbody>
                              </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @foreach (['unitOfMeasureModal' => ['store', 'Add Unit of Measure', 'fa-plus', 'fa-save', 'Save'], 'unitOfMeasureEditModal' => ['update', 'Edit Unit of Measure', 'fa-edit', 'fa-refresh', 'Update']] as $modalId => [$action, $title, $icon, $btnIcon, $btnLabel])
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="{{ $modalId }}Label">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="{{ $modalId }}Label"><i class="fas {{ $icon }}"></i> {{ $title }} <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="{{ $action }}">
                <div class="modal-body">
                    <div class="form-group">
                        <label>Name<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="e.g. Crate" required />
                        @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label>Abbreviation<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="abbreviation" placeholder="e.g. crt" required />
                        @error('abbreviation') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="description" placeholder="Optional" />
                        @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa {{ $btnIcon }}"></i>{{ $btnLabel }}</button>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
    @endforeach

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="unitOfMeasureDeleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                   <center><strong>Are you sure you want to delete this Unit of Measure?</strong></center>
                </div>
                <div class="modal-footer no-border">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="button" wire:click="delete" wire:loading.attr="disabled" class="btn bg-black btn-wide btn-rounded"><i class="fa fa-trash"></i>Delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        ['unitOfMeasureModal', 'unitOfMeasureEditModal', 'unitOfMeasureDeleteModal'].forEach(function (id) {
            window.addEventListener('show-' + id, function () { $('#' + id).modal('show'); });
            window.addEventListener('hide-' + id, function () { $('#' + id).modal('hide'); });
        });
    </script>
</div>
