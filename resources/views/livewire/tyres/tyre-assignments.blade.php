<div>
    <div class="panel-title">
        <a href="#" wire:click="exportTyreAssignmentsExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
        <a href="#" wire:click="exportTyreAssignmentsCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
        <a href="#" wire:click="exportTyreAssignmentsPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
        <a href="#" wire:click.prevent="openAssign()" class="btn btn-default border-success btn-rounded btn-wide"><i class="fa fa-plus-square-o"></i>Assign Tyres</a>
        <a href="#" wire:click.prevent="openUnassign()" class="btn btn-default border-warning btn-rounded btn-wide {{ count($selected) ? '' : 'disabled' }}"><i class="fa fa-unlink"></i>Unassign Selected{{ count($selected) ? ' ('.count($selected).')' : '' }}</a>
    </div>
    <br>
    @php $pageIds = $tyre_assignments->pluck('id')->all(); @endphp
    <table  class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
        <thead >
            <tr>
            <th class="th-sm">
                @if (count($pageIds))
                    <input type="checkbox" wire:click="selectAll({{ json_encode($pageIds) }})" {{ count($pageIds) && count($selected) === count($pageIds) ? 'checked' : '' }} title="Select all">
                @endif
            </th>
            <th class="th-sm">Tyre#
            </th>
            <th class="th-sm">Product
            </th>
            <th class="th-sm">Serial#
            </th>
            <th class="th-sm">Specifications
            </th>
            <th class="th-sm">Axle
            </th>
            <th class="th-sm">Position
            </th>
            <th class="th-sm">Usage
            </th>
            <th class="th-sm">Health Status
            </th>
            <th class="th-sm">Action
            </th>
            </tr>
        </thead>
        <tbody>
            
            @forelse ($tyre_assignments as $tyre_assignment)
            <tr wire:key="asset-tyre-{{ $tyre_assignment->id }}">
                @php
                    $tyre = $tyre_assignment->tyre;
                @endphp
                @if ($tyre)
                    <td><input type="checkbox" wire:model="selected" value="{{ $tyre_assignment->id }}"></td>
                    <td>{{$tyre_assignment->tyre ? $tyre_assignment->tyre->tyre_number : ""}}</td>
                    <td>{{$tyre_assignment->tyre->product ? $tyre_assignment->tyre->product->name : ""}} {{$tyre_assignment->tyre->product && $tyre_assignment->tyre->product->brand ? $tyre_assignment->tyre->product->brand->name : ""}}</td>
                    <td>{{$tyre_assignment->tyre ? $tyre_assignment->tyre->serial_number : ""}}</td>
                    <td>{{$tyre_assignment->tyre ? $tyre_assignment->tyre->width : ""}} / {{$tyre_assignment->tyre ? $tyre_assignment->tyre->aspect_ratio : ""}} R {{$tyre_assignment->tyre ? $tyre_assignment->tyre->diameter : ""}}</td>
                    <td>{{$tyre_assignment->axle}}</td>
                    <td>{{ \App\Models\TyreAssignment::POSITIONS[$tyre_assignment->position] ?? $tyre_assignment->position }}</td>
                    <td>
                        <small><strong>Acquisition: </strong> {{Carbon\Carbon::parse($tyre_assignment->tyre->purchase_date)->format('d M Y')}}</small><br>
                        <small><strong>Age: </strong> {{ $tyre_assignment->tyre->age ?? '-' }}</small><br>
                        <small><strong>Fitted: </strong> {{ number_format($tyre_assignment->starting_odometer) }}</small> <br>
                        <small><strong>Current: </strong> {{ $tyre_assignment->ending_odometer ? number_format($tyre_assignment->ending_odometer) : number_format(optional($asset)->mileage ?? 0) }}</small> <br>
                        <small><strong>Travelled: </strong> {{ number_format($tyre_assignment->travelled_km ?? 0) }} km</small> <br>
                        <small><strong>Life(Standard): </strong> {{ number_format($tyre_assignment->tyre->life_span ?? 0) }} km</small> <br>
                        <small><strong>Remaining: </strong>
                            @php $rem = $tyre_assignment->remaining_km; $pct = $tyre_assignment->remaining_pct; @endphp
                                {{ is_null($rem) ? '-' : number_format($rem) . ' km' }}
                            @if(!is_null($pct))
                                ({{ $pct }}%)
                            @endif
                        </small> <br>
                    </td>
                    <td>
                        @php
                                $checklist_result = App\Models\ChecklistResult::where('tyre_id',$tyre->id)->latest()->first();
                                if ($checklist_result) {
                                    $tread_depth_mm = $checklist_result->tread_depth_mm;
                                    $pressure_psi = $checklist_result->pressure_psi;
                                    $valve_ok = $checklist_result->valve_ok;
                                    $sidewall_damage = $checklist_result->sidewall_damage;
                                    $rim_condition = $checklist_result->rim_condition;
                                    $wheel_nuts_torqued = $checklist_result->wheel_nuts_torqued;
                                    $axle_match = $checklist_result->axle_match;
                                    $notes = $checklist_result->notes;
                                    $action_required = $checklist_result->action_required;
                                    $rating = $checklist_result->rating;
                                }
                        @endphp 
                        
                        @if ($checklist_result)
                            <small><strong>Tread Depth(<i>mm</i>):</strong>  <span class="badge bg-{{$this->badge($tyre->id,'depth')}}">{{$tread_depth_mm}}</span> </small> <br>
                            <small><strong>Tyre Pressure(<i>psi</i>):</strong> <span class="badge bg-{{$this->badge($tyre->id,'pressure')}}">{{$pressure_psi}}</span> </small> <br>
                            <small><strong>Valve: </strong> {{$valve_ok == 1 ? "Air Tight" : "Leaking"}}</small> <br>
                            <small><strong>Sidewall Damage: </strong> {{$sidewall_damage}}</small> <br>
                            <small><strong>Rim Condition: </strong> {{$rim_condition}}</small> <br>
                            <small><strong>Wheelnuts Torqued:</strong> {{$wheel_nuts_torqued == 1 ? "Yes" : "No"}}</small> <br>
                            <small><strong>Axle Match:</strong> {{$axle_match == 1 ? "Match" : "Not Matching"}}</small> <br>
                            <small><strong>Overal Rating:</strong>  @for ($i = 1; $i <= 5; $i++)
                        <span style="color: {{ $i <= $rating ? '#FFD700' : '#ccc' }};">★</span>
                    @endfor</small> <br>
                            <small><strong>Notes:</strong> {{Str::limit($notes,30,'...')}}</small> <br>
                            <small><strong>Action:</strong> {{$action_required}}</small> <br>
                        @endif
                        {{-- <span class="badge bg-{{$tyre->retread == 0 ? "success" : "warning"}}">{{$tyre->retread == 0 ? "Fit for use" : "Retread"}}</span> --}}
                    </td>
                    <td>
                        <a href="#" wire:click.prevent="openUnassign({{ $tyre_assignment->id }})" class="btn btn-default btn-sm"><i class="fa fa-unlink color-warning"></i> Unassign</a>
                    </td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="11">
                    <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                        No tyres assigned to this {{ strtolower($type) }} found ....
                    </div>
                    
                </td>
            </tr> 
            @endforelse
        
        </tbody>
    </table>
    <nav class="text-center" style="float: right">
        <ul class="pagination rounded-corners">
            @if (isset($tyre_assignments))
                @if ($tyre_assignments->count()>0)
                    {{ $tyre_assignments->links() }} 
                @endif
            @endif 
        </ul>
    </nav>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="assetTyreAssignModal" tabindex="-1" role="dialog" aria-labelledby="assetTyreAssignModalLabel">
        <div class="modal-dialog mw-100 w-75" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="assetTyreAssignModalLabel"><i class="fa fa-plus"></i> Assign Tyres to {{ $type }} {{ optional($asset)->identifier_label }} <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="saveAssignments()">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Fitting Mileage<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.defer="fitting_odometer" placeholder="Enter Fitting Mileage" required>
                                @error('fitting_odometer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Date Fitted<span class="required" style="color: red">*</span></label>
                                <input type="date" max="{{ date('Y-m-d') }}" class="form-control" wire:model.defer="date_fitted" required>
                                @error('date_fitted') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Search Tyres</label>
                                <input type="text" class="form-control" autocomplete="off" wire:model.debounce.400ms="tyreSearch" placeholder="Serial#, tyre#, product or brand...">
                            </div>
                        </div>
                    </div>
                    @error('assignRows') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th style="width:45%">Tyre<span class="required" style="color: red">*</span> <small class="text-muted">({{ $availableTyres->count() }} available)</small></th>
                                <th>Axle<span class="required" style="color: red">*</span></th>
                                <th>Position<span class="required" style="color: red">*</span></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($assignRows as $i => $row)
                            <tr wire:key="assign-row-{{ $row['key'] }}">
                                <td>
                                    <select class="form-control" wire:model.defer="assignRows.{{ $i }}.tyre_id">
                                        <option value="">Select Tyre</option>
                                        @foreach ($availableTyres as $availableTyre)
                                            <option value="{{ $availableTyre->id }}">{{ $availableTyre->product && $availableTyre->product->brand ? $availableTyre->product->brand->name : "" }} {{ $availableTyre->product ? $availableTyre->product->name : "" }} {{ $availableTyre->serial_number ? "SN#: ".$availableTyre->serial_number : "" }} - {{ $availableTyre->width }}/{{ $availableTyre->aspect_ratio }} R {{ $availableTyre->diameter }}</option>
                                        @endforeach
                                    </select>
                                    @error('assignRows.'.$i.'.tyre_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </td>
                                <td>
                                    <select class="form-control" wire:model.defer="assignRows.{{ $i }}.axle">
                                        <option value="">Select Axle</option>
                                        @foreach (\App\Models\TyreAssignment::AXLES as $axleOption)
                                            <option value="{{ $axleOption }}">{{ $axleOption }}</option>
                                        @endforeach
                                    </select>
                                    @error('assignRows.'.$i.'.axle') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </td>
                                <td>
                                    <select class="form-control" wire:model.defer="assignRows.{{ $i }}.position">
                                        <option value="">Select Position</option>
                                        @foreach (\App\Models\TyreAssignment::POSITIONS as $positionValue => $positionLabel)
                                            <option value="{{ $positionValue }}">{{ $positionLabel }}</option>
                                        @endforeach
                                    </select>
                                    @error('assignRows.'.$i.'.position') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm" wire:click="removeRow({{ $i }})" title="Remove row"><i class="fa fa-times"></i></button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <button type="button" class="btn btn-default btn-sm" wire:click="addRow()"><i class="fa fa-plus"></i> Add Tyre</button>
                    <div class="form-group" style="margin-top:15px">
                        <label>Comments</label>
                        <textarea wire:model.defer="assign_comments" class="form-control" rows="3" placeholder="Enter Details"></textarea>
                        @error('assign_comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded" wire:loading.attr="disabled" wire:target="saveAssignments"><i class="fa fa-save"></i>Assign {{ count($assignRows) }} Tyre(s)</button>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="assetTyreUnassignModal" tabindex="-1" role="dialog" aria-labelledby="assetTyreUnassignModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="assetTyreUnassignModalLabel"><i class="fa fa-unlink"></i> Unassign {{ count($selected) }} Tyre(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="saveUnassignments()">
                <div class="modal-body">
                    @error('selected') <p class="error" style="color:red">{{ $message }}</p> @enderror
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Unassignment Mileage<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.defer="ending_odometer" placeholder="Enter Mileage" required>
                                @error('ending_odometer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Unassignment Date<span class="required" style="color: red">*</span></label>
                                <input type="date" max="{{ date('Y-m-d') }}" class="form-control" wire:model.defer="unassigned_date" required>
                                @error('unassigned_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Reason<span class="required" style="color: red">*</span></label>
                        <textarea wire:model.defer="unassignment_reason" class="form-control" rows="4" placeholder="e.g. Worn out, puncture, rotation, sent for retread..." required></textarea>
                        @error('unassignment_reason') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-warning btn-wide btn-rounded" wire:loading.attr="disabled" wire:target="saveUnassignments"><i class="fa fa-unlink"></i>Unassign</button>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        if (!window.__assetTyreModalsBound) {
            window.__assetTyreModalsBound = true;
            ['assetTyreAssignModal', 'assetTyreUnassignModal'].forEach(function (id) {
                window.addEventListener('show-' + id, function () { $('#' + id).modal('show'); });
                window.addEventListener('hide-' + id, function () { $('#' + id).modal('hide'); });
            });
        }
    </script>
</div>
