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
                                <a href="#" data-toggle="modal" data-target="#logModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Log</a>
                                {{-- <a href="" data-toggle="modal" data-target="#logsImportModal" class="btn btn-default border-primary btn-wide"><i class="fa fa-upload"></i>Import</a> --}}
                                {{-- <a href="#" wire:click="exportlogsExcel()"  class="btn btn-default border-primary btn-wide"><i class="fa fa-download"></i>Excel</a>
                                <a href="#" wire:click="exportlogsCSV()" class="btn btn-default border-primary btn-wide"><i class="fa fa-download"></i>CSV</a>
                                <a href="#" wire:click="exportlogsPDF()" class="btn btn-default border-primary btn-wide"><i class="fa fa-download"></i>PDF</a> --}}
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="logsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Log#
                                    </th>
                                    <th class="th-sm">Driver
                                    </th>
                                    <th class="th-sm">Vehicle
                                    </th>
                                    <th class="th-sm">From
                                    </th>
                                    <th class="th-sm">Departure
                                    </th>
                                    <th class="th-sm">Starting Mileage
                                    </th>
                                    <th class="th-sm">To
                                    </th>
                                    <th class="th-sm">Arrival
                                    </th>
                                    <th class="th-sm">Ending Mileage
                                    </th>
                                    <th class="th-sm">Distance
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($logs->count()>0)
                                <tbody>
                                    @foreach ($logs as $log)
                                  <tr>
                                    <td>{{$log->log_number}}</td>
                                    <td>{{$log->employee ? $log->employee->name : ""}} {{$log->employee ? $log->employee->surname : ""}}</td>
                                    <td>
                                        @if ($log->vehicle)
                                        {{$log->vehicle->registration_number}} {{$log->vehicle->vehicle_make ? $log->vehicle->vehicle_make->name : ""}} {{$log->vehicle->vehicle_make ? $log->vehicle->vehicle_model->name : ""}} 
                                        @endif
                                    </td> 
                                    <td>{{$log->from}}</td>
                                    <td>{{$log->departure_datetime}}</td>
                                    <td>
                                        @if ($log->starting_mileage)
                                        {{$log->starting_mileage}}Kms
                                        @endif
                                    </td>
                                    <td>{{$log->to}}</td>
                                    <td>{{$log->arrival_datetime}}</td>
                                    <td>
                                        @if ($log->ending_mileage)
                                        {{$log->ending_mileage}}Kms
                                        @endif
                                    </td>
                                    <td>
                                        @if ($log->distance)
                                        {{$log->distance}}Kms 
                                        @endif
                                    </td>
                                    <td><span class="badge bg-{{$log->status == 1 ? "warning" : "success"}}">{{$log->status == 1 ? "Open" : "Closed"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('logs.show', $log->id) }}" ><i class="fa fa-eye color-default"></i> View</a></li>
                                                <li><a href="#"  wire:click="edit({{$log->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                @if ($log->status == 1)
                                                <li><a href="#"  wire:click="editUpdate({{$log->id}})" ><i class="fa fa-refresh color-primary"></i> Update</a></li>
                                                @endif
                                              
                                                <li><a href="#" data-toggle="modal" data-target="#logDeleteModal{{ $log->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('logs.delete')
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="logModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Trip Log <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Employees<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="selectedEmployee" class="form-control" required >
                                   <option value="">Select Employee</option>
                                   @foreach ($employees as $employee)
                                   <option value="{{ $employee->id }}">{{ $employee->name }} {{ $employee->surname }}</option>
                                   @endforeach
                                 
                               </select>
                                @error('selectedEmployee') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Vehicles<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="selectedVehicle" class="form-control" required >
                                   <option value="">Select Vehicle</option>
                                   @if (!is_null($selectedEmployee))
                                        @foreach ($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}">{{ $vehicle->vehicle_make ? $vehicle->vehicle_make->name : "" }} {{ $vehicle->vehicle_model ? $vehicle->vehicle_model->name : "" }}{{ $vehicle->registration_number }}</option>
                                        @endforeach
                                   @endif                         
                               </select>
                                @error('selectedVehicle') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">From<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="from" placeholder="Enter Current Location" required />
                                @error('from') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">To<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="to" placeholder="Enter Destination Location" required />
                                @error('to') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>   
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Departure Date & Time<span class="required" style="color: red">*</span></label>
                                <input type="datetime-local" class="form-control" wire:model.debounce.300ms="departure_datetime" placeholder="Enter Departure Date & Time" required />
                                @error('departure_datetime') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Arrival Date & Time</label>
                                <input type="datetime-local" class="form-control" wire:model.debounce.300ms="arrival_datetime" placeholder="Enter Destination Arrival Date & Time" />
                                @error('arrival_datetime') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Starting Mileage<span class="required" style="color: red">*</span></label>
                                <input type="number" min="{{ $starting_mileage }}" step="any" class="form-control" wire:model.debounce.300ms="starting_mileage" placeholder="Enter Starting Mileage" required />
                                @error('starting_mileage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Ending Mileage</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="ending_mileage" placeholder="Enter Ending Mileage"  />
                                @error('ending_mileage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                     </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Distance<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="distance" placeholder="Trip Distance" disabled required />
                                    @error('distance') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Notes</label>
                                   <textarea class="form-control" wire:model.debounce.300ms="notes" cols="30" rows="4"></textarea>
                                    @error('notes') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                       
                    </div>
                
               
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Save</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="logEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Trip Log <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Employees<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="selectedEmployee" class="form-control" required >
                                   <option value="">Select Employee</option>
                                   @foreach ($employees as $employee)
                                   <option value="{{ $employee->id }}">{{ $employee->name }} {{ $employee->surname }}</option>
                                   @endforeach
                                 
                               </select>
                                @error('selectedEmployee') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Vehicles<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="selectedVehicle" class="form-control" required >
                                   <option value="">Select Vehicle</option>
                                   @if (!is_null($selectedEmployee))
                                        @foreach ($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}">{{ $vehicle->vehicle_make ? $vehicle->vehicle_make->name : "" }} {{ $vehicle->vehicle_model ? $vehicle->vehicle_model->name : "" }}{{ $vehicle->registration_number }}</option>
                                        @endforeach
                                   @endif                         
                               </select>
                                @error('selectedVehicle') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">From<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="from" placeholder="Enter Current Location" required />
                                @error('from') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">To<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="to" placeholder="Enter Destination Location" required />
                                @error('to') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>   
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Departure Date & Time<span class="required" style="color: red">*</span></label>
                                <input type="datetime-local" class="form-control" wire:model.debounce.300ms="departure_datetime" placeholder="Enter Departure Date & Time" required />
                                @error('departure_datetime') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Arrival Date & Time</label>
                                <input type="datetime-local" class="form-control" wire:model.debounce.300ms="arrival_datetime" placeholder="Enter Destination Arrival Date & Time" />
                                @error('arrival_datetime') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Starting Mileage<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="starting_mileage" placeholder="Enter Starting Mileage" required />
                                @error('starting_mileage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Ending Mileage</label>
                                <input type="number" min="{{ $starting_mileage }}"  step="any" class="form-control" wire:model.debounce.300ms="ending_mileage" placeholder="Enter Ending Mileage"  />
                                @error('ending_mileage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                     </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Distance<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="distance" placeholder="Trip Distance" disabled required />
                                    @error('distance') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Notes</label>
                                   <textarea class="form-control" wire:model.debounce.300ms="notes" cols="30" rows="4"></textarea>
                                    @error('notes') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="logUpdateModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Update Trip Log <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="updateLog()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Employees<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="selectedEmployee" class="form-control" required >
                                   <option value="">Select Employee</option>
                                   @foreach ($employees as $employee)
                                   <option value="{{ $employee->id }}">{{ $employee->name }} {{ $employee->surname }}</option>
                                   @endforeach
                                 
                               </select>
                                @error('selectedEmployee') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Vehicles<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="selectedVehicle" class="form-control" required >
                                   <option value="">Select Vehicle</option>
                                   @if (!is_null($selectedEmployee))
                                        @foreach ($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}">{{ $vehicle->vehicle_make ? $vehicle->vehicle_make->name : "" }} {{ $vehicle->vehicle_model ? $vehicle->vehicle_model->name : "" }}{{ $vehicle->registration_number }}</option>
                                        @endforeach
                                   @endif                         
                               </select>
                                @error('selectedVehicle') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">From<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="from" placeholder="Enter Current Location" required />
                                @error('from') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">To<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="to" placeholder="Enter Destination Location" required />
                                @error('to') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>   
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Departure Date & Time<span class="required" style="color: red">*</span></label>
                                <input type="datetime-local" class="form-control" wire:model.debounce.300ms="departure_datetime" placeholder="Enter Departure Date & Time" required />
                                @error('departure_datetime') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Arrival Date & Time<span class="required" style="color: red">*</span></label>
                                <input type="datetime-local" class="form-control" wire:model.debounce.300ms="arrival_datetime" placeholder="Enter Destination Arrival Date & Time" required/>
                                @error('arrival_datetime') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Starting Mileage<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="starting_mileage" placeholder="Enter Starting Mileage" required />
                                @error('starting_mileage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Ending Mileage<span class="required" style="color: red">*</span></label>
                                <input type="number" min="{{ $starting_mileage }}" step="any" class="form-control" wire:model.debounce.300ms="ending_mileage" placeholder="Enter Ending Mileage" required />
                                @error('ending_mileage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                     </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Distance<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="distance" placeholder="Trip Distance" disabled required />
                                    @error('distance') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Notes</label>
                                   <textarea class="form-control" wire:model.debounce.300ms="notes" cols="30" rows="4"></textarea>
                                    @error('notes') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

