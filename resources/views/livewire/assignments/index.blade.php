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
                                <a href="" data-toggle="modal" data-target="#assignmentModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Driver - Horse Assignment</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Transporter
                                    </th>
                                    <th class="th-sm">Horse
                                    </th>
                                    <th class="th-sm">Driver
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
                                @if (isset($assignments))
                                <tbody>
                                    @foreach ($assignments as $assignment)
                                  <tr>
                                    <td>{{$assignment->transporter ? $assignment->transporter->name : ""}}</td>
                                    <td>
                                        @if ($assignment->horse)
                                           {{$assignment->horse->horse_make ? $assignment->horse->horse_make->name : ""}} {{$assignment->horse->horse_model ? $assignment->horse->horse_model->name : ""}} ({{$assignment->horse->registration_number}})
                                        @endif
                                    </td>
                                    <td>{{ucfirst($assignment->driver->employee->name)}} {{ucfirst($assignment->driver->employee->surname)}}</td>
                                    <td>{{$assignment->starting_odometer ? $assignment->starting_odometer."Kms" : ""}}</td>

                                    <td>{{$assignment->status == 0 ? $assignment->ending_odometer."Kms" : ""}}</td>

                                    <td>{{$assignment->start_date}}</td>

                                    <td>{{$assignment->status == 0 ? $assignment->end_date : ""}}</td>

                                    <td>{{$assignment->comments}}</td>
                                    <td><span class="label label-{{$assignment->status == 1 ? "success" : "danger"}} label-rounded">{{$assignment->status == 1 ? "Assigned" : "Unassigned"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#"  wire:click="edit({{$assignment->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                @if ($assignment->status == 1)
                                                <li><a href="#"  wire:click="unAssignment({{$assignment->id}})" ><i class="fa fa-undo color-success"></i> Unassign</a></li>
                                                @endif
                                                <li><a href="#" data-toggle="modal" data-target="#assignmentDeleteModal{{ $assignment->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('assignments.delete')
                                </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                                @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                 @endif
                              </table>
                              <nav class="text-center" style="float: right">
                                <ul class="pagination rounded-corners">
                                    @if (isset($assignments))
                                        {{ $assignments->links() }} 
                                    @endif 
                                </ul>
                            </nav>    

                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="assignmentModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Driver - Horse Assignment <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="driver">Transporters<span class="required" style="color: red">*</span></label>
                       <select wire:model.debounce.300ms="selectedTransporter" class="form-control" required>
                           <option value="" selected>Select Transporter</option>
                            @foreach ($transporters as $transporter)
                                <option value="{{ $transporter->id }}">{{ $transporter->name }}</option>
                            @endforeach
                       </select>
                        @error('selectedTransporter') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="horse">Horses<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="selectedHorse" class="form-control" required>
                                   <option value="" selected>Select Horse</option>
                                   @php
                                       $assignments = App\Models\Assignment::where('status',1)->get();
                                       foreach ($assignments as $assignment) {
                                           $assignment_driver_ids[] = $assignment->driver_id;
                                           $assignment_horse_ids[] = $assignment->horse_id;
                                       }
                                   @endphp
                                   @if (!is_null($selectedTransporter))
                                        @foreach ($horses as $horse)
                                            @if (isset($assignment_horse_ids))
                                                @if (in_array($horse->id, $assignment_horse_ids ))
        
                                                @else
                                                <option value="{{$horse->id}}">{{ucfirst($horse->horse_make ? $horse->horse_make->name : "")}} {{ucfirst($horse->horse_model ? $horse->horse_model->name : "")}} {{ucfirst($horse->registration_number)}} </option>
                                                @endif
                                            @else
                                            <option value="{{$horse->id}}">{{ucfirst($horse->horse_make ? $horse->horse_make->name : "")}} {{ucfirst($horse->horse_model ? $horse->horse_model->name : "")}} {{ucfirst($horse->registration_number)}} </option>
                                            @endif
                                        @endforeach
                                   @endif
                                  
                               </select>
                                @error('selectedHorse') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="driver">Drivers<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="driver_id" class="form-control" required>
                                   <option value="" selected>Select Driver</option>
                                   @if (!is_null($selectedTransporter)) 
                                        @foreach ($drivers as $driver)
                                            @if (isset($assignment_driver_ids))
                                                @if (in_array($driver->id, $assignment_driver_ids ))
        
                                                @else
                                                <option value="{{$driver->id}}">{{$driver->employee ? $driver->employee->name : ""}} {{$driver->employee ? $driver->employee->surname : ""}}</option>
                                                @endif
                                            @else
                                            <option value="{{$driver->id}}">{{$driver->employee ? $driver->employee->name : ""}} {{$driver->employee ? $driver->employee->surname : ""}}</option>
                                            @endif
                                        @endforeach
                                   @endif
                               </select>
                                @error('driver_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_odometer">Starting Odometer<span class="required" style="color: red">*</span></label>
                                <input type="number" class="form-control" wire:model.debounce.300ms="starting_odometer" required>
                                @error('starting_odometer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_date">Starting Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="start_date" required>
                                @error('start_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="assignmentEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Driver - Horse Assignment <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="driver">Transporters<span class="required" style="color: red">*</span></label>
                       <select wire:model.debounce.300ms="selectedTransporter" class="form-control" required>
                           <option value="" selected>Select Transporter</option>
                            @foreach ($transporters as $transporter)
                                <option value="{{ $transporter->id }}">{{ $transporter->name }}</option>
                            @endforeach
                       </select>
                        @error('selectedTransporter') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="horse">Horse(s)<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="selectedHorse" class="form-control" required>
                                   <option value="" selected>Select Horse</option>
                                   @foreach ($horses as $horse)
                                   <option value="{{$horse->id}}">{{ucfirst($horse->horse_make ? $horse->horse_make->name : "")}} {{ucfirst($horse->horse_model ? $horse->horse_model->name : "")}} {{ucfirst($horse->registration_number)}} </option>
                                   @endforeach
                               </select>
                                @error('selectedHorse') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="driver">Driver(s)<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="driver_id" class="form-control" required>
                                   <option value="" selected>Select Driver</option>
                                   @foreach ($drivers as $driver)
                                   <option value="{{$driver->id}}">{{$driver->employee ? $driver->employee->name : ""}} {{$driver->employee ? $driver->employee->surname : ""}}</option>
                                   @endforeach
                               </select>
                                @error('driver_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_odometer">Starting Odometer<span class="required" style="color: red">*</span></label>
                                <input type="number" class="form-control" wire:model.debounce.300ms="starting_odometer" required>
                                @error('starting_odometer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_date">Starting Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="start_date" required>
                                @error('start_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
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
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i>Driver - Horse Unassignment<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="updateAssignment()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ending_odometer">Ending Odometer<span class="required" style="color: red">*</span></label>
                                <input type="text" min="{{ $starting_odometer }}" wire:model.debounce.300ms="ending_odometer"  class="form-control" placeholder="Enter Ending Odometer" required>
                                @error('ending_odometer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end_date">End Date<span class="required" style="color: red">*</span></label>
                                <input type="date" wire:model.debounce.300ms="end_date" class="form-control" placeholder="Enter Ending Date" required>
                                @error('end_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

