<div>
    {{-- <blockquote class="blockquote-reverse mt-20"> --}}
        @if (Auth::user()->employee)
        @if ($trip->authorization == "approved")
        <a href="" data-toggle="modal" data-target="#breakdownModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Incident</a>
        @endif
        <br>
        <br>
        @endif
        {{-- <x-loading/> --}}
        <table id="breakdownsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <caption>Incidents Table</caption>
            <thead >
                <th class="th-sm">Incident#
                </th>
                <th class="th-sm">Transporter
                </th>
                <th class="th-sm">Horse
                </th>
                <th class="th-sm">Driver
                </th>
                <th class="th-sm">Trailers
                </th>
                <th class="th-sm">Date
                </th>
                <th class="th-sm">Location
                </th>
                <th class="th-sm">Details
                </th>
                <th class="th-sm">Status
                </th>
                @if (Auth::user()->category == "employee" || Auth::user()->category == "driver" || Auth::user()->category == "admin")
                <th class="th-sm">Actions
                </th>
                @endif
              </tr>
            </thead>

            <tbody>
                @forelse ($breakdowns as $breakdown)
            
              <tr>
                <td>{{$breakdown->breakdown_number}}</td>
                <td>{{$breakdown->transporter ? $breakdown->transporter->name : ""}}</td>
                <td>
                    @if ($breakdown->horse)
                        {{$breakdown->horse ? $breakdown->horse->registration_number : ""}} {{$breakdown->horse->horse_make ? $breakdown->horse->horse_make->name : ""}} {{$breakdown->horse->horse_model ? $breakdown->horse->horse_model->name : ""}}        
                    @endif
                </td>
                <td>{{$breakdown->driver->employee ? $breakdown->driver->employee->name : ""}} {{$breakdown->driver->employee ? $breakdown->driver->employee->surname : ""}}</td>
                <td>
                    @foreach ($breakdown->trailers as $trailer)
                            {{ $trailer->registration_number }} {{ $trailer->make }} {{ $trailer->model }} <br>  
                    @endforeach
                </td>
                <td>{{$breakdown->date}}</td>
                <td>{{$breakdown->location}}</td>
                <td>{{$breakdown->description}}</td>      
                <td><span class="badge bg-{{$breakdown->status == 1 ? "warning" : "success"}}">{{$breakdown->status == 1 ? "Open" : "Closed"}}</span></td>      
                @if (Auth::user()->category == "employee" || Auth::user()->category == "employee" || Auth::user()->category == "admin")
                <td class="w-10 line-height-35 table-dropdown">
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-bars"></i>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="#" wire:click="showAssignment({{$breakdown->id}})"><i class="fa fa-plus color-success"></i> Assign</a></li>
                            <li><a href="#" wire:click="edit({{$breakdown->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                            <li><a href="#" data-toggle="modal" data-target="#breakdownDeleteModal{{$breakdown->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                        </ul>
                    </div>
                    @include('breakdowns.delete')

            </td>
            @endif
            </tr>
            @empty
            <tr>
                <td colspan="10">
                    <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                        No Incidents Recorded ....
                    </div>
                   
                </td>
              </tr> 
            @endforelse
            </tbody>
          </table>
          <br>
          <br>
        <table id="assignmentsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <caption>Assignments Table</caption>
            <thead >
                <th class="th-sm">Incident#
                </th>
                <th class="th-sm">Transporter
                </th>
                <th class="th-sm">Horse
                </th>
                <th class="th-sm">Driver
                </th>
                <th class="th-sm">Trailer(s)
                </th>
                <th class="th-sm">Date
                </th>
                <th class="th-sm">Details
                </th>
                <th class="th-sm">Status
                </th>
                @if (Auth::user()->category == "employee" || Auth::user()->category == "driver" || Auth::user()->category == "admin")
                <th class="th-sm">Actions
                </th>
                @endif
              </tr>
            </thead>

            <tbody>
                @forelse ($breakdown_assignments as $breakdown_assignment)
              <tr>
                <td>{{$breakdown_assignment->breakdown ? $breakdown->breakdown_number : ""}}</td>
                <td>{{$breakdown_assignment->transporter ? $breakdown->transporter->name : ""}}</td>
                <td>
                    @if ($breakdown_assignment->horse )
                        {{$breakdown_assignment->horse ? $breakdown_assignment->horse->registration_number : ""}} {{$breakdown_assignment->horse->horse_make ? $breakdown_assignment->horse->horse_make->name : ""}} {{$breakdown_assignment->horse->horse_model ? $breakdown_assignment->horse->horse_model->name : ""}}
                    @endif
                </td>
                <td>
                    @if ($breakdown_assignment->driver)
                        {{$breakdown_assignment->driver->employee ? $breakdown_assignment->driver->employee->name : ""}} {{$breakdown_assignment->driver->employee ? $breakdown_assignment->driver->employee->surname : ""}}        
                    @endif
                </td>
                <td>
                    @foreach ($breakdown_assignment->trailers as $trailer)
                            {{ $trailer->registration_number }} {{ $trailer->make }} {{ $trailer->model }} <br>    
                    @endforeach
                </td>
                <td>{{$breakdown_assignment->date}}</td>
                <td>{{$breakdown_assignment->description}}</td>      
                <td><span class="badge bg-{{$breakdown_assignment->status == 1 ? "success" : "warning"}}">{{$breakdown_assignment->status == 1 ? "Active" : "Inactive"}}</span></td>      
                @if (Auth::user()->category == "employee" || Auth::user()->category == "employee" || Auth::user()->category == "admin")
                <td class="w-10 line-height-35 table-dropdown">
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-bars"></i>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a href="#" wire:click="editAssignment({{$breakdown_assignment->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                            <li><a href="#" data-toggle="modal" data-target="#breakdown_assignmentDeleteModal{{$breakdown_assignment->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                        </ul>
                    </div>
                    @include('breakdowns.assignments_delete')

            </td>
            @endif
            </tr>
            @empty
            <tr>
                <td colspan="9">
                    <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                        No Assignment Details Recorded ....
                    </div>
                   
                </td>
              </tr> 
            @endforelse
            </tbody>
          </table>
    {{-- </blockquote> --}}
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="breakdownModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="breakdown">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Incident <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="title">Trips<span class="required" style="color: red">*</span></label>
                            <select class="form-control" wire:model.debounce.300ms="trip_id" required disabled>
                                <option value="">Select Trip</option>
                                    @foreach ($trips as $trip)
                                        @php
                                            $from = App\Models\Destination::find($trip->from);
                                            $to = App\Models\Destination::find($trip->to);
                                        @endphp
                                        @if (isset($from) && isset($to))
                                            <option value="{{ $trip->id }}">{{$trip->trip_number}}{{$trip->trip_ref ? "/".$trip->trip_ref : ""}} | {{$trip->customer ? $trip->customer->name : ""}} | {{$from->country ? $from->country->name : ""}} {{$from->city}} {{$trip->loading_point ? $trip->loading_point->name : ""}}- {{$to->country ? $to->country->name : ""}} {{$to->city}} {{$trip->offloading_point ? $trip->offloading_point->name : ""}} </option>
                                        @endif
                                    @endforeach
                            </select>
                        @error('trip_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">Incident Date & Time<span class="required" style="color: red">*</span></label>
                                <input type="datetime-local" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter incident date" required>
                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">Location<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="location" placeholder="Enter incident location" required>
                                @error('location') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="">Incident Details<span class="required" style="color: red">*</span></label>
                            <textarea wire:model.debounce.300ms="description" cols="30" rows="5" class="form-control" placeholder="Write incident details... " required></textarea>
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="breakdownEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="breakdown">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Incident Details <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="title">Trips<span class="required" style="color: red">*</span></label>
                            <select class="form-control" wire:model.debounce.300ms="trip_id" required disabled>
                                <option value="">Select Trip</option>
                                    @foreach ($trips as $trip)
                                        @php
                                            $from = App\Models\Destination::find($trip->from);
                                            $to = App\Models\Destination::find($trip->to);
                                        @endphp
                                        @if (isset($from) && isset($to))
                                            <option value="{{ $trip->id }}">{{$trip->trip_number}}{{$trip->trip_ref ? "/".$trip->trip_ref : ""}} | {{$trip->customer ? $trip->customer->name : ""}} | {{$from->country ? $from->country->name : ""}} {{$from->city}} {{$trip->loading_point ? $trip->loading_point->name : ""}}- {{$to->country ? $to->country->name : ""}} {{$to->city}} {{$trip->offloading_point ? $trip->offloading_point->name : ""}} </option>
                                        @endif
                                    @endforeach
                            </select>
                        @error('trip_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">Date<span class="required" style="color: red">*</span></label>
                                <input type="datetime-local" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter incident date">
                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">Location<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="location" placeholder="Enter incident location">
                                @error('location') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="">Details<span class="required" style="color: red">*</span></label>
                            <textarea wire:model.debounce.300ms="description" cols="30" rows="5" class="form-control" placeholder="Write incident details... " required></textarea>
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="breakdown_assignmentModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="breakdown">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> New Assignment <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="storeAssignment()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="title">Transporters<span class="required" style="color: red">*</span></label>
                            <select class="form-control" wire:model.debounce.300ms="selectedTransporter" required>
                                <option value="">Select Transporter</option>
                                    @foreach ($transporters as $transporter)
                                        <option value="{{ $transporter->id }}">{{$transporter->name}}</option>
                                    @endforeach
                            </select>
                        @error('selectedTransporter') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">Horses</label>
                                <select class="form-control" wire:model.debounce.300ms="horse_id">
                                    <option value="">Select Horse</option>
                                    @if (!is_null($selectedTransporter))
                                        @foreach ($horses as $horse)
                                            @if ($trip->horse_id == $horse->id )
                                            @else 
                                            <option value="{{ $horse->id }}"> {{$horse->registration_number}} {{$horse->horse_make ? $horse->horse_make->name : ""}} {{$horse->horse_make ? $horse->horse_model->name : ""}}</option>
                                            @endif
                                           
                                        @endforeach
                                    @endif
                                </select>
                                @error('horse_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">Drivers</label>
                                <select class="form-control" wire:model.debounce.300ms="driver_id" >
                                    <option value="">Select Driver</option>
                                    @if (!is_null($selectedTransporter))
                                        @foreach ($drivers as $driver)
                                        @if ($trip->driver_id == $driver->id)
                                            
                                        @else   
                                        <option value="{{ $driver->id }}">{{$driver->employee ? $driver->employee->name : ""}} {{$driver->employee ? $driver->employee->surname : ""}}</option>
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
                                <label for="">Trailers</label>
                                <select class="form-control" wire:model.debounce.300ms="trailer_id"  multiple="multiple">
                                    <option value="">Select Trailers</option>
                                        @php
                                            $trip_trailers = $trip->trailers;
                                            foreach ($trip_trailers as $trip_trailer) {
                                                $trip_trailer_ids[] = $trip_trailer->id;
                                            }  
                                        @endphp
                                        @if (!is_null($selectedTransporter))
                                            @foreach ($trailers as $trailer)
                                                @if (in_array($trailer->id, $trip_trailer_ids))
                                                @else  
                                                <option value="{{ $trailer->id }}">{{$trailer->registration_number}} {{$trailer->make}} {{$trailer->model}}</option>
                                                @endif
                                            @endforeach
                                        @endif
                                </select>     
                                @error('trailer_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">Date<span class="required" style="color: red">*</span></label>
                                  <input type="datetime-local" class="form-control" wire:model.debounce.300ms="date" required>
                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="">Assignment Details</label>
                            <textarea wire:model.debounce.300ms="description" cols="30" rows="5" class="form-control" placeholder="Write assignment details... "></textarea>
                        @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>save</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="breakdown_assignmentEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="breakdown">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Assignment <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="updateAssignment()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="title">Transporters<span class="required" style="color: red">*</span></label>
                            <select class="form-control" wire:model.debounce.300ms="selectedTransporter" required>
                                <option value="">Select Transporter</option>
                                    @foreach ($transporters as $transporter)
                                        <option value="{{ $transporter->id }}">{{$transporter->name}}</option>
                                    @endforeach
                            </select>
                        @error('selectedTransporter') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">Horses</label>
                                <select class="form-control" wire:model.debounce.300ms="horse_id">
                                    <option value="">Select Horse</option>
                                    @if (!is_null($selectedTransporter))
                                    @if (isset($assignment_horses))
                                        @foreach ($assignment_horses as $assignment_horse)
                                            @if ($trip->horse_id == $assignment_horse->id )
                                            @else 
                                            <option value="{{ $assignment_horse->id }}"> {{$assignment_horse->registration_number}} {{$assignment_horse->horse_make ? $assignment_horse->horse_make->name : ""}} {{$assignment_horse->horse_model ? $assignment_horse->horse_model->name : ""}}</option>
                                            @endif
                                           
                                        @endforeach
                                    @endif
                                    @endif
                                </select>
                                @error('horse_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">Drivers</label>
                                <select class="form-control" wire:model.debounce.300ms="driver_id">
                                    <option value="">Select Driver</option>
                                    @if (!is_null($selectedTransporter))
                                        @foreach ($drivers as $driver)
                                        @if ($trip->driver_id == $driver->id)
                                            
                                        @else   
                                        <option value="{{ $driver->id }}">{{$driver->employee ? $driver->employee->name : ""}} {{$driver->employee ? $driver->employee->surname : ""}}</option>
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
                                <label for="">Trailers</label>
                                <select class="form-control" wire:model.debounce.300ms="trailer_id"   multiple="multiple">
                                    <option value="">Select Trailers</option>
                                        @php
                                            $trip_trailers = $trip->trailers;
                                            foreach ($trip_trailers as $trip_trailer) {
                                                $trip_trailer_ids[] = $trip_trailer->id;
                                            }   
                                        @endphp
                                        @if (!is_null($selectedTransporter))
                                            @foreach ($trailers as $trailer)
                                                @if (in_array($trailer->id, $trip_trailer_ids))
                                                @else  
                                                <option value="{{ $trailer->id }}" >{{$trailer->registration_number}} {{$trailer->make}} {{$trailer->model}}</option>
                                                @endif
                                            @endforeach
                                        @endif
                                </select>     
                                @error('trailer_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">Date<span class="required" style="color: red">*</span></label>
                                  <input type="datetime-local" class="form-control" wire:model.debounce.300ms="date" required>
                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                       
                    </div>
                    <div class="form-group">
                        <label for="">Assignment Details</label>
                            <textarea wire:model.debounce.300ms="description" cols="30" rows="5" class="form-control" placeholder="Write breakdown details... "></textarea>
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
@section('extra-js')
<script>
    $(document).ready( function () {
        $('#breakdownsTable').DataTable();
    } );
    </script>
@endsection
