<div>
    <div class="row mt-30">
    
        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1" >

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Shift Details</a></li>
                @if ($shift->for === "Trips")
                    <li role="presentation"><a href="#trips" aria-controls="trips" role="tab" data-toggle="tab">Trips</a></li>
                @elseif($shift->for === "Rehandling")
                    <li role="presentation"><a href="#rehandling" aria-controls="rehandling" role="tab" data-toggle="tab">Rehandling Work</a></li>
                @endif 
            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">

                        <tbody class="text-center line-height-35 ">

                            <tr>
                                <th class="w-10 text-center line-height-35">CreatedBy</th>
                                <td class="w-20 line-height-35">{{$shift->user ? $shift->user->name : ""}} {{$shift->user ? $shift->user->surname : ""}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Type</th>
                                <td class="w-20 line-height-35">{{$shift->type}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">For</th>
                                <td class="w-20 line-height-35">{{$shift->for}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Customer</th>
                                <td class="w-20 line-height-35">{{$shift->customer ? $shift->customer->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Transporter</th>
                                <td class="w-20 line-height-35">{{$shift->transporter ? $shift->transporter->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Driver</th>
                                <td class="w-20 line-height-35">{{$shift->driver->employee ? $shift->driver->employee->name : ""}} {{$shift->driver->employee ? $shift->driver->employee->surname : ""}}</td>
                            </tr>
                          
                            <tr>
                                <th class="w-10 text-center line-height-35">Equipment</th>
                                <td class="w-20 line-height-35">
                                    @if ($shift->horse)
                                        {{$shift->horse->registration_number}}        
                                    @elseif($shift->vehicle)
                                        {{$shift->vehicle->registration_number}}     
                                    @endif
                                </td>
                            </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Status</th>
                                    <td class="w-20 line-height-35"><span class="badge bg-{{$shift->status == 1 ? "success" : "danger"}}">{{$shift->status == 1 ? "Active" : "Inactive"}}</span></td>
                                </tr>
                        </tbody>
                    </table>
                </div>
                @if ($shift->for === "Trips")
                    <div role="tabpanel" class="tab-pane" id="trips">
                    @livewire('shifts.trips', ['id' => $shift->id])
                    </div> 
                @elseif ($shift->for === "Rehandling")
                  <div role="tabpanel" class="tab-pane" id="rehandling">
                    @livewire('shifts.rehandlings', ['id' => $shift->id])
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
