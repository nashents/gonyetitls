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
                                <a href="" data-toggle="modal" data-target="#vehicle_assignmentModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Employee - Vehicle Assignment</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="vehicle_assignmentsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                  
                                    <th class="th-sm">Vehicle
                                    </th>
                                    <th class="th-sm">Employee
                                    </th>
                                    <th class="th-sm">Starting Odometer
                                    </th>
                                    <th class="th-sm">Ending Odometer
                                    </th>
                                    <th class="th-sm">Start Date
                                    </th>
                                    <th class="th-sm">End Date
                                    </th>
                                    <th class="th-sm">Comments
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($vehicle_assignments->count()>0)
                                <tbody>
                                    @foreach ($vehicle_assignments as $vehicle_assignment)
                                  <tr>
                                    <td>
                                        @if ($vehicle_assignment->vehicle)
                                           {{$vehicle_assignment->vehicle->vehicle_make ? $vehicle_assignment->vehicle->vehicle_make->name : "undefined"}} {{$vehicle_assignment->vehicle->vehicle_model ? $vehicle_assignment->vehicle->vehicle_model->name : ""}} ({{$vehicle_assignment->vehicle->registration_number}})
                                        @endif
                                    </td>
                                    <td>{{ucfirst($vehicle_assignment->employee->name)}} {{ucfirst($vehicle_assignment->employee->surname)}}</td>
                                    <td>{{$vehicle_assignment->starting_odometer}}Kms</td>

                                    <td>{{$vehicle_assignment->status == 0 ? $vehicle_assignment->ending_odometer : ""}} Kms</td>

                                    <td>{{$vehicle_assignment->start_date}}</td>

                                    <td>{{$vehicle_assignment->status == 0 ? $vehicle_assignment->end_date : ""}}</td>

                                    <td>{{$vehicle_assignment->comments}}</td>
                                    <td><span class="label label-{{$vehicle_assignment->status == 1 ? "success" : "danger"}} label-rounded">{{$vehicle_assignment->status == 1 ? "Assigned" : "Unassigned"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#"  wire:click="edit({{$vehicle_assignment->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                @if ($vehicle_assignment->status == 1)
                                                <li><a href="#"  wire:click="unAssignment({{$vehicle_assignment->id}})" ><i class="fa fa-undo color-success"></i> Unassign</a></li>
                                                @endif
                                                <li><a href="#" data-toggle="modal" data-target="#vehicle_assignmentDeleteModal{{ $vehicle_assignment->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('vehicle_assignments.delete')
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="vehicle_assignmentModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-user-plus"></i> Employee - Vehicle Assignment <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()">
                <div class="modal-body">
                    
                    <div class="form-group">
                        <label for="vehicle">Vehicles</label>
                       <select wire:model.debounce.300ms="selectedVehicle" class="form-control" required>
                           <option value="" selected>Select Vehicle</option>
                           @php
                               $vehicle_assignments = App\Models\VehicleAssignment::where('status',1)->get();
                               foreach ($vehicle_assignments as $vehicle_assignment) {
                                   $vehicle_assignment_employee_ids[] = $vehicle_assignment->employee_id;
                                   $vehicle_assignment_vehicle_ids[] = $vehicle_assignment->vehicle_id;
                               }
                           @endphp
                       
                                @foreach ($vehicles as $vehicle)
                                    @if (isset($vehicle_assignment_vehicle_ids))
                                        @if (in_array($vehicle->id, $vehicle_assignment_vehicle_ids ))

                                        @else
                                        <option value="{{$vehicle->id}}">{{ucfirst($vehicle->vehicle_make ? $vehicle->vehicle_make->name : "undefined")}} {{ucfirst($vehicle->vehicle_model ? $vehicle->vehicle_model->name : "")}}({{ucfirst($vehicle->registration_number)}})</option>
                                        @endif
                                    @else
                                    <option value="{{$vehicle->id}}">{{ucfirst($vehicle->vehicle_make ? $vehicle->vehicle_make->name : "undefined")}} {{ucfirst($vehicle->vehicle_model ? $vehicle->vehicle_model->name : "")}}({{ucfirst($vehicle->registration_number)}})</option>
                                    @endif
                                @endforeach
                          
                          
                       </select>
                        @error('selectedVehicle') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="employee">Employees</label>
                       <select wire:model.debounce.300ms="employee_id" class="form-control" required>
                           <option value="" selected>Select Employee</option>
                         
                                @foreach ($employees as $employee)
                                    @if (isset($vehicle_assignment_employee_ids))
                                        @if (in_array($employee->id, $vehicle_assignment_employee_ids ))

                                        @else
                                        <option value="{{$employee->id}}">{{$employee->name }} {{$employee->surname}}</option>
                                        @endif
                                    @else
                                    <option value="{{$employee->id}}">{{$employee->name }} {{$employee->surname}}</option>
                                    @endif
                                @endforeach
                   
                       </select>
                        @error('employee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="start_odometer">Starting Odometer</label>
                     <input type="number" class="form-control" wire:model.debounce.300ms="starting_odometer" required>
                        @error('starting_odometer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="start_date">Starting Date</label>
                     <input type="date" class="form-control" wire:model.debounce.300ms="start_date" required>
                        @error('start_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="comments">Comments</label>
                    <textarea wire:model.debounce.300ms="comments" class="form-control" cols="30" rows="5"></textarea>
                        @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="vehicle_assignmentEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Employee - Vehicle Assignment <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="vehicle">Vehicle(s)</label>
                       <select wire:model.debounce.300ms="selectedVehicle" class="form-control" required>
                           <option value="" selected>Select Vehicle</option>
                           @foreach ($vehicles as $vehicle)
                           <option value="{{$vehicle->id}}">{{ucfirst($vehicle->vehicle_make ? $vehicle->vehicle_make->name : "undefined make")}} {{ucfirst($vehicle->vehicle_model ? $vehicle->vehicle_model->name : "& model")}}({{ucfirst($vehicle->registration_number)}})</option>
                           @endforeach
                       </select>
                        @error('selectedVehicle') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="employee">Employee(s)</label>
                       <select wire:model.debounce.300ms="employee_id" class="form-control" required>
                           <option value="" selected>Select Employee</option>
                           @foreach ($employees as $employee)
                           <option value="{{$employee->id}}">{{$employee->employee_number}} {{$employee->name}} {{$employee->surname}}</option>
                           @endforeach
                       </select>
                        @error('employee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="start_odometer">Starting Odometer</label>
                     <input type="number" class="form-control" wire:model.debounce.300ms="starting_odometer" required>
                        @error('starting_odometer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="start_date">Starting Date</label>
                     <input type="date" class="form-control" wire:model.debounce.300ms="start_date" required>
                        @error('start_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="comments">Comments</label>
                    <textarea wire:model.debounce.300ms="comments" class="form-control" cols="30" rows="5"></textarea>
                        @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="unAssignmentModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i>Employee - Vehicle Assignment<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="updateAssignment()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="ending_odometer">Ending Odometer</label>
                        <input type="text" wire:model.debounce.300ms="ending_odometer"  class="form-control" placeholder="Enter Ending Odometer" required>
                        @error('ending_odometer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <input type="date" wire:model.debounce.300ms="end_date" class="form-control" placeholder="Enter Ending Date" required>
                        @error('end_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

