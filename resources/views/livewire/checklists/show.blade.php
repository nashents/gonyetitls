<div>
    <div class="row mt-30">
        <!-- /.col-md-3 -->
        <div class="col-md-12">
            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#checklist" aria-controls="checklist" role="tab" data-toggle="tab">Inspection Details</a></li>
                <li role="presentation" ><a href="#results" aria-controls="results" role="tab" data-toggle="tab">Inspection Results</a></li>
               

            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="checklist">
                    <table class="table table-striped">
                        <tbody class="text-center line-height-35 ">
                            <tr>
                                <th class="w-10 text-center line-height-35">Inspection#</th>
                                <td class="w-20 line-height-35">{{$checklist->checklist_number}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Inspected By</th>
                                <td class="w-20 line-height-35">
                                    {{$checklist->user ? $checklist->user->name : ""}} {{$checklist->user ? $checklist->user->surname : ""}}
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Checklist</th>
                                <td class="w-20 line-height-35">
                                    {{$checklist->checklist_category ? $checklist->checklist_category->name : ""}}
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Inspected On</th>
                                <td class="w-20 line-height-35">{{Carbon\Carbon::parse($checklist->date)->format('F j, Y g:i A')}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Next Inspection Date</th>
                                <td class="w-20 line-height-35">{{Carbon\Carbon::parse($checklist->next_inspection_at)->format('F j, Y g:i A')}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Driver</th>
                                <td class="w-20 line-height-35">
                                    @if ($checklist->employee)
                                        {{$checklist->employee ? $checklist->employee->name : ""}} {{$checklist->employee ? $checklist->employee->surname : ""}}        
                                    @elseif($checklist->driver)
                                        {{$checklist->driver->employee ? $checklist->driver->employee->name : ""}} {{$checklist->driver->employee ? $checklist->driver->employee->surname : ""}}
                                    @endif   
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Inspection For</th>
                                <td class="w-20 line-height-35">
                                        @if ($checklist->horse)
                                            Horse | {{$checklist->horse->registration_number}} {{$checklist->horse->fleet_number ?  "(".$checklist->horse->fleet_number.")" : ""}}
                                        @elseif($checklist->vehicle)
                                            Vehicle | {{$checklist->vehicle->registration_number}} {{$checklist->vehicle->fleet_number ?  "(".$checklist->vehicle->fleet_number.")" : ""}}
                                        @elseif($checklist->trailer)
                                            Trailer | {{$checklist->trailer ? $checklist->trailer->registration_number : ""}} {{$checklist->trailer->fleet_number ?  "(".$checklist->trailer->fleet_number.")" : ""}}
                                        @endif
                                        
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Comments</th>
                                <td class="w-20 line-height-35">{{$checklist->comments}}</td>
                            </tr>
              
                          

                            </tr>
                        </tbody>
                    </table>
                
                </div>
                <div role="tabpanel" class="tab-pane " id="results">

                    @if ($checklist->checklist_category->name != "Tyre Inspection")
                        <table id="checklist_resultsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                        <thead >
                         <tr>
                            <th class="th-sm">Inspection Group
                            </th>
                            <th class="th-sm">Inspection Item
                            </th>
                            <th class="th-sm">Status
                            </th>
                            <th class="th-sm">Comments
                            </th>
                          </tr>
                        </thead>
                        <tbody>
                           @foreach ($checklist_results as  $result)
                            <tr>
                                <td>{{$result->checklist_item->checklist_sub_category ? $result->checklist_item->checklist_sub_category->name : ""}}</td>
                                <td>
                                    {{$result->checklist_item ? $result->checklist_item->name : ""}}
                                </td>
                                <td><span class="badge bg-{{($result->status == '1') ? 'success' : 'danger' }}">{{($result->status == '1') ? 'Yes' : 'No' }}</span></td>
                                <td>{{$result->comments}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                      </table>
                    @else
                    <table id="checklist_resultsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                        <thead >
                         <tr>
                            <th class="th-sm" style="width: 15%">Tyre
                            </th>
                            <th class="th-sm">Tread Depth(<i>mm</i>)
                            </th>
                            <th class="th-sm">Pressure(<i>PSI</i>)
                            </th>
                            <th class="th-sm">Wear Pattern
                            </th>
                            <th class="th-sm">Sidewall Damage
                            </th>
                            <th class="th-sm">Valve
                            </th>
                            <th class="th-sm">Nuts Torqued
                            </th>
                            <th class="th-sm">Axle Match
                            </th>
                            <th class="th-sm">Rim Condition
                            </th>
                            <th class="th-sm">Rating
                            </th>
                            <th class="th-sm">Actions
                            </th>
                           
                          </tr>
                        </thead>

                        <tbody>
                           @foreach ($checklist_results as  $result)
                            <tr>
                                <td>
                                    @if ($result->tyre)
                                        <strong>{{$result->tyre->product ? $result->tyre->product->name : ""}} {{$result->tyre->product->brand ? $result->tyre->product->brand->name : ""}} {{$result->tyre->serial_number}}</strong></strong>
                                    @endif
                                    @if ($result->tyre_assignment)
                                        <br>
                                        {{$result->tyre_assignment->axle}} {{$result->tyre_assignment->position}}
                                    @endif
                                    @if ($result->notes)
                                        <br>
                                        <small><strong>Notes:</strong> {{$result->notes}}</small> 
                                    @endif
                                </td>
                                <td>{{$result->tread_depth_mm}}</td>
                                <td>{{$result->pressure_psi}}</td>
                                <td>{{$result->wear_pattern}}</td>
                                <td>{{$result->sidewall_damage}}</td>
                                <td>
                                    <span class="badge bg-{{($result->valve_ok == '1') ? 'success' : 'danger' }}">{{($result->valve_ok == '1') ? 'Tight' : 'Leaking' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{($result->wheel_nuts_torqued == '1') ? 'success' : 'danger' }}">{{($result->wheel_nuts_torqued == '1') ? 'Yes' : 'No' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{($result->axle_match == '1') ? 'success' : 'danger' }}">{{($result->axle_match == '1') ? 'Yes' : 'No' }}</span>
                                </td>
                                <td>{{$result->rim_condition}}</td>
                                <td>
                                    @for ($i = 1; $i <= 5; $i++)
                                        <span style="color: {{ $i <= $result->rating ? '#FFD700' : '#ccc' }};">★</span>
                                    @endfor
                                </td>
                                <td>{{$result->action_required}}</td>
                            
                            </tr>
                            @endforeach
                        </tbody>
                      </table>
                    @endif

                    


                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="btn-group pull-right mt-10" >
                               <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                            </div>
                        </div>
                        </div>
                <!-- /.section-title -->
            </div>
        </div>
        <!-- /.col-md-9 -->
    </div>




</div>
