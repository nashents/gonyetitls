<div>
    <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
        <thead>
          <tr>
           <th class="th-sm">Tyre
            </th>
            <th class="th-sm">Dimensions
            </th>
            <th class="th-sm">Location
            </th>
              <th class="th-sm">Usage
            </th>
            <th class="th-sm">Health Status
            </th>
            <th class="th-sm">Qty
            </th>
            <th class="th-sm">Ccy
            </th>
            <th class="th-sm">Rate
            </th>
            <th class="th-sm">Tax
            </th>
            <th class="th-sm">Cost
            </th>
            <th class="th-sm">Total
            </th>                        
            <th class="th-sm">Action
            </th>
          </tr>
        </thead>
        @if (isset($tyres))
        <tbody>
            @forelse ($tyres as $tyre)
          <tr>
            @php
                                $assignment = App\Models\TyreAssignment::with(['horse','tyre'])->where('tyre_id',$tyre->id)->where('status',1)->latest()->first();
                            @endphp
                        <td>
                            @if ($tyre->product)
                                {{$tyre->product->brand ? $tyre->product->brand->name : ""}} {{$tyre->product ? $tyre->product->name : ""}}
                                <br>
                            @endif
                            <small><strong>Type: </strong>  
                                @if ($tyre->type == "Diff")
                                      <span class="badge bg-primary">{{$tyre->type}}</span>
                                @elseif($tyre->type == "Supersingle")
                                    <span class="badge bg-info">{{$tyre->type}}</span>
                                @elseif($tyre->type == "Multipurpose")
                                    <span class="badge bg-success">{{$tyre->type}}</span>
                                @elseif($tyre->type == "Steer")
                                    <span class="badge bg-active">{{$tyre->type}}</span>
                                @endif
                                </small> <br>
                                @if ($tyre->serial_number)
                                    <small><strong>S#:</strong> {{$tyre->serial_number}}</small>
                                @endif
                        </td>
                        <td>
                            {{$tyre->width}} / {{$tyre->aspect_ratio}} R {{$tyre->diameter}}
                            <br>
                            <small><strong>Tread Depth(<i>mm</i>): </strong> {{number_format($tyre->thread_depth ?? Null,2)}}</small>
                            <br>
                            <small><strong>Pressure(<i>psi</i>): </strong> {{number_format($tyre->pressure_psi ?? Null,2)}}</small>
                            <br>
                            <small><strong>Life Span(<i>kms</i>): </strong> {{number_format($tyre->life_span ?? Null,2)}}</small>

                        
                        </td>
                        <td>
                            
                            @if ($assignment)
                                <a href="{{route('tyre_assignments.show',$assignment->id)}}" style="color: blue">
                                @if ($assignment->horse)
                                    Horse: {{$assignment->horse->registration_number}} {{$assignment->horse->fleet_number ? "(".$assignment->horse->fleet_number.")" : ""}}
                                @elseif($assignment->trailer)
                                    Trailer: {{$assignment->trailer->registration_number}} {{$assignment->trailer->fleet_number ? "(".$assignment->trailer->fleet_number.")" : ""}}
                                @elseif($assignment->vehicle)
                                    Vehicle: {{$assignment->horse->registration_number}} {{$assignment->vehicle->fleet_number ? "(".$assignment->vehicle->fleet_number.")" : ""}}
                                @endif
                                </a>
                                <br>
                                <small><strong>{{$assignment->axle}} {{$assignment->position}}</strong></small>
                            @else
                                @if ($tyre->retread == 0)
                                    <span class="badge bg-success">Instore</span>
                                    <br>
                                    {{$tyre->store ? $tyre->store->name : ""}}
                                @else
                                    <span class="badge bg-warning">Retread</span>
                                @endif  
                            @endif
                        </td>
                            <td>
                                <small><strong>Acquisition: </strong> {{Carbon\Carbon::parse($tyre->purchase_date)->format('d M Y')}}</small><br>
                                <small><strong>Age: </strong> {{ $tyre->age ?? '-' }}</small>
                            @if ($assignment)
                                <br>
                                <small><strong>Fitted: </strong> {{ number_format($assignment->starting_odometer) }}</small> <br>
                                <small><strong>Current: </strong> {{ $assignment->ending_odometer ? number_format($assignment->ending_odometer) : number_format($assignment->horse->mileage ?? 0) }}</small> <br>
                                <small><strong>Travelled: </strong> {{ number_format($assignment->travelled_km ?? 0) }} km</small> <br>
                                <small><strong>Life(Standard): </strong> {{ number_format($tyre->life_span ?? 0) }} km</small> <br>
                                <small><strong>Remaining: </strong>
                                    @php $rem = $assignment->remaining_km; $pct = $assignment->remaining_pct; @endphp
                                        {{ is_null($rem) ? '-' : number_format($rem) . ' km' }}
                                    @if(!is_null($pct))
                                        ({{ $pct }}%)
                                    @endif</small> <br>
                            @endif
                        </td>
                        <td>
                            @php
                                    $checklist_result = App\Models\ChecklistResult::where('tyre_id',$tyre->id)->first();
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
                        <td>{{$tyre->qty}}</td>
                        <td>{{$tyre->currency ? $tyre->currency->name : ""}}</td>
                        <td>
                            {{$tyre->currency ? $tyre->currency->symbol : ""}}{{number_format($tyre->amount ? $tyre->amount : 0,2)}}  
                        </td>
                        <td>
                            {{$tyre->currency ? $tyre->currency->symbol : ""}}{{number_format($tyre->tax_amount ? $tyre->tax_amount : 0,2)}}  
                        </td>
                        <td>
                            {{$tyre->currency ? $tyre->currency->symbol : ""}}{{number_format($tyre->cost ? $tyre->cost : 0,2)}}  
                        </td>
                        <td>
                              {{$tyre->currency ? $tyre->currency->symbol : ""}}{{number_format($tyre->total ? $tyre->total : 0,2)}}  
                            @if (Auth::user()->employee->company->currency_id != $tyre->currency_id)
                                <br>
                                <small>
                                    <strong>Exc Rate:</strong> {{number_format($tyre->exchange_rate,2)}} <br>
                                    <strong>Exc Total:</strong> {{Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->name : ""}} {{Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->symbol : ""}}{{number_format($tyre->exchange_amount,2)}}
                                </small>
                            @endif
                        </td>
            <td class="w-10 line-height-35 table-dropdown">
                <div class="dropdown">
                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-bars"></i>
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a href="{{ route('tyres.show',$tyre->id) }}" ><i class="fa fa-eye color-default"></i>View</a></li>
                      </ul>
                </div>
        </td>
          </tr>
          @empty
          <tr>
            <td colspan="10">
                <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                    No tyres from store found ....
                </div>
               
            </td>
          </tr>  
            @endforelse
        </tbody>
        @else
            <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
         @endif
      </table>
      <nav class="text-center" style="float: right">
        <ul class="pagination rounded-corners">
            @if (isset($tyres))
                {{ $tyres->links() }} 
            @endif 
        </ul>
    </nav>    
</div>
