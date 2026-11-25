@php
    $isMaintenance = !(isset($type) && $type == 'inspection');
    $label = $isMaintenance ? 'Maintenance Schedule' : 'Inspection Schedule';
    $statusClass = ['overdue' => 'label-danger', 'due_soon' => 'label-warning', 'ok' => 'label-success', 'no_reading' => 'label-default', 'inactive' => 'label-default'];
    $statusLabel = \App\Models\InspectionSchedule::STATUS_LABELS + ['inactive' => 'Inactive'];
    $suffix = \App\Models\MaintenanceItem::UNIT_SUFFIX;
    $fmt = fn ($v) => $v === null || $v === '' ? '' : number_format((float) $v, floor((float) $v) == (float) $v ? 0 : 1);
@endphp
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
                            <div class="panel-title">
                                <a href="#" wire:click.prevent="create" class="btn btn-default"><i class="fa fa-plus-square-o"></i> {{ $label }}</a>
                                @if ($isMaintenance)
                                    <a href="#" wire:click.prevent="openBulk" class="btn btn-default"><i class="fa fa-th-list"></i> Track Item on Fleet</a>
                                    <a href="#" wire:click.prevent="openReading" class="btn btn-default"><i class="fa fa-tachometer"></i> Update Hours / Km</a>
                                    <a href="#" wire:click.prevent="exportPlanner" class="btn btn-default" style="float:right"><i class="fa fa-file-excel-o"></i> Download Maintenance Planner</a>
                                @endif
                            </div>
                        </div>
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                            @if ($isMaintenance)
                                <div class="row" style="margin-bottom:10px">
                                    @foreach (['overdue' => 'Overdue', 'due_soon' => 'Due Soon', 'ok' => 'OK', 'no_reading' => 'No Reading'] as $key => $text)
                                        <div class="col-md-3 col-sm-6">
                                            <a href="#" wire:click.prevent="$set('filterStatus', '{{ $filterStatus === $key ? '' : $key }}')" style="text-decoration:none">
                                                <div style="border:1px solid #ddd; border-left:4px solid {{ ['overdue' => '#d9534f', 'due_soon' => '#f0ad4e', 'ok' => '#5cb85c', 'no_reading' => '#999'][$key] }}; padding:8px 12px; {{ $filterStatus === $key ? 'background:#f5f5f5' : '' }}">
                                                    <small class="text-muted">{{ $text }}</small>
                                                    <div style="font-size:20px; font-weight:600; color:#333">{{ $summary[$key] ?? 0 }}</div>
                                                </div>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                                <small style="color: green">Remaining = next due minus the unit's current hour meter / mileage, or days to the next due date. <strong>No Reading</strong> means the unit has no current hours/km captured, or the schedule has no last-done reading.</small>
                                <div class="row" style="margin-top:8px">
                                    <div class="col-md-2">
                                        <select wire:model="filterEquipment" class="form-control">
                                            <option value="">All Equipment</option>
                                            <option value="Horse">Horses</option>
                                            <option value="Vehicle">Vehicles</option>
                                            <option value="Trailer">Trailers</option>
                                            <option value="Asset">Assets</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select wire:model="filterItem" class="form-control">
                                            <option value="">All Tracked Items</option>
                                            @foreach ($items as $item)
                                                <option value="{{ $item->id }}">{{ $item->label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <select wire:model="filterStatus" class="form-control">
                                            <option value="">All Statuses</option>
                                            @foreach ($statusLabel as $key => $text)
                                                <option value="{{ $key }}">{{ $text }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search schedule#, item, fleet# or reg...">
                                    </div>
                                </div>
                                <small class="text-muted">The Maintenance Planner download uses these filters.</small>
                            @else
                                <div class="col-md-5" style="float: right; padding-right:2px">
                                    <div class="form-group">
                                        <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search schedules...">
                                    </div>
                                </div>
                            @endif
                            <table class="table table-striped table-bordered table-sm" cellspacing="0" width="100%" style="margin-top:10px">
                                <thead>
                                    <tr>
                                        <th class="th-sm">Schedule#</th>
                                        <th class="th-sm">Equipment</th>
                                        <th class="th-sm">{{ $isMaintenance ? 'Tracks' : 'Inspection' }}</th>
                                        <th class="th-sm">Interval</th>
                                        <th class="th-sm">Last Done</th>
                                        <th class="th-sm">Next Due</th>
                                        @if ($isMaintenance)
                                            <th class="th-sm">Current</th>
                                            <th class="th-sm">Remaining</th>
                                        @endif
                                        <th class="th-sm">Status</th>
                                        <th class="th-sm">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($inspection_schedules as $schedule)
                                        @php
                                            $rows = $schedule->remaining();
                                            $inactive = strtolower((string) $schedule->status) === 'inactive';
                                            $due = $inactive ? 'inactive' : \App\Models\InspectionSchedule::worstStatus(array_column($rows, 'status'));
                                        @endphp
                                        <tr wire:key="schedule-{{ $schedule->id }}">
                                            <td>
                                                {{ $schedule->inspection_schedule_number }}<br>
                                                <small class="text-muted">
                                                    {{ $schedule->creator ? trim($schedule->creator->name . ' ' . $schedule->creator->surname) : '' }}
                                                    {{ optional($schedule->created_at)->format('d/m/Y') }}
                                                </small>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $schedule->equipmentType() }}</small><br>
                                                {{ $schedule->equipmentLabel() }}
                                            </td>
                                            <td>
                                                {{ $schedule->scheduleFor() }}
                                                @if ($schedule->maintenance_item && $schedule->service_type)
                                                    <br><small class="text-muted">{{ $schedule->service_type->name }}</small>
                                                @endif
                                                @if ($schedule->notes)
                                                    <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($schedule->notes, 60) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                @foreach ($rows as $unit => $row)
                                                    <div>{{ $fmt($row['interval']) }} {{ $suffix[$unit] }}</div>
                                                @endforeach
                                            </td>
                                            <td>
                                                {{ optional($schedule->last_inspection_date)->format('d/m/Y') }}
                                                @if (is_numeric($schedule->last_inspection_hours)) <div>{{ $fmt($schedule->last_inspection_hours) }} HRS</div> @endif
                                                @if (is_numeric($schedule->last_inspection_km)) <div>{{ $fmt($schedule->last_inspection_km) }} KM</div> @endif
                                            </td>
                                            <td>
                                                @foreach ($rows as $unit => $row)
                                                    <div>{{ $unit === 'days' ? optional($schedule->next_due_date)->format('d/m/Y') : ($row['next_due'] !== null ? $fmt($row['next_due']) . ' ' . $suffix[$unit] : '') }}</div>
                                                @endforeach
                                            </td>
                                            @if ($isMaintenance)
                                                <td>
                                                    @foreach ($rows as $unit => $row)
                                                        <div>{{ $unit === 'days' ? now()->format('d/m/Y') : ($row['current'] !== null ? $fmt($row['current']) . ' ' . $suffix[$unit] : '-') }}</div>
                                                    @endforeach
                                                </td>
                                                <td>
                                                    @foreach ($rows as $unit => $row)
                                                        <div class="{{ ['overdue' => 'text-danger', 'due_soon' => 'text-warning'][$row['status']] ?? '' }}" style="font-weight:600">
                                                            {{ $row['remaining'] === null ? '-' : $fmt($row['remaining']) . ' ' . $suffix[$unit] }}
                                                        </div>
                                                    @endforeach
                                                </td>
                                            @endif
                                            <td><span class="label {{ $statusClass[$due] ?? 'label-default' }}">{{ $statusLabel[$due] ?? $due }}</span></td>
                                            <td class="w-10 line-height-35 table-dropdown">
                                                <div class="dropdown">
                                                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i class="fa fa-bars"></i>
                                                        <span class="caret"></span>
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a href="#" wire:click.prevent="openDone({{ $schedule->id }})"><i class="fa fa-check color-success"></i> Mark Done</a></li>
                                                        <li><a href="#" wire:click.prevent="edit({{ $schedule->id }})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                        <li><a href="#" wire:click.prevent="confirmDelete({{ $schedule->id }})"><i class="fa fa-trash color-danger"></i> Delete</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $isMaintenance ? 10 : 8 }}">
                                                <div style="text-align:center; color:grey; padding:5px; font-size:17px">
                                                    No {{ $isMaintenance ? 'Maintenance' : 'Inspection' }} Schedules Found ....
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <nav class="text-center" style="float: right">
                                @if ($inspection_schedules->count() > 0)
                                    {{ $inspection_schedules->links() }}
                                @endif
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Create / Edit --}}
    @foreach (['saveModal' => ['store', 'Add'], 'updateModal' => ['update', 'Edit']] as $modalId => [$action, $verb])
        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="{{ $modalId }}" tabindex="-1" role="dialog">
            <div class="modal-dialog mw-100 w-70" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><i class="fas fa-{{ $action === 'store' ? 'plus' : 'edit' }}"></i> {{ $verb }} {{ $label }} <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="{{ $action }}()">
                        <div class="modal-body">
                            <div class="mb-10">
                                @foreach (['Asset', 'Horse', 'Trailer', 'Vehicle'] as $eq)
                                    <input type="radio" id="{{ $modalId }}-{{ $eq }}" wire:model="equipment" value="{{ $eq }}" class="line-style" required/>
                                    <label for="{{ $modalId }}-{{ $eq }}" class="radio-label">{{ $eq }}</label>
                                @endforeach
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    @php
                                        $searchProp = 'search' . $equipment;
                                        $selectProp = 'selected' . $equipment;
                                    @endphp
                                    <div class="form-group">
                                        <label>{{ $equipment }}<span class="required" style="color: red">*</span></label>
                                        <input type="text" wire:model.debounce.300ms="{{ $searchProp }}" placeholder="Search {{ strtolower($equipment) }}..." class="form-control">
                                        <select wire:model="{{ $selectProp }}" class="form-control" size="4">
                                            <option value="">Select {{ $equipment }}</option>
                                            @foreach ($equipmentList as $unit)
                                                <option value="{{ $unit->id }}">
                                                    @if ($equipment === 'Asset')
                                                        {{ $unit->product->name ?? '' }} {{ $unit->serial_number ? 'SN:' . $unit->serial_number : '' }}
                                                    @elseif ($equipment === 'Horse')
                                                        {{ \App\Support\FleetIdentifier::regFleetLabel($unit) }} {{ $unit->horse_make->name ?? '' }} {{ $unit->horse_model->name ?? '' }}
                                                    @elseif ($equipment === 'Vehicle')
                                                        {{ \App\Support\FleetIdentifier::regFleetLabel($unit) }} {{ $unit->vehicle_make->name ?? '' }} {{ $unit->vehicle_model->name ?? '' }}
                                                    @else
                                                        {{ \App\Support\FleetIdentifier::regFleetLabel($unit) }} {{ $unit->make }} {{ $unit->model }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        @error($selectProp) <span class="text-danger error">{{ $message }}</span>@enderror
                                        @if ($selectedReading)
                                            <div class="alert alert-info" style="margin:6px 0 0; padding:6px 10px">
                                                @if ($editingCurrentReading)
                                                    <div><i class="fas fa-tachometer-alt"></i> Update current readings <small class="text-muted">(saved to the {{ strtolower($equipment) }}'s record)</small></div>
                                                    <div class="row" style="margin-top:6px">
                                                        <div class="col-xs-5">
                                                            <input type="number" step="any" min="0" class="form-control input-sm" wire:model.defer="current_hours" wire:keydown.enter.prevent="saveCurrentReading" placeholder="Hours">
                                                            @error('current_hours') <span class="text-danger error">{{ $message }}</span>@enderror
                                                        </div>
                                                        <div class="col-xs-5">
                                                            <input type="number" step="any" min="0" class="form-control input-sm" wire:model.defer="current_km" wire:keydown.enter.prevent="saveCurrentReading" placeholder="Km">
                                                            @error('current_km') <span class="text-danger error">{{ $message }}</span>@enderror
                                                        </div>
                                                    </div>
                                                    <div style="margin-top:6px">
                                                        <button type="button" class="btn btn-success btn-xs" wire:click="saveCurrentReading" wire:loading.attr="disabled" wire:target="saveCurrentReading"><i class="fas fa-save"></i> Save</button>
                                                        <button type="button" class="btn btn-default btn-xs" wire:click="cancelCurrentReading">Cancel</button>
                                                        <small class="text-muted">Leave a box blank to keep its current figure.</small>
                                                    </div>
                                                @else
                                                    <i class="fas fa-tachometer-alt"></i> Current readings:
                                                    <strong>{{ $selectedReading['hours'] !== null ? number_format($selectedReading['hours'], 1) . ' hrs' : 'no hours recorded' }}</strong>
                                                    &middot;
                                                    <strong>{{ $selectedReading['km'] !== null ? number_format($selectedReading['km']) . ' km' : 'no mileage recorded' }}</strong>
                                                    <a href="#" wire:click.prevent="editCurrentReading" style="margin-left:6px">
                                                        <i class="fas fa-{{ $selectedReading['hours'] === null && $selectedReading['km'] === null ? 'plus' : 'edit' }}"></i>
                                                        {{ $selectedReading['hours'] === null && $selectedReading['km'] === null ? 'Click to add' : 'Update' }}
                                                    </a>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    @if ($isMaintenance)
                                        <div class="form-group">
                                            <label>Tracked Item</label>
                                            <select wire:model="maintenance_item_id" class="form-control">
                                                <option value="">None - general job type schedule</option>
                                                @foreach ($formItems as $item)
                                                    <option value="{{ $item->id }}">{{ $item->label }}</option>
                                                @endforeach
                                            </select>
                                            @error('maintenance_item_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small class="text-muted">Picking an item fills the trigger and interval; change the interval here to override it for this unit.</small>
                                        </div>
                                        <div class="form-group">
                                            <label>Job Type</label>
                                            <select wire:model.defer="service_type_id" class="form-control">
                                                <option value="">Select Job Type</option>
                                                @foreach ($service_types as $service_type)
                                                    <option value="{{ $service_type->id }}">{{ $service_type->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('service_type_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    @else
                                        <div class="form-group">
                                            <label>Categories<span class="required" style="color: red">*</span></label>
                                            <select wire:model.defer="problem_category_id" class="form-control" required>
                                                <option value="">Select Inspection</option>
                                                @foreach ($problem_categories as $problem_category)
                                                    <option value="{{ $problem_category->id }}">{{ $problem_category->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('problem_category_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small><a href="{{ route('problem_categories.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Category</a></small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @php $dims = \App\Models\MaintenanceItem::parseUnits((array) $trigger_units); @endphp
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Count By<span class="required" style="color: red">*</span></label>
                                        @foreach (\App\Models\MaintenanceItem::UNITS as $unit => $unitLabel)
                                            @if ($unit === 'days' || $equipment !== 'Asset')
                                                <div class="checkbox" style="margin:0 0 2px">
                                                    <label><input type="checkbox" wire:model="trigger_units" value="{{ $unit }}"> {{ $unitLabel }}</label>
                                                </div>
                                            @endif
                                        @endforeach
                                        <small class="text-muted">More than one: due when the first runs out.</small>
                                        @error('trigger_units') <br><span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                @if (in_array('hours', $dims))
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Interval Hours<span class="required" style="color: red">*</span></label>
                                            <input type="number" step="any" class="form-control" wire:model.defer="interval_hours" placeholder="e.g. 15000">
                                            @error('interval_hours') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                @endif
                                @if (in_array('km', $dims))
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Interval Km<span class="required" style="color: red">*</span></label>
                                            <input type="number" step="any" class="form-control" wire:model.defer="interval_km" {{ $equipment == 'Asset' ? 'disabled' : '' }} placeholder="e.g. 10000">
                                            @error('interval_km') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                @endif
                                @if (in_array('days', $dims))
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Interval Days<span class="required" style="color: red">*</span></label>
                                            <input type="number" step="any" class="form-control" wire:model.defer="interval_days" placeholder="e.g. 90">
                                            @error('interval_days') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Last Done Date</label>
                                        <input type="date" class="form-control" wire:model.defer="last_inspection_date">
                                        @error('last_inspection_date') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                @if (in_array('hours', $dims))
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Last Done Hours</label>
                                            <input type="number" step="any" class="form-control" wire:model.defer="last_inspection_hours" placeholder="Hour meter when done">
                                            @error('last_inspection_hours') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                @endif
                                @if (in_array('km', $dims))
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Last Done Km</label>
                                            <input type="number" step="any" class="form-control" wire:model.defer="last_inspection_km" {{ $equipment == 'Asset' ? 'disabled' : '' }} placeholder="Odometer when done">
                                            @error('last_inspection_km') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="row">
                                @if (in_array('days', $dims))
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Next Due Date</label>
                                            <input type="date" class="form-control" wire:model.defer="next_due_date">
                                            @error('next_due_date') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                @endif
                                @if (in_array('hours', $dims))
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Next Due Hours</label>
                                            <input type="number" step="any" class="form-control" wire:model.defer="next_due_hours">
                                            @error('next_due_hours') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                @endif
                                @if (in_array('km', $dims))
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Next Due Km</label>
                                            <input type="number" step="any" class="form-control" wire:model.defer="next_due_km" {{ $equipment == 'Asset' ? 'disabled' : '' }}>
                                            @error('next_due_km') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                @endif
                                <div class="col-md-3">
                                    <small class="text-muted">Leave Next Due blank to calculate it as last done + interval.</small>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Status<span class="required" style="color: red">*</span></label>
                                        <select class="form-control" wire:model.defer="status" required>
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                        @error('status') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Notes</label>
                                        <textarea class="form-control" wire:model.defer="notes" cols="30" rows="2"></textarea>
                                        @error('notes') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
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
    @endforeach

    @if ($isMaintenance)
        {{-- Track one item on many units --}}
        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="bulkTrackModal" tabindex="-1" role="dialog">
            <div class="modal-dialog mw-100 w-70" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><i class="fa fa-th-list"></i> Track Item on Fleet <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="storeBulk()">
                        <div class="modal-body">
                            @php $bulkItem = $bulk_item_id ? $bulkItems->firstWhere('id', (int) $bulk_item_id) : null; @endphp
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tracked Item<span class="required" style="color: red">*</span></label>
                                        <select wire:model="bulk_item_id" class="form-control" required>
                                            <option value="">Select Item</option>
                                            @foreach ($bulkItems as $item)
                                                <option value="{{ $item->id }}">{{ $item->label }}</option>
                                            @endforeach
                                        </select>
                                        @error('bulk_item_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Equipment<span class="required" style="color: red">*</span></label>
                                        <select wire:model="bulk_equipment" class="form-control">
                                            @foreach (['Horse', 'Vehicle', 'Trailer', 'Asset'] as $eq)
                                                @if (!$bulkItem || $bulkItem->appliesTo($eq))
                                                    @if ($eq !== 'Asset' || !$bulkItem || $bulkItem->units() === ['days'])
                                                        <option value="{{ $eq }}">{{ $eq }}s</option>
                                                    @endif
                                                @endif
                                            @endforeach
                                        </select>
                                        @error('bulk_equipment') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>
                            @php
                                $bulkUnitsCounted = $bulkItem ? $bulkItem->units() : [];
                                $bulkMeters = array_values(array_diff($bulkUnitsCounted, ['days']));
                            @endphp
                            @if ($bulkItem)
                                <div class="row">
                                    @foreach ($bulkUnitsCounted as $u)
                                        <div class="col-md-3" wire:key="bulk-interval-{{ $u }}">
                                            <div class="form-group">
                                                <label>Interval ({{ $suffix[$u] }})<span class="required" style="color: red">*</span></label>
                                                <input type="number" step="any" class="form-control" wire:model.defer="bulk_intervals.{{ $u }}">
                                                @error('bulk_intervals.' . $u) <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                    @endforeach
                                    @if (count($bulkUnitsCounted) > 1)
                                        <div class="col-md-3"><small class="text-muted">Due when the first of these runs out.</small></div>
                                    @endif
                                </div>
                            @endif
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Last Done Date</label>
                                        <input type="date" class="form-control" wire:model.defer="bulk_last_date">
                                        @error('bulk_last_date') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                @if ($bulkMeters)
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label>Last Done Reading</label>
                                            <div class="checkbox" style="margin-top:0">
                                                <label><input type="checkbox" wire:model="bulk_use_current"> Use each unit's current {{ implode(' and ', array_map(fn ($u) => $u === 'km' ? 'mileage' : 'hour meter', $bulkMeters)) }}</label>
                                            </div>
                                            @if (!$bulk_use_current)
                                                @foreach ($bulkMeters as $u)
                                                    <input type="number" step="any" class="form-control" style="margin-bottom:4px" wire:key="bulk-reading-{{ $u }}" wire:model.defer="bulk_last_readings.{{ $u }}" placeholder="{{ $suffix[$u] }} - same reading for every unit ticked">
                                                    @error('bulk_last_readings.' . $u) <span class="text-danger error">{{ $message }}</span>@enderror
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                @endif
                                <div class="col-md-4">
                                    <small class="text-muted">Units already tracking this item are skipped. Adjust a single unit's last-done reading afterwards with Edit.</small>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Units<span class="required" style="color: red">*</span>
                                    <a href="#" wire:click.prevent="selectAllBulk" style="margin-left:10px; font-weight:normal">Select all</a>
                                    <a href="#" wire:click.prevent="$set('bulk_ids', [])" style="margin-left:10px; font-weight:normal">Clear</a>
                                    <span class="text-muted" style="font-weight:normal; margin-left:10px">{{ count($bulk_ids) }} selected</span>
                                </label>
                                <div style="max-height:260px; overflow-y:auto; border:1px solid #ddd; padding:6px 10px">
                                    <div class="row">
                                        @forelse ($bulkUnits as $unit)
                                            <div class="col-md-3 col-sm-4" wire:key="bulk-unit-{{ $bulk_equipment }}-{{ $unit->id }}">
                                                <label style="font-weight:normal">
                                                    <input type="checkbox" wire:model.defer="bulk_ids" value="{{ $unit->id }}">
                                                    {{ \App\Models\InspectionSchedule::labelFor($unit) }}
                                                    @if ($bulk_equipment !== 'Asset')
                                                        <small class="text-muted">
                                                            {{ implode(' / ', array_map(function ($u) use ($unit, $fmt) {
                                                                $r = \App\Models\InspectionSchedule::currentReading($unit, $u);
                                                                return $r !== null ? $fmt($r) . ($u === 'km' ? ' km' : ' hrs') : ($u === 'km' ? 'no km' : 'no hrs');
                                                            }, $bulkMeters ?: ['hours'])) }}
                                                        </small>
                                                    @endif
                                                </label>
                                            </div>
                                        @empty
                                            <div class="col-md-12 text-muted">No units found.</div>
                                        @endforelse
                                    </div>
                                </div>
                                @error('bulk_ids') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
                                <button type="submit" class="btn btn-primary btn-wide btn-rounded"><i class="fa fa-save"></i> Track Item</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Update a unit's hour meter / odometer --}}
        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="readingModal" tabindex="-1" role="dialog">
            <div class="modal-dialog {{ $reading_mode === 'fleet' ? 'modal-lg' : '' }}" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><i class="fa fa-tachometer"></i> Update Hours / Km <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="{{ $reading_mode === 'fleet' ? 'saveFleetReadings' : 'saveReading' }}">
                        <div class="modal-body">
                            <div class="btn-group mb-10" role="group" style="margin-bottom:12px">
                                <button type="button" wire:click="$set('reading_mode', 'single')" class="btn btn-sm {{ $reading_mode === 'single' ? 'btn-primary' : 'btn-default' }}"><i class="fa fa-truck"></i> Single Unit</button>
                                <button type="button" wire:click="$set('reading_mode', 'fleet')" class="btn btn-sm {{ $reading_mode === 'fleet' ? 'btn-primary' : 'btn-default' }}"><i class="fa fa-list"></i> Entire Fleet</button>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Equipment</label>
                                        <select wire:model="reading_equipment" class="form-control">
                                            <option value="Horse">Horse</option>
                                            <option value="Vehicle">Vehicle</option>
                                            <option value="Trailer">Trailer</option>
                                        </select>
                                    </div>
                                </div>
                                @if ($reading_mode === 'fleet')
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Search</label>
                                        <input type="text" wire:model.debounce.400ms="reading_search" class="form-control" placeholder="Reg or fleet number...">
                                    </div>
                                </div>
                            </div>
                            @error('fleet_readings') <div class="alert alert-danger py-2">{{ $message }}</div> @enderror
                            <div style="max-height:55vh; overflow-y:auto">
                                <table class="table table-sm table-bordered table-striped" style="margin-bottom:0">
                                    <thead style="position:sticky; top:0; background:#fff; z-index:1">
                                        <tr>
                                            <th>Unit</th>
                                            <th class="text-right">Current Hrs</th>
                                            <th style="width:150px">New Hrs</th>
                                            <th class="text-right">Current Km</th>
                                            <th style="width:150px">New Km</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($readingUnits as $unit)
                                            @php
                                                $curHrs = \App\Models\InspectionSchedule::currentReading($unit, 'hours');
                                                $curKm = \App\Models\InspectionSchedule::currentReading($unit, 'km');
                                            @endphp
                                            <tr wire:key="fleet-reading-{{ $reading_equipment }}-{{ $unit->id }}">
                                                <td>{{ \App\Models\InspectionSchedule::labelFor($unit) }}</td>
                                                <td class="text-right">{{ $curHrs !== null ? \App\Models\MaintenanceItem::formatNumber($curHrs) : '—' }}</td>
                                                <td>
                                                    <input type="number" step="any" min="0" class="form-control input-sm" wire:model.defer="fleet_readings.{{ $unit->id }}.hours" placeholder="unchanged">
                                                    @error("fleet_readings.{$unit->id}.hours") <small class="text-danger">{{ $message }}</small> @enderror
                                                </td>
                                                <td class="text-right">{{ $curKm !== null ? \App\Models\MaintenanceItem::formatNumber($curKm) : '—' }}</td>
                                                <td>
                                                    <input type="number" step="any" min="0" class="form-control input-sm" wire:model.defer="fleet_readings.{{ $unit->id }}.km" placeholder="unchanged">
                                                    @error("fleet_readings.{$unit->id}.km") <small class="text-danger">{{ $message }}</small> @enderror
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-center text-muted">No units found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <small class="text-muted">Leave a cell blank to keep the current reading. Readings can only go up here — fix a lower reading under Single Unit. Saves to the same fields job cards and the tracker sync update.</small>
                                @else
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Unit<span class="required" style="color: red">*</span></label>
                                        <select wire:model="reading_unit_id" class="form-control">
                                            <option value="">Select Unit</option>
                                            @foreach ($readingUnits as $unit)
                                                <option value="{{ $unit->id }}">{{ \App\Models\InspectionSchedule::labelFor($unit) }}</option>
                                            @endforeach
                                        </select>
                                        @error('reading_unit_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Current Hour Meter</label>
                                        <input type="number" step="any" class="form-control" wire:model.defer="reading_hours">
                                        @error('reading_hours') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Current Mileage (Km)</label>
                                        <input type="number" step="any" class="form-control" wire:model.defer="reading_km">
                                        @error('reading_km') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>
                            <small class="text-muted">Saves to the unit's hours / mileage, the same fields job cards and the tracker sync update.</small>
                            @endif
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
    @endif

    {{-- Mark done --}}
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="doneModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title"><i class="fa fa-check"></i> Mark Done <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="markDone()">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Done On<span class="required" style="color: red">*</span></label>
                                    <input type="date" class="form-control" wire:model.defer="done_date" required>
                                    @error('done_date') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Hour Meter</label>
                                    <input type="number" step="any" class="form-control" wire:model.defer="done_reading_hours">
                                    @error('done_reading_hours') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Mileage (Km)</label>
                                    <input type="number" step="any" class="form-control" wire:model.defer="done_reading_km">
                                    @error('done_reading_km') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea class="form-control" wire:model.defer="done_notes" rows="2" placeholder="e.g. Job card number, parts fitted"></textarea>
                        </div>
                        <small class="text-muted">Restarts the countdown: next due = these readings / date + the interval. A reading above the unit's current meter also updates the unit.</small>
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
                            <button type="submit" class="btn btn-primary btn-wide btn-rounded"><i class="fa fa-check"></i> Mark Done</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <center><strong>Are you sure you want to delete this Schedule?</strong></center>
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
