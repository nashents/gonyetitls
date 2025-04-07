<div>
    <div class="row mt-30">
        <!-- /.col-md-3 -->
        <div class="col-md-10 col-md-offset-1">
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
                                <th class="w-10 text-center line-height-35">CheckedBy</th>
                                <td class="w-20 line-height-35">
                                    {{$checklist->user ? $checklist->user->name : ""}} {{$checklist->user ? $checklist->user->surname : ""}}
                                </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Checked On</th>
                                <td class="w-20 line-height-35">{{$checklist->date}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">DrivenBy</th>
                                <td class="w-20 line-height-35">
                                    @if ($checklist->employee)
                                    {{$checklist->employee ? $checklist->employee->name : ""}} {{$checklist->employee ? $checklist->employee->surname : ""}}        
                                    @elseif($checklist->driver)
                                    {{$checklist->driver->employee ? $checklist->driver->employee->name : ""}} {{$checklist->driver->employee ? $checklist->driver->employee->surname : ""}}
                                    @endif   
                                </td>
                            </tr>

                            @if (isset($checklist->horse))
                            <tr>
                                <th class="w-10 text-center line-height-35">Horse Details</th>
                                <td class="w-20 line-height-35">
                                    @if ($checklist->horse)
                                    <a href="{{route('horses.show',$checklist->horse->id)}}">{{$checklist->horse->horse_make ? $checklist->horse->horse_make->name : "undefined make & model"}} {{$checklist->horse->horse_model ? $checklist->horse->horse_model->name : ""}} [HRN: {{$checklist->horse->registration_number}}]</a>
                                    @endif
                                </td>
                            </tr>
                            @endif

                            @if (isset($checklist->vehicle))
                            <tr>
                                <th class="w-10 text-center line-height-35">Vehicle Details</th>
                                <td class="w-20 line-height-35">
                                    @if ($checklist->vehicle)
                                    <a href="{{route('vehicles.show',$checklist->vehicle->id)}}">{{$checklist->vehicle->vehicle_make ? $checklist->vehicle->vehicle_make->name : "undefined make & model"}} {{$checklist->vehicle->vehicle_model ? $checklist->vehicle->vehicle_model->name : ""}} [VRN: {{$checklist->vehicle->registration_number}}]</a>
                                    @endif
                                </td>
                            </tr>
                            @endif

                            @if (isset($checklist->trailer))
                            <tr>
                                <th class="w-10 text-center line-height-35">Trailer Details</th>
                                <td class="w-20 line-height-35">

                                    <a href="{{route('trailers.show',$checklist->trailer->id)}}">{{$checklist->trailer ? $checklist->trailer->make : "undefined make & model"}} {{$checklist->trailer ? $checklist->trailer->model : ""}} [TRN: {{$checklist->trailer->registration_number}}]</a>

                                </td>
                            </tr>
                            @endif

                            <tr>
                                <th class="w-10 text-center line-height-35">Comments</th>
                                <td class="w-20 line-height-35">{{$checklist->comments}}</td>
                            </tr>
              
                          

                            </tr>
                        </tbody>
                    </table>
                
                </div>
                <div role="tabpanel" class="tab-pane " id="results">

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
                                <td>{{$result->checklist_item ? $result->checklist_item->name : ""}}</td>
                                <td><span class="badge bg-{{($result->status == '1') ? 'success' : 'danger' }}">{{($result->status == '1') ? 'Yes' : 'No' }}</span></td>
                                <td>{{$result->comments}}</td>
                            </tr>
                            @endforeach
                        </tbody>
                      </table>


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
