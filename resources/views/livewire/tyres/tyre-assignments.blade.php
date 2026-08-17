<div>
    <div class="panel-title">
        <a href="#" wire:click="exportTyreAssignmentsExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
        <a href="#" wire:click="exportTyreAssignmentsCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
        <a href="#" wire:click="exportTyreAssignmentsPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a> 
    </div>
    <br>
    <table  class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
        <thead >
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
            </tr>
        </thead>
        <tbody>
            
            @forelse ($tyre_assignments as $tyre_assignment)
            <tr>
                @php
                    $tyre = $tyre_assignment->tyre;
                @endphp
                @if ($tyre)
                    <td>{{$tyre_assignment->tyre ? $tyre_assignment->tyre->tyre_number : ""}}</td>
                    <td>{{$tyre_assignment->tyre->product ? $tyre_assignment->tyre->product->name : ""}} {{$tyre_assignment->tyre->product && $tyre_assignment->tyre->product->brand ? $tyre_assignment->tyre->product->brand->name : ""}}</td>
                    <td>{{$tyre_assignment->tyre ? $tyre_assignment->tyre->serial_number : ""}}</td>
                    <td>{{$tyre_assignment->tyre ? $tyre_assignment->tyre->width : ""}} / {{$tyre_assignment->tyre ? $tyre_assignment->tyre->aspect_ratio : ""}} R {{$tyre_assignment->tyre ? $tyre_assignment->tyre->diameter : ""}}</td>
                    <td>{{$tyre_assignment->position}}</td>
                    <td>{{$tyre_assignment->axle}}</td>
                    <td>
                        <small><strong>Acquisition: </strong> {{Carbon\Carbon::parse($tyre_assignment->tyre->purchase_date)->format('d M Y')}}</small><br>
                        <small><strong>Age: </strong> {{ $tyre_assignment->tyre->age ?? '-' }}</small><br>
                        <small><strong>Fitted: </strong> {{ number_format($tyre_assignment->starting_odometer) }}</small> <br>
                        <small><strong>Current: </strong> {{ $tyre_assignment->ending_odometer ? number_format($tyre_assignment->ending_odometer) : number_format($tyre_assignment->horse->mileage ?? 0) }}</small> <br>
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
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="8">
                    <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                        No tyres assigned to horse found ....
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
</div>
