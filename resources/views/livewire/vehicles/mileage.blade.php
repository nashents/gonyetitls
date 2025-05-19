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
                                <a href="#" wire:click="exportVehiclesMileageExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                <a href="#" wire:click="exportVehiclesMileageCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                <a href="#" wire:click="exportVehiclesMileagePDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                            </div>
                        </div>

                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="vehiclesTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Transporter
                                    </th>
                                    <th class="th-sm">Vehicle
                                    </th>
                                                             <th class="th-sm">Prev Service Date
                                    </th>
                                    <th class="th-sm">
                                        Prev Mileage
                                        <hr style="margin-top:2px; margin-bottom:2px">
                                        Prev Hours
                                    </th>
                                   <th class="th-sm">
                                        Current Mileage
                                        <hr style="margin-top:2px; margin-bottom:2px">
                                        Current Hours
                                    </th>
                                    <th class="th-sm">
                                        Next Mileage
                                        <hr style="margin-top:2px; margin-bottom:2px">
                                        Next Hours
                                    </th>
                                    <th class="th-sm">Current - Next Service Diff
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                  </tr>
                                </thead>
                                @if ($vehicles->count()>0)
                                <tbody>
                                    @foreach ($vehicles as $vehicle)
                                  <tr>
                                    <td>{{$vehicle->transporter ? $vehicle->transporter->name : ""}}</td>
                                    <td>{{$vehicle->vehicle_make ? $vehicle->vehicle_make->name : ""}} {{$vehicle->vehicle_model ? $vehicle->vehicle_model->name : ""}} {{$vehicle->registration_number}} {{$vehicle->fleet_number ? "(".$vehicle->fleet_number.")" : ""}}</td>
                                 <td>{{$vehicle->prev_service_date}}</td> 
                                    <td>
                                            {{$vehicle->prev_service ? $vehicle->prev_service."Kms" : ""}}
                                            <hr style="margin-top:2px; margin-bottom:2px">
                                            {{$vehicle->prev_service_hours ? $vehicle->prev_service_hours."Hours" : ""}}
                                    </td>
                                
                                  
                                   <td>
                                            {{$vehicle->mileage ? $vehicle->mileage."Kms" : ""}}
                                            <hr style="margin-top:2px; margin-bottom:2px">
                                            {{$vehicle->hours ? $vehicle->hours."Hours" : ""}}
                                    </td>
                                    <td>
                                            {{$vehicle->next_service ? $vehicle->next_service."Kms" : ""}}
                                            <hr style="margin-top:2px; margin-bottom:2px">
                                            {{$vehicle->next_service_hours ? $vehicle->next_service_hours."Hours" : ""}}
                                    </td>
                                    <td>
                                        @if ((isset($vehicle->mileage) && $vehicle->mileage > 0) && (isset($vehicle->next_service) && $vehicle->next_service > 0))
                                            @php
                                                $difference = $vehicle->next_service - $vehicle->mileage;
                                                $hours_difference = $vehicle->next_service_hours - $vehicle->hours;
                                            @endphp
                                            {{$difference ? $difference."Kms" : ""}} 
                                            @if ($hours_difference)
                                                 {{$hours_difference ? $hours_difference."Hours" : ""}} 
                                            @endif
                                        @endif
                                    </td>
                                    <td>
                                        @if (((isset($vehicle->mileage) && $vehicle->mileage > 0) && (isset($vehicle->next_service) && $vehicle->next_service > 0)) || ((isset($vehicle->hours) && $vehicle->hours > 0) && (isset($vehicle->next_service_hours) && $vehicle->next_service_hours > 0)))
                                            @if (($vehicle->mileage >= $vehicle->next_service) || ($vehicle->hours >= $vehicle->next_service_hours))
                                                <span class="badge bg-danger">Due for service</span>
                                            @elseif(($vehicle->mileage < $vehicle->next_service) || ($vehicle->hours < $vehicle->next_service_hours))
                                                <span class="badge bg-success">Fit for use</span>
                                            @endif
                                        @endif
                                        
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#"  wire:click="edit({{$vehicle->id}})" ><i class="fa fa-refresh color-success"></i> Update</a></li>
                                            </ul>
                                        </div>
                                </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                                @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                 @endif
                              </table>

                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="mileageModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Update Mileage <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Prev Service Mileage</label>
                                <input type="number" step="any" min="0" wire:model.debounce.300ms="prev_service" class="form-control">
                                @error('prev_service') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
            
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Prev Service Date</label>
                                <input type="date"  wire:model.debounce.300ms="prev_service_date" class="form-control">
                                @error('prev_service_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Current Mileage<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" min="0" wire:model.debounce.300ms="mileage" class="form-control" required>
                                @error('mileage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
            
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Next Service Mileage<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" min="0" wire:model.debounce.300ms="next_service" class="form-control" required>
                                @error('next_service') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>



</div>

