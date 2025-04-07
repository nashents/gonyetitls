<div>
    <div class="row mt-30">
    
        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1" >

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Trip Log Details</a></li>
            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">

                        <tbody class="text-center line-height-35 ">

                            <tr>
                                <th class="w-10 text-center line-height-35">CreatedBy</th>
                                <td class="w-20 line-height-35">{{$log->user ? $log->user->name : ""}} {{$log->user ? $log->user->surname : ""}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Log #</th>
                                <td class="w-20 line-height-35">{{$log->log_number}}</td>
                            </tr>
                          
                                <tr>
                                    <th class="w-10 text-center line-height-35">Driver</th>
                                    <td class="w-20 line-height-35">{{$log->employee ? $log->employee->name : ""}} {{$log->employee ? $log->employee->surname : ""}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Vehicle</th>
                                    <td class="w-20 line-height-35">{{$log->vehicle->vehicle_make ? $log->vehicle->vehicle_make->name : ""}} {{$log->vehicle->vehicle_make ? $log->vehicle->vehicle_model->name : ""}} {{$log->vehicle ? $log->vehicle->registration_number : ""}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">From</th>
                                    <td class="w-20 line-height-35">{{$log->from}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">To</th>
                                    <td class="w-20 line-height-35">{{$log->to}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Departure</th>
                                    <td class="w-20 line-height-35">{{$log->departure_datetime}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Starting Mileage</th>
                                    <td class="w-20 line-height-35">
                                        @if ($log->starting_mileage)
                                        {{$log->starting_mileage}}Kms
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Starting Mileage Image</th>
                                    <td class="w-20 line-height-35">
                                        @if ($log->starting_mileage_image)
                                            <a href="{{ asset('images/uploads/'.$log->starting_mileage_image) }}"><img src="{{ asset('images/uploads/'.$log->starting_mileage_image) }}" style="width: 200px; height:200px;" alt=""></a>
                                        @else
                                        <div class="col-lg-6">
                                            <div class="input-group">
                                                <input type="file" wire:model.debounce.300ms="starting_mileage_image" class="form-control" placeholder="Search for...">
                                                <span class="input-group-btn">
                                                    <button type="button" wire:click.prevent="uploadImage()" class="btn btn-default btn-rounded btn-wide"><i class="fa fa-upload"></i>Upload Image</button>
                                          </span>
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                        @endif
                                    
                                    </td>
                                </tr>
                               
                                <tr>
                                    <th class="w-10 text-center line-height-35">Arrival</th>
                                    <td class="w-20 line-height-35">{{$log->arrival_datetime}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Ending Mileage</th>
                                    <td class="w-20 line-height-35">
                                        @if ($log->ending_mileage)
                                        {{$log->ending_mileage}}Kms
                                        @endif
                                    </td>
                                </tr>

                                <tr>
                                    <th class="w-10 text-center line-height-35">Ending Mileage Image</th>
                                    <td class="w-20 line-height-35">
                                        @if (isset($log->ending_mileage_image))
                                        <a href="{{ asset('images/uploads/'.$log->ending_mileage_image) }}"><img src="{{ asset('images/uploads/'.$log->ending_mileage_image) }}" style="width: 200px; height:200px;" alt=""></a>
                                        @else   
                                        <div class="col-lg-6">
                                            <div class="input-group">
                                                <input type="file" wire:model.debounce.300ms="ending_mileage_image" class="form-control" placeholder="Search for...">
                                                <span class="input-group-btn">
                                                    <button type="button" wire:click.prevent="uploadImage()" class="btn btn-default btn-rounded btn-wide"><i class="fa fa-upload"></i>Upload Image</button>
                                          </span>
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                        
                                        @endif
                                    
                                    </td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Distance</th>
                                    <td class="w-20 line-height-35">
                                        @if ($log->distance)
                                        {{$log->distance}}Kms
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Notes</th>
                                    <td class="w-20 line-height-35">{{$log->notes}}</td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Status</th>
                                    <td class="w-20 line-height-35"><span class="badge bg-{{$log->status == 1 ? "warning" : "success"}}">{{$log->status == 1 ? "Open" : "Closed"}}</span></td>
                                </tr>
                             
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

            </div>
        </div>
        <!-- /.col-md-9 -->
    </div>
   
</div>
