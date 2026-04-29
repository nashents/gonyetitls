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
                                <a href="" data-toggle="modal" data-target="#saveModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>
                                    @if (isset($type) && $type == "inspection")
                                        Inspection Schedule
                                    @else
                                        Maintenance Schedule
                                    @endif 
                                </a>
                            </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-5" style="float: right; padding-right:2px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search schedules...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Schedule#
                                    </th>
                                    <th class="th-sm">Type
                                    </th>
                                    <th class="th-sm">Equipment
                                    </th>
                                    <th class="th-sm">ScheduleFor
                                    </th>
                                    <th class="th-sm">Trigger
                                    </th>
                                    <th class="th-sm">Intervals
                                    </th>
                                    <th class="th-sm">Notes
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($inspection_schedules))
                                <tbody>
                                    @forelse ($inspection_schedules as $inspection_schedule)
                                  <tr>
                                    <td>
                                        {{$inspection_schedule->inspection_schedule_number}}
                                        <small class="text-muted">
                                            <strong>CreatedBy:</strong>{{$inspection_schedule->created_by?->name}} {{$inspection_schedule->created_by?->surname}}
                                            <strong>CreatedOn:</strong>{{$inspection_schedule->created_at}}
                                        </small>
                                    </td>
                                    <td>
                                        {{$inspection_schedule->inspection_schedule_number}}
                                    </td>
                                    <td>{{$inspection_schedule->description}}</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#"  wire:click="edit({{$inspection_schedule->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#inspection_scheduleDeleteModal{{ $inspection_schedule->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('inspection_schedules.delete')
                                </td>
                                  </tr>
                                   @empty
                                      <tr>
                                        <td colspan="11">
                                            <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                No  @if (isset($type) && $type == "inspection")
                                                    Inspection Schedules
                                                @else
                                                    Maintenance Schedules
                                                @endif  Found ....
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
                                        @if (isset($inspection_schedules))
                                            @if ($inspection_schedules->count()>0)
                                                {{ $inspection_schedules->links() }} 
                                            @endif
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="saveModal" tainspection_scheduledex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-70" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add 
                                                @if (isset($type) && $type == "inspection")
                                                    Inspection Schedule
                                                @else
                                                    Maintenance Schedule
                                                @endif <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                        <div class="mb-10">
                            <input type="radio" wire:model.debounce.300ms="equipment" value="Asset"  class="line-style"  required/>
                            <label for="one" class="radio-label">Asset</label>
                            <input type="radio" wire:model.debounce.300ms="equipment" value="Horse"  class="line-style"  required/>
                            <label for="one" class="radio-label">Horse</label>
                            <input type="radio" wire:model.debounce.300ms="equipment" value="Trailer"  class="line-style"  required/>
                            <label for="one" class="radio-label">Trailer</label>
                            <input type="radio" wire:model.debounce.300ms="equipment" value="Vehicle"  class="line-style" required/>
                            <label for="one" class="radio-label">Vehicle</label>
                        </div>     
                        <div class="row">
                            <div class="col-md-6">
                                @if ($equipment == "Horse")
                                <div class="form-group">
                                    <label for="exampleInputEmail13">Horses<span class="required" style="color: red">*</span></label>
                                    <input type="text" wire:model.debounce.300ms="searchHorse" placeholder="Search horse..." class="form-control" >
                                    <select wire:model.debounce.300ms="selectedHorse" class="form-control" required size="4">
                                        <option value="">Select Horse </option>
                                        @foreach ($horses as $horse)
                                            <option value="{{$horse->id}}">{{$horse->registration_number}} {{$horse->horse_make ? $horse->horse_make->name : ""}} {{$horse->horse_model ? $horse->horse_model->name : ""}} {{$horse->fleet_number ? "(".$horse->fleet_number.")" : ""}}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedHorse') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                                @elseif ($equipment == "Asset")
                                <div class="form-group">
                                    <label for="exampleInputEmail13">Assets<span class="required" style="color: red">*</span></label>
                                    <input type="text" wire:model.debounce.300ms="searchAsset" placeholder="Search vehicle..." class="form-control" >
                                    <select wire:model.debounce.300ms="selectedAsset" class="form-control" required size="4">
                                        <option value="">Select Asset</option>
                                        @foreach ($assets as $asset)
                                            <option value="{{$asset->id}}">{{$asset->brand ? $asset->brand->name : ""}} {{$asset->product ? $asset->product->name : ""}} {{$asset->serial_number ? "SN:".$asset->serial_number : ""}}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedAsset') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                                @elseif ($equipment == "Vehicle")
                                <div class="form-group">
                                    <label for="exampleInputEmail13">Vehicles<span class="required" style="color: red">*</span></label>
                                    <input type="text" wire:model.debounce.300ms="searchVehicle" placeholder="Search vehicle..." class="form-control" >
                                    <select wire:model.debounce.300ms="selectedVehicle" class="form-control" required size="4">
                                        <option value="">Select Vehicle</option>
                                        @foreach ($vehicles as $vehicle)
                                            <option value="{{$vehicle->id}}">{{$vehicle->registration_number}} {{$vehicle->vehicle_make ? $vehicle->vehicle_make->name : ""}} {{$vehicle->vehicle_model ? $vehicle->vehicle_model->name : ""}} {{$vehicle->fleet_number ? "(".$vehicle->fleet_number.")" : ""}}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedVehicle') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                                @elseif ($equipment == "Trailer")
                                <div class="form-group">
                                    <label for="exampleInputEmail13">Trailers<span class="required" style="color: red">*</span></label>
                                    <input type="text" wire:model.debounce.300ms="searchTrailer" placeholder="Search trailer..." class="form-control" >
                                    <select wire:model.debounce.300ms="selectedTrailer" class="form-control" required size="4">
                                        <option value="">Select Trailer</option>
                                        @foreach ($trailers as $trailer)
                                            <option value="{{$trailer->id}}">{{$trailer->registration_number}} {{$trailer->make}} {{$trailer->model}} {{$trailer->fleet_number ? "(".$trailer->fleet_number.")" : ""}}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedTrailer') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                                @endif
                            </div>
                           
                            <div class="col-md-6">
                                @if (isset($type) && $type == "inspection")
                                    <div class="form-group">
                                        <label for="exampleInputEmail13">Categories<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="problem_category_id" class="form-control" required>
                                            <option value="">Select Inspection</option>
                                            @foreach ($problem_categories as $problem_category)
                                            <option value="{{$problem_category->id}}">{{$problem_category->name}}</option>
                                            @endforeach
                                        </select>
                                        @error('problem_category_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                        <small><a href="{{ route('problem_categories.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Category</a></small> <a href="#" wire:click.prevent="refresh('problem_categories')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                    </div>
                                @else
                                    <div class="form-group">
                                        <label for="exampleInputEmail13">Job Types<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="service_type_id" class="form-control" required>
                                            <option value="">Select Job Type</option>
                                            @foreach ($service_types as $service_type)
                                            <option value="{{$service_type->id}}">{{$service_type->name}}</option>
                                            @endforeach
                                        </select>
                                        @error('service_type_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                        <small><a href="{{ route('service_types.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Job Type</a></small> <a href="#" wire:click.prevent="refresh('service_types')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                    </div>
                                @endif
                               
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail13">Trigger<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="trigger_type" class="form-control" required>
                                        <option value="">Select Trigger</option>
                                        <option value="Mileage">Mileage</option>
                                        <option value="Date">Date</option>
                                        <option value="Both">Both</option>
                                    </select>
                                    @error('service_type_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="number">Interval Days</label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="interval_days"  placeholder=" e.g. 90">
                                    @error('interval_days') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="number">Interval Km</label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="interval_km" {{$equipment == "Asset" ? "disabled" : ""}} placeholder="e.g. 10000">
                                    @error('interval_km') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                             <div class="col-md-3">
                                <div class="form-group">
                                    <label for="number">Last Inspection Date</label>
                                    <input type="date" step="any" class="form-control" wire:model.debounce.300ms="last_inspection_date"  placeholder="Last Inspection Date">
                                    @error('last_inspection_date') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="number">Last Inspection Km</label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="last_inspection_km" {{$equipment == "Asset" ? "disabled" : ""}} placeholder="Last Inspection Km">
                                    @error('last_inspection_km') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                             <div class="col-md-3">
                                <div class="form-group">
                                    <label for="number">Next Due Date</label>
                                    <input type="date" step="any" class="form-control" wire:model.debounce.300ms="next_due_date"  placeholder="Next Due Date">
                                    @error('next_due_date') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="number">Next Due Km</label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="next_due_km" {{$equipment == "Asset" ? "disabled" : ""}} placeholder="Next Due Km">
                                    @error('next_due_km') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                             <div class="col-md-4">
                                <div class="form-group">
                                    <label for="number">Status<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="status" required>
                                        <option value="Active">Active</option>
                                        <option value="InActive">InActive</option>
                                    </select>
                                    @error('status') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="number">Notes</label>
                                    <textarea class="form-control" wire:model.debounce.300ms="notes" cols="30" rows="2"></textarea>
                                    @error('notes') <span class="text-danger error">{{ $message }}</span>@enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="inspection_scheduleEditModal" tainspection_scheduledex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-70" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Fleet Schedule <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <input type="hidden" wire:model="inspection_schedule_id">
                <div class="modal-body">
                             <div class="row">
                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="name">Name</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter inspection_schedule Name">
                                @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="inspection_schedule_number">inspection_schedule Number<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="inspection_schedule_number" placeholder="Enter inspection_schedule Number" required>
                                @error('inspection_schedule_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>               
                    <div class="form-group">
                        <label for="name">Description</label>
                        <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="4"></textarea>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                <center> <strong>Are you sure you want to delete this Schedule?</strong> </center>
                </div>
                <form wire:submit.prevent="destroy()" >
                <div class="modal-footer no-border">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-black btn-wide btn-rounded" ><i class="fa fa-trash"></i>Delete</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>


</div>

