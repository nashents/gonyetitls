<div>
    <section class="section">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <a href="#" wire:click.prevent="create" class="btn btn-default"><i class="fa fa-plus-square-o"></i> Tracked Item</a>
                                <a href="#" wire:click.prevent="loadStandardItems" class="btn btn-default"><i class="fa fa-download"></i> Load Standard Items</a>
                                <a href="#maintenanceItemsBody" data-toggle="collapse" class="btn btn-default" style="float:right"><i class="fa fa-list"></i> Show / Hide Tracked Items</a>
                            </div>
                        </div>
                        <div id="maintenanceItemsBody" class="panel-body p-20 collapse" wire:ignore.self style="overflow-x:auto; width:100%;">
                            <small style="color: green">Tracked items are the fixed intervals the workshop tracks per unit, e.g. THERMOSTATS @ 15000 HRS. Add an item to a horse/vehicle/trailer with <strong>Track Item on Fleet</strong> below.</small>
                            <div class="col-md-4" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search tracked items...">
                                </div>
                            </div>
                            <table class="table table-striped table-bordered table-sm" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th class="th-sm">Item</th>
                                        <th class="th-sm">Category</th>
                                        <th class="th-sm">Interval</th>
                                        <th class="th-sm">Due Soon At</th>
                                        <th class="th-sm">Applies To</th>
                                        <th class="th-sm">Units Tracking</th>
                                        <th class="th-sm">Status</th>
                                        <th class="th-sm">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($items as $item)
                                        <tr wire:key="maintenance-item-{{ $item->id }}">
                                            <td>{{ $item->name }} @if ($item->is_monitoring) <span class="label label-info">Monitoring</span> @endif
                                                @if ($item->description) <br><small class="text-muted">{{ $item->description }}</small> @endif
                                            </td>
                                            <td>{{ $item->category }}</td>
                                            <td>{{ str_replace(' / ', ' or ', $item->intervalText()) }}</td>
                                            <td>{{ implode(' / ', array_map(fn ($u) => number_format($item->warningThreshold($u)) . ' ' . \App\Models\MaintenanceItem::UNIT_SUFFIX[$u], $item->units())) }} left</td>
                                            <td>{{ $item->applies_to_label }}</td>
                                            <td>{{ $item->schedules_count }}</td>
                                            <td>
                                                @if ($item->status)
                                                    <span class="label label-success">Active</span>
                                                @else
                                                    <span class="label label-default">Inactive</span>
                                                @endif
                                            </td>
                                            <td class="w-10 line-height-35 table-dropdown">
                                                <div class="dropdown">
                                                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i class="fa fa-bars"></i>
                                                        <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a href="#" wire:click.prevent="edit({{ $item->id }})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                        <li><a href="#" wire:click.prevent="confirmDelete({{ $item->id }})"><i class="fa fa-trash color-danger"></i> Delete</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8">
                                                <div style="text-align:center; color:grey; padding:5px; font-size:17px">
                                                    No Tracked Items Found .... Click <strong>Load Standard Items</strong> to start from the workshop planner list.
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <nav class="text-center" style="float: right">
                                {{ $items->links() }}
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="maintenanceItemModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fas fa-plus"></i> {{ $item_id ? 'Edit' : 'Add' }} Tracked Item <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="save()">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Item Name<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control" wire:model.defer="name" placeholder="e.g. THERMOSTATS" required>
                                    @error('name') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Category</label>
                                    <input type="text" class="form-control" wire:model.defer="category" placeholder="e.g. Cooling">
                                    @error('category') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        <label>Tracked By<span class="required" style="color: red">*</span></label>
                        <small class="text-muted">Tick one or more. With more than one, the item is due when the first one runs out.</small>
                        @error('units') <br><span class="text-danger error">{{ $message }}</span>@enderror
                        <table class="table table-condensed" style="margin-bottom:10px">
                            <thead>
                                <tr><th style="width:30%"></th><th>Interval</th><th>Due Soon When Left &le;</th></tr>
                            </thead>
                            <tbody>
                                @foreach (\App\Models\MaintenanceItem::UNITS as $unit => $unitLabel)
                                    @php $on = in_array($unit, (array) $units); @endphp
                                    <tr wire:key="item-unit-{{ $unit }}">
                                        <td>
                                            <label style="font-weight:normal; margin:0">
                                                <input type="checkbox" wire:model="units" value="{{ $unit }}"> {{ $unitLabel }}
                                            </label>
                                        </td>
                                        <td>
                                            <input type="number" step="any" class="form-control input-sm" wire:model.defer="intervals.{{ $unit }}"
                                                placeholder="{{ ['hours' => 'e.g. 10000', 'km' => 'e.g. 150000', 'days' => 'e.g. 365'][$unit] }}" {{ $on ? '' : 'disabled' }}>
                                            @error('intervals.' . $unit) <span class="text-danger error">{{ $message }}</span>@enderror
                                        </td>
                                        <td>
                                            <input type="number" step="any" class="form-control input-sm" wire:model.defer="warnings.{{ $unit }}" placeholder="default 10%" {{ $on ? '' : 'disabled' }}>
                                            @error('warnings.' . $unit) <span class="text-danger error">{{ $message }}</span>@enderror
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Applies To<span class="required" style="color: red">*</span></label>
                                    @foreach (\App\Models\MaintenanceItem::APPLIES_TO as $value => $label)
                                        <div class="checkbox" style="margin:0 0 4px">
                                            <label><input type="checkbox" wire:model.defer="applies_to" value="{{ $value }}"> {{ $label }}</label>
                                        </div>
                                    @endforeach
                                    @error('applies_to') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Status<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.defer="status" required>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <div class="checkbox" style="margin-top:0">
                                        <label><input type="checkbox" wire:model.defer="is_monitoring"> Monitoring item</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea class="form-control" wire:model.defer="description" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
                            <button type="submit" class="btn btn-primary btn-wide btn-rounded"><i class="fa fa-save"></i> Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="maintenanceItemDeleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <center><strong>Are you sure you want to delete this Tracked Item?</strong></center>
                </div>
                <form wire:submit.prevent="destroy()">
                    <div class="modal-footer no-border">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
                            <button type="submit" class="btn btn-danger btn-wide btn-rounded"><i class="fa fa-trash"></i> Delete</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
