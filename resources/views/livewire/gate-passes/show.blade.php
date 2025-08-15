<div>
    <div class="row mt-30">
    
        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1" >

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">GatePass Details</a></li>
                @if ($gate_pass->type == "Trip")
                <li role="presentation" ><a href="#inspections" aria-controls="inspections" role="tab" data-toggle="tab">Inspections</a></li>
                @endif
               

            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">

                        <tbody class="text-center line-height-35 ">

                            <tr>
                                <th class="w-10 text-center line-height-35">CreatedBy</th>
                                <td class="w-20 line-height-35">{{$gate_pass->user ? $gate_pass->user->name : ""}} {{$gate_pass->user ? $gate_pass->user->surname : ""}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Type</th>
                                <td class="w-20 line-height-35">{{$gate_pass->type}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">For</th>
                                <td class="w-20 line-height-35">{{$gate_pass->for}}</td>
                            </tr>
                            @if ($gate_pass->group)
                                <tr>
                                    <th class="w-10 text-center line-height-35">Group</th>
                                    <td class="w-20 line-height-35">{{$gate_pass->group ? $gate_pass->group->name : ""}}</td>
                                </tr>
                            @endif
                            @if ($gate_pass->visitor)
                                <tr>
                                    <th class="w-10 text-center line-height-35">Group</th>
                                    <td class="w-20 line-height-35">
                                        {{$gate_pass->visitor ? $gate_pass->visitor->name : ""}}
                                        @if ($gate_pass->vrn)
                                            <br>
                                            {{$gate_pass->make}} {{$gate_pass->vrn}}
                                        @endif
                                    </td>
                                </tr>
                            @endif
                          
                            @php
                                 $invited_by = App\Models\Employee::find($gate_pass->invited_by_id);
                            @endphp
                                <tr>
                                    <th class="w-10 text-center line-height-35">Invited By</th>
                                    <td class="w-20 line-height-35">
                                       @if ($invited_by)
                                            {{$invited_by->name}} {{$invited_by->surname}}
                                        @elseif($gate_pass->employee)
                                            {{$gate_pass->employee ? $gate_pass->employee->name : ""}} {{$gate_pass->employee ? $gate_pass->employee->surname : ""}} 
                                        @endif
                                    </td>
                                </tr>
                          
                            @if ($gate_pass->trip)
                                <tr>
                                    <th class="w-10 text-center line-height-35">Trip</th>
                                    <td class="w-20 line-height-35">{{$gate_pass->trip->trip_number}}</td>
                                </tr>
                            @endif
                            @if ($gate_pass->horse)
                                <tr>
                                    <th class="w-10 text-center line-height-35">Horse</th>
                                    <td class="w-20 line-height-35">{{$gate_pass->horse->registration_number}}</td>
                                </tr>
                            @endif
                            @if ($gate_pass->trailers->count()>0)
                            <tr>
                                <th class="w-10 text-center line-height-35">Trailers</th>
                                <td class="w-20 line-height-35">
                                    @foreach ($gate_pass->trailers as $trailer)
                                        {{ $trailer->registration_number }} {{ $trailer->make }} {{ $trailer->model }}
                                    @endforeach
                                </td>
                            </tr>
                            @endif
                            @if ($gate_pass->driver)
                            <tr>
                                <th class="w-10 text-center line-height-35">Driver</th>
                                <td class="w-20 line-height-35">{{$gate_pass->driver->employee ? $gate_pass->driver->employee->name : ""}} {{$gate_pass->driver->employee ? $gate_pass->driver->employee->surname : ""}}</td>
                            </tr>
                            @endif
                            
                            <tr>
                                <th class="w-10 text-center line-height-35">Entry</th>
                                <td class="w-20 line-height-35">{{$gate_pass->entry}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Exit</th>
                                <td class="w-20 line-height-35">{{$gate_pass->exit}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Purpose of visit</th>
                                <td class="w-20 line-height-35">{{$gate_pass->reason}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Signature</th>
                                <td class="w-20 line-height-35"><img src="{{ asset('images/uploads/' . $gate_pass->signature) }}" style="width: 25%; height:25%" alt="Signature" /></td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Authorization</th>
                                <td class="w-20 line-height-35"><span class="badge bg-{{($gate_pass->authorization == 'approved') ? 'success' : (($gate_pass->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($gate_pass->authorization == 'approved') ? 'approved' : (($gate_pass->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                            </tr>
                              
                             
                        </tbody>
                    </table>
                </div>
                @if ($gate_pass->type == "Trip")
                <div role="tabpanel" class="tab-pane " id="inspections">
                    @livewire('gate-passes.inspections', ['id' => $gate_pass->id])
                </div>
                @endif
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="btn-group pull-right mt-10" >
                           <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                        </div>
                    </div>
                    </div>

            </div>
        </div>
        <!-- /.col-md-9 -->
    </div>
   
</div>
