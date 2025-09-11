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
                            <a href="" data-toggle="modal" data-target="#breakdownModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Incident</a>
                        </div>
                        
                    </div>
            <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                <div class="col-md-3" style="float: right; padding-right:0px">
                    <div class="form-group">
                        <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search incidents reports...">
                    </div>
            </div>
            <table  class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <caption>Incident Reports</caption>
            <thead >
                <th class="th-sm">Breakdown#
                </th>
                <th class="th-sm">Equipment
                </th>
                <th class="th-sm">Date
                </th>
                <th class="th-sm">Location
                </th>
                <th class="th-sm">Details
                </th>
                <th class="th-sm">Status
                </th>
                <th class="th-sm">Actions
                </th>
               
              </tr>
            </thead>

            <tbody>
                @forelse ($breakdowns as $breakdown)
            
              <tr>
                <td>{{$breakdown->breakdown_number}}</td>
                <td>{{$breakdown->transporter ? $breakdown->transporter->name : ""}}</td>
                <td>
                    @if ($breakdown->horse)
                        Horse: {{$breakdown->horse ? $breakdown->horse->registration_number : ""}} {{$breakdown->horse->horse_make ? $breakdown->horse->horse_make->name : ""}} {{$breakdown->horse->horse_model ? $breakdown->horse->horse_model->name : ""}}        
                    @endif
                </td>
                <td>{{$breakdown->date}}</td>
                <td>{{$breakdown->location}}</td>
                <td>{{$breakdown->description}}</td>      
                <td><span class="badge bg-{{$breakdown->status == 1 ? "warning" : "success"}}">{{$breakdown->status == 1 ? "Open" : "Closed"}}</span></td>      
               
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
         
            </tr>
            @empty
            <tr>
                <td colspan="8">
                    <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                        No Breakdown Reports Recorded ....
                    </div>
                   
                </td>
            </tr> 
            @endforelse
            </tbody>
          </table>
    
          <nav class="text-center" style="float: right">
            <ul class="pagination rounded-corners">
                @if (isset($breakdowns))
                    {{ $breakdowns->links() }} 
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

       <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="breakdownModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="breakdown">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Incident <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">

                    <label for="title">Incident For?<span class="required" style="color: red">*</span></label>
                    <div class="mb-10">
                        <input type="radio" wire:model.debounce.300ms="equipment" value="Horse"  class="line-style"  />
                        <label for="one" class="radio-label">Horse</label>
                        <input type="radio" wire:model.debounce.300ms="equipment" value="Trailer"  class="line-style"  />
                        <label for="one" class="radio-label">Trailer</label>
                        <input type="radio" wire:model.debounce.300ms="equipment" value="Vehicle"  class="line-style" />
                        <label for="one" class="radio-label">Vehicle</label>
                    </div> 
                   
                        <div class="form-group">
                            @if ($equipment && $equipment == "Horse")
                                <label for="title">Horses<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="selectedHorse" required>
                                    <option value="">Select Horse</option>
                                        @foreach ($horses as $horse)
                                            <option value="{{ $horse->id }}">{{$horse->registration_number}} {{$horse->fleet_number ? "(".$horse->fleet_number.")" : ""}}</option>
                                        @endforeach
                                </select>
                                @error('selectedHorse') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            @elseif($equipment && $equipment == "Trailers")
                                <label for="title">Trailers<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="selectedTrailer" required >
                                    <option value="">Select Trailer</option>
                                        @foreach ($trailers as $trailer)
                                            <option value="{{ $trailer->id }}">{{$trailer->registration_number}} {{$trailer->fleet_number ? "(".$trailer->fleet_number.")" : ""}}</option>
                                        @endforeach
                                </select>
                                @error('selectedTrailer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            @elseif($equipment && $equipment == "Vehicle")
                                <label for="title">Vehicles<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="selectedVehicle" required >
                                    <option value="">Select Vehicle</option>
                                        @foreach ($vehicles as $vehicle)
                                            <option value="{{ $vehicle->id }}">{{$vehicle->registration_number}} {{$vehicle->fleet_number ? "(".$vehicle->fleet_number.")" : ""}}</option>
                                        @endforeach
                                </select>
                                @error('selectedVehicle') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            
                            @endif
                            
                        </div>
                  

                    <div class="form-group">
                        <label for="title">Trips</label>
                            <select class="form-control" wire:model.debounce.300ms="trip_id" size="6">
                                <option value="">Select Trip</option>
                                    @foreach ($trips as $trip)
                                        @php
                                            $from = App\Models\Destination::find($trip->from);
                                            $to = App\Models\Destination::find($trip->to);
                                        @endphp
                                        @if (isset($from) && isset($to))
                                            <option value="{{ $trip->id }}">{{$trip->trip_number}}{{$trip->trip_ref ? "/".$trip->trip_ref : ""}} {{$trip->start_date}} {{$trip->customer ? $trip->customer->name : ""}} | From: {{$from->country ? $from->country->name : ""}} {{$from->city}} {{$trip->loading_point ? $trip->loading_point->name : ""}} To: {{$to->country ? $to->country->name : ""}} {{$to->city}} {{$trip->offloading_point ? $trip->offloading_point->name : ""}} </option>
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
        <div class="modal-dialog mw-100 w-50" role="breakdown">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Edit Incident <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">

                    <label for="title">Incident For?<span class="required" style="color: red">*</span></label>
                    <div class="mb-10">
                        <input type="radio" wire:model.debounce.300ms="equipment" value="Horse"  class="line-style"  />
                        <label for="one" class="radio-label">Horse</label>
                        <input type="radio" wire:model.debounce.300ms="equipment" value="Trailer"  class="line-style"  />
                        <label for="one" class="radio-label">Trailer</label>
                        <input type="radio" wire:model.debounce.300ms="equipment" value="Vehicle"  class="line-style" />
                        <label for="one" class="radio-label">Vehicle</label>
                    </div> 
                   
                        <div class="form-group">
                            @if ($equipment && $equipment == "Horse")
                                <label for="title">Horses<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="selectedHorse" required>
                                    <option value="">Select Horse</option>
                                        @foreach ($horses as $horse)
                                            <option value="{{ $horse->id }}">{{$horse->registration_number}} {{$horse->fleet_number ? "(".$horse->fleet_number.")" : ""}}</option>
                                        @endforeach
                                </select>
                                @error('selectedHorse') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            @elseif($equipment && $equipment == "Trailers")
                                <label for="title">Trailers<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="selectedTrailer" required >
                                    <option value="">Select Trailer</option>
                                        @foreach ($trailers as $trailer)
                                            <option value="{{ $trailer->id }}">{{$trailer->registration_number}} {{$trailer->fleet_number ? "(".$trailer->fleet_number.")" : ""}}</option>
                                        @endforeach
                                </select>
                                @error('selectedTrailer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            @elseif($equipment && $equipment == "Vehicle")
                                <label for="title">Vehicles<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="selectedVehicle" required >
                                    <option value="">Select Vehicle</option>
                                        @foreach ($vehicles as $vehicle)
                                            <option value="{{ $vehicle->id }}">{{$vehicle->registration_number}} {{$vehicle->fleet_number ? "(".$vehicle->fleet_number.")" : ""}}</option>
                                        @endforeach
                                </select>
                                @error('selectedVehicle') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            
                            @endif
                            
                        </div>
                  

                    <div class="form-group">
                        <label for="title">Trips</label>
                            <select class="form-control" wire:model.debounce.300ms="trip_id" size="6">
                                <option value="">Select Trip</option>
                                    @foreach ($trips as $trip)
                                        @php
                                            $from = App\Models\Destination::find($trip->from);
                                            $to = App\Models\Destination::find($trip->to);
                                        @endphp
                                        @if (isset($from) && isset($to))
                                            <option value="{{ $trip->id }}">{{$trip->trip_number}}{{$trip->trip_ref ? "/".$trip->trip_ref : ""}} {{$trip->start_date}} {{$trip->customer ? $trip->customer->name : ""}} | From: {{$from->country ? $from->country->name : ""}} {{$from->city}} {{$trip->loading_point ? $trip->loading_point->name : ""}} To: {{$to->country ? $to->country->name : ""}} {{$to->city}} {{$trip->offloading_point ? $trip->offloading_point->name : ""}} </option>
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

</div>
