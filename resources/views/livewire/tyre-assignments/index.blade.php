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
                                <a href="" data-toggle="modal" data-target="#tyre_assignmentModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Assignment</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search assignments...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Tyre#
                                    </th>
                                    <th class="th-sm">Tyre
                                    </th>
                                    <th class="th-sm">Specifications
                                    </th>
                                    <th class="th-sm">Assigned On
                                    </th>
                                    <th class="th-sm">Fitting Mileage
                                    </th>
                                    <th class="th-sm">Current Mileage
                                    </th>
                                    <th class="th-sm">Tyre Life(Kms)
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($tyre_assignments))
                                <tbody>
                                    @forelse ($tyre_assignments as $tyre_assignment)
                                  <tr>
                                    <td>{{$tyre_assignment->tyre ? $tyre_assignment->tyre->tyre_number : ""}}</td>
                                    <td>
                                        @if ($tyre_assignment->tyre)
                                            @if ($tyre_assignment->tyre->product)
                                            {{$tyre_assignment->tyre->product->name}} {{$tyre_assignment->tyre->product->brand ? $tyre_assignment->tyre->product->brand->name : ""}} <br>
                                            SN#: {{$tyre_assignment->tyre ? $tyre_assignment->tyre->serial_number : ""}}
                                            @endif
                                       
                                        @endif
                                    </td>
                                    <td>{{$tyre_assignment->tyre ? $tyre_assignment->tyre->width : ""}}/ {{$tyre_assignment->tyre ? $tyre_assignment->tyre->aspect_ratio : ""}} R {{$tyre_assignment->tyre ? $tyre_assignment->tyre->diameter : ""}}</td>
                                    <td>
                                        @if ($tyre_assignment->horse)
                                        Horse | {{$tyre_assignment->horse->horse_make ? $tyre_assignment->horse->horse_make->name : ""}} {{$tyre_assignment->horse->horse_model ? $tyre_assignment->horse->horse_model->name : ""}} [ {{$tyre_assignment->horse ? $tyre_assignment->horse->registration_number : ""}} ]
                                        @elseif ($tyre_assignment->trailer)
                                        Trailer | {{$tyre_assignment->trailer ? $tyre_assignment->trailer->make : ""}} {{$tyre_assignment->trailer ? $tyre_assignment->trailer->model : ""}} [{{$tyre_assignment->trailer ? $tyre_assignment->trailer->registration_number : ""}}]
                                        @elseif ($tyre_assignment->vehicle)
                                        Vehicle | {{$tyre_assignment->vehicle->vehicle_make ? $tyre_assignment->vehicle->vehicle_make->name : ""}} {{$tyre_assignment->vehicle->vehicle_model ? $tyre_assignment->vehicle->vehicle_model->name : ""}} [{{$tyre_assignment->vehicle ? $tyre_assignment->vehicle->registration_number : ""}}]
                                        @endif
                                    </td>
                                    <td>{{$tyre_assignment->starting_odometer ? $tyre_assignment->starting_odometer."Kms" : ""}}</td>
                                    <td>
                                        @if ($tyre_assignment->horse)
                                            {{$tyre_assignment->horse->mileage ? $tyre_assignment->horse->mileage."Kms" : ""}}
                                            @elseif ($tyre_assignment->vehicle)
                                            {{$tyre_assignment->vehicle->mileage ? $tyre_assignment->vehicle->mileage."Kms" : ""}}
                                            @elseif ($tyre_assignment->trailer)
                                            {{$tyre_assignment->trailer->mileage ? $tyre_assignment->trailer->mileage."Kms" : ""}}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($tyre_assignment->tyre)
                                            {{$tyre_assignment->tyre->mileage ? $tyre_assignment->tyre->mileage."Kms" : ""}}
                                        @endif
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('tyre_assignments.show',$tyre_assignment->id)}}" ><i class="fa fa-eye color-default"></i> View</a></li>
                                                <li><a href="#"  wire:click="edit({{$tyre_assignment->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#tyre_assignmentDeleteModal{{ $tyre_assignment->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('tyre_assignments.delete')
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="10">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Tyre Assignments Found ....
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
                                    @if (isset($tyre_assignments))
                                        {{ $tyre_assignments->links() }} 
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="tyre_assignmentModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Tyre - Assignment <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <label for="name">Tyre Assignment for?<span class="required" style="color: red">*</span></label>
                    <div class="mb-10">
                        <input type="radio" wire:model.debounce.300ms="type" value="Horse"  class="line-style"  />
                        <label for="one" class="radio-label">Horse</label>
                        <input type="radio" wire:model.debounce.300ms="type" value="Trailer"  class="line-style"  />
                        <label for="one" class="radio-label">Trailer</label>
                        <input type="radio" wire:model.debounce.300ms="type" value="Vehicle"  class="line-style" />
                        <label for="one" class="radio-label">Vehicle</label>
                    </div>      
                  
                    <div class="form-group">
                        <label for="name">Tyres<span class="required" style="color: red">*</span></label>
                         <input type="text" wire:model.debounce.300ms="searchTyres" placeholder="Search tyres..." class="form-control" >
                        <select class="form-control" wire:model.debounce.300ms="tyre_id" required>
                            <option value="">Select Tyre</option>
                            @foreach ($tyres as $tyre)
                                <option value="{{$tyre->id}}">{{$tyre->tyre_number}} {{$tyre->product->brand ? $tyre->product->brand->name : ""}} {{$tyre->product ? $tyre->product->name : ""}} SN#: {{$tyre->serial_number}} |  {{$tyre->width}} / {{$tyre->aspect_ratio}} R {{$tyre->diameter}} </option>
                            @endforeach
                        </select>
                        @error('tyre_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                  
                    <div class="row">
                        <div class="col-md-6">
                            @if ($type == "Horse")
                                <div class="form-group">
                                    <label for="name">Horses<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="horse_id" required>
                                        <option value="">Select Horse</option>
                                        @foreach ($horses as $horse)
                                            <option value="{{$horse->id}}">{{$horse->registration_number}} {{$horse->horse_make ? $horse->horse_make->name : ""}} {{$horse->horse_model ? $horse->horse_model->name : ""}}</option>
                                        @endforeach
                                    </select>
                                    @error('horse_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            @elseif ($type == "Trailer")
                                <div class="form-group">
                                    <label for="name">Trailers<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="trailer_id" required>
                                        <option value="">Select Trailer</option>
                                        @foreach ($trailers as $trailer)
                                            <option value="{{$trailer->id}}">{{$trailer->registration_number}} {{$trailer->make}} {{$trailer->model}} </option>
                                        @endforeach
                                    </select>
                                    @error('trailer_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            @elseif ($type == "Vehicle")
                                <div class="form-group">
                                    <label for="name">Vehicles<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="vehicle_id" required>
                                        <option value="">Select vehicle</option>
                                        @foreach ($vehicles as $vehicle)
                                            <option value="{{$vehicle->id}}">{{$vehicle->registration_number}} {{$vehicle->vehicle_make ? $vehicle->vehicle_make->name : ""}} {{$vehicle->vehicle_model ? $vehicle->vehicle_model->name : ""}}</option>
                                        @endforeach
                                    </select>
                                    @error('vehicle_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Fitting Mileage<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="starting_odometer" placeholder="Enter Starting Mileage" required>
                                @error('starting_odometer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Wheel Axles<span class="required" style="color: red">*</span></label>
                               <select class="form-control" wire:model.debounce.300ms="axle" required>
                                    <option value="">Select Wheel Axle</option>
                                    <option value="Steering Axle">Steering Axle</option>
                                    <option value="Drive Axle">Drive Axle</option>
                                    <option value="Front Axle">Front Axle</option>
                                    <option value="Middle Axle">Middle Axle</option>
                                    <option value="Rear Axle">Rear Axle</option>
                                    <option value="Diff Axle">Diff Axle</option>
                                    <option value="Spare Wheel">Spare Wheel</option>

                               </select>
                                @error('axle') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Wheel Positions<span class="required" style="color: red">*</span></label>
                              <select class="form-control" wire:model.debounce.300ms="position" required>
                                <option value="">Select Wheel Position</option>
                                <option value="Front Left">Front Left</option>
                                <option value="Front Right">Front Right</option>
                                <option value="Middle Left Inside">Middle Left Inside</option>
                                <option value="Middle Left Outside">Middle Left Outside</option>
                                <option value="Middle Right Inside">Middle Right Inside</option>
                                <option value="Middle Right Outside">Middle Right Outside</option>
                                <option value="Rear Left Inside">Rear Left Inside</option>
                                <option value="Rear Left Outside">Rear Left Outside</option>
                                <option value="Rear Right Inside">Rear Right Inside</option>
                                <option value="Rear Right Outside">Rear Right Outside</option>
                                <option value="Spare Wheel">Spare Wheel</option>
                              </select>
                                @error('position') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="name">Comments</label>
                       <textarea wire:model.debounce.300ms="description" class="form-control" cols="30" rows="5" placeholder="Enter Details"></textarea>
                        @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="tyre_assignmentEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Tyre - Assignment <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <h5 class="underline mt-30">Tyre Assignment For ?</h5>
                    <div class="mb-10">
                        <input type="radio" wire:model.debounce.300ms="type" value="Horse"  class="line-style"  />
                        <label for="one" class="radio-label">Horse</label>
                        <input type="radio" wire:model.debounce.300ms="type" value="Trailer"  class="line-style"  />
                        <label for="one" class="radio-label">Trailer</label>
                        <input type="radio" wire:model.debounce.300ms="type" value="Vehicle"  class="line-style" />
                        <label for="one" class="radio-label">Vehicle</label>
                    </div>      
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Tyres<span class="required" style="color: red">*</span></label>
                                 <input type="text" wire:model.debounce.300ms="searchTyres" placeholder="Search tyres..." class="form-control" >
                                <select class="form-control" wire:model.debounce.300ms="tyre_id" required>
                                    <option value="">Select Tyre</option>
                                    @foreach ($tyres as $tyre)
                                        <option value="{{$tyre->id}}">{{$tyre->tyre_number}} {{$tyre->product->brand ? $tyre->product->brand->name : ""}} {{$tyre->product ? $tyre->product->name : ""}} SN#: {{$tyre->serial_number}} |  {{$tyre->width}} / {{$tyre->aspect_ratio}} R {{$tyre->diameter}} </option>
                                    @endforeach
                                </select>
                                @error('tyre_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            @if ($type == "Horse")
                                <div class="form-group">
                                    <label for="name">Horses<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="horse_id" required>
                                        <option value="">Select Horse</option>
                                        @foreach ($horses as $horse)
                                            <option value="{{$horse->id}}">{{$horse->registration_number}} {{$horse->horse_make ? $horse->horse_make->name : ""}} {{$horse->horse_model ? $horse->horse_model->name : ""}} {{$horse->fleet_number ? "(".$horse->fleet_number.")" : ""}}</option>
                                        @endforeach

                                    </select>
                                    @error('horse_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            @elseif ($type == "Vehicle")
                                <div class="form-group">
                                    <label for="name">Vehicles<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="vehicle_id" required>
                                        <option value="">Select vehicle</option>
                                        @foreach ($vehicles as $vehicle)
                                            <option value="{{$vehicle->id}}">{{$vehicle->registration_number}} {{$vehicle->vehicle_make ? $vehicle->vehicle_make->name : ""}} {{$vehicle->vehicle_model ? $vehicle->vehicle_model->name : ""}} {{$vehicle->fleet_number ? "(".$vehicle->fleet_number.")" : ""}}</option>
                                        @endforeach
                                    </select>
                                    @error('vehicle_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            @elseif ($type == "Trailer")
                                <div class="form-group">
                                    <label for="name">Trailers<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="trailer_id" required>
                                        <option value="">Select Trailer</option>
                                        @foreach ($trailers as $trailer)
                                            <option value="{{$trailer->id}}">{{$trailer->registration_number}} {{$trailer->make}} {{$trailer->model}} {{$trailer->fleet_number ? "(".$trailer->fleet_number.")" : ""}}</option>
                                        @endforeach
                                    </select>
                                    @error('trailer_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Fitting Mileage</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="starting_odometer" placeholder="Enter Starting Mileage">
                                @error('starting_odometer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Ending Mileage</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="ending_odometer" placeholder="Enter Ending Mileage">
                                @error('ending_odometer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Wheel Axles<span class="required" style="color: red">*</span></label>
                               <select class="form-control" wire:model.debounce.300ms="axle" required>
                                    <option value="">Select Wheel Axle</option>
                                    <option value="Steering Axle">Steering Axle</option>
                                    <option value="Drive Axle">Drive Axle</option>
                                    <option value="Front Axle">Front Axle</option>
                                    <option value="Middle Axle">Middle Axle</option>
                                    <option value="Rear Axle">Rear Axle</option>
                                    <option value="Diff Axle">Diff Axle</option>
                                    <option value="Spare Wheel">Spare Wheel</option>

                               </select>
                                @error('axle') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Wheel Positions<span class="required" style="color: red">*</span></label>
                              <select class="form-control" wire:model.debounce.300ms="position" required>
                                <option value="">Select Wheel Position</option>
                                <option value="Front Left">Front Left</option>
                                <option value="Front Right">Front Right</option>
                                <option value="Middle Left Inside">Middle Left Inside</option>
                                <option value="Middle Left Outside">Middle Left Outside</option>
                                <option value="Middle Right Inside">Middle Right Inside</option>
                                <option value="Middle Right Outside">Middle Right Outside</option>
                                <option value="Rear Left Inside">Rear Left Inside</option>
                                <option value="Rear Left Outside">Rear Left Outside</option>
                                <option value="Rear Right Inside">Rear Right Inside</option>
                                <option value="Rear Right Outside">Rear Right Outside</option>
                                <option value="Spare Wheel">Spare Wheel</option>
                              </select>
                                @error('position') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="name">Comments</label>
                       <textarea wire:model.debounce.300ms="description" class="form-control" cols="30" rows="5" placeholder="Enter Details"></textarea>
                        @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

