<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                       
                            <div class="panel-title">
                                <a href="" data-toggle="modal" data-target="#rehandlingModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Rehandling</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-5" style="float: right; padding-right:2px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search rehandling...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Rehandling#
                                    </th>
                                    <th class="th-sm">CreatedBy
                                    </th>
                                    <th class="th-sm">Shift
                                    </th>
                                    <th class="th-sm">Start Time
                                    </th>
                                    <th class="th-sm">Open Hours
                                    </th>
                                    <th class="th-sm">Open Mileage
                                    </th>
                                    <th class="th-sm">Work Description & Location
                                    </th>
                                    <th class="th-sm">Stop Time
                                    </th>
                                    <th class="th-sm">Duration
                                    </th>
                                    
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($rehandlings))
                                <tbody>
                                    @forelse($rehandlings as $rehandling)
                                  <tr>
                                    <td>
                                         {{$rehandling->rehandling_number}}
                                    </td>
                                    <td>
                                         {{$rehandling->user ? $rehandling->user->name : ""}} {{$rehandling->user ? $rehandling->user->surname : ""}}
                                    </td>
                                     <td>
                                        @if ($rehandling->driver)
                                                {{$rehandling->driver->employee ? $rehandling->driver->employee->name : ""}} {{$rehandling->driver->employee ? $rehandling->driver->employee->surname : ""}}        
                                        @endif
                                    </td>
                                     <td>{{$rehandling->customer ? $rehandling->customer->name : ""}}</td>
                                    <td>
                                         {{ucfirst($rehandling->type)}}
                                    </td>
                                     <td>
                                         {{ucfirst($rehandling->for)}}
                                    </td>
                                    <td>{{$rehandling->date}}</td>
                                    <td>{{$rehandling->rehandling_start_time}} - {{$rehandling->rehandling_start_time}} </td>
                                    <td>
                                        {{$rehandling->depart_workshop_time}}
                                            <hr style="margin-top:5px; margin-bottom:5px">  
                                         {{$rehandling->arrive_workshop_time}}
                                    </td>
                                    <td>
                                        {{$rehandling->arrive_location_time}}
                                            <hr style="margin-top:5px; margin-bottom:5px">  
                                         {{$rehandling->depart_location_time}}
                                    </td>
                                    <td><span class="badge bg-{{$rehandling->status == 1 ? "success" : "danger"}}">{{$rehandling->status == 1 ? "Active" : "Inactive"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('rehandlings.show', $rehandling->id) }}"   ><i class="fa fa-eye color-default"></i> View</a></li>
                                                <li><a href="#"  wire:click="edit({{$rehandling->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#rehandlingDeleteModal{{ $rehandling->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('rehandlings.delete')
                                    </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="12">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Rehandlings Found ....
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
                                    @if (isset($rehandlings))
                                        {{ $rehandlings->links() }} 
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

   
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="rehandlingModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-60" role="document">
        <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Rehandling <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Shifts<span class="required" style="color: red">*</span></label>
                        <select class="form-control" wire:model.debounce.300ms="shift_id" required>
                            <option value="">Select Shift</option>
                            @foreach ($shifts as $shift)
                                <option value="{{$shift->id}}">{{$shift->shift_number}}</option>
                            @endforeach
                        </select>
                        @error('shift_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                           <small><a href="{{ route('shifts.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Shift</a></small> <a href="#" wire:click.prevent="refresh('shifts')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                    </div>
                    <div class="row">
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Start Time</label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="start_time" placeholder="Enter Start Time"/>
                                @error('start_time') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Open Hours</label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="open_hours" placeholder="Enter Open Engine Hours"/>
                                @error('open_hours') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Open Mileage</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="open_mileage" placeholder="Enter Open Mileage"/>
                                @error('open_mileage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                              <div class="form-group">
                                <label for="name">Work Descriptions<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="work_id" required>
                                    <option value="">Select Work/Job</option>
                                    @foreach ($works as $work)
                                        <option value="{{$work->id}}">{{$work->description}}</option>
                                    @endforeach
                                </select>
                                @error('work_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                <small><a href="{{ route('works.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Work</a></small> <a href="#" wire:click.prevent="refresh('works')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                            </div>
                        </div>
                        <div class="col-md-6">
                              <div class="form-group">
                                <label for="name">Locations / Work Sites</label>
                                <select class="form-control" wire:model.debounce.300ms="location_id">
                                    <option value="">Select Site</option>
                                    @foreach ($locations as $location)
                                        <option value="{{$location->id}}">{{$location->name}}</option>
                                    @endforeach
                                </select>
                                @error('location_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                <small><a href="{{ route('locations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Location / WorkSite</a></small> <a href="#" wire:click.prevent="refresh('locations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                            </div>
                        </div>
                    </div>
               
                    <div class="row">
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Close Time</label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="close_time" placeholder="Enter Close Time"/>
                                @error('close_time') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Close Hours</label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="close_hours" placeholder="Enter Close Engine Hours"/>
                                @error('close_hours') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Close Mileage</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="close_mileage" placeholder="Enter Close Mileage"/>
                                @error('close_mileage') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="rehandlingEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Loading Point <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >

                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Name<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="name" id="autocomplete-edit" placeholder="Enter Name" required/>
                        @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Contact Name</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="contact_name" placeholder="Enter Name" />
                                @error('contact_name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Contact Surname</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="contact_surname" placeholder="Enter Surname" />
                                @error('contact_surname') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Email</label>
                                <input type="email" class="form-control" wire:model.debounce.300ms="email" placeholder="Enter Email" />
                                @error('email') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Phonenumber</label>
                                <input type="number" class="form-control" wire:model.debounce.300ms="phonenumber" placeholder="Enter Phonenumber" />
                                @error('phonenumber') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <center> <small style="color: red"><a href="https://www.google.com/maps" target="_blank">Click me to go on Google Maps</a></small></center>
                    </div>
                    <div class="form-group">
                        <label for="description">Google Maps Location Pin<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="location" placeholder="Copy and Paste Loading Point Location Pin from Google Maps" required>
                        @error('location') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="description">Latitude<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="lat" placeholder="Enter Latitude" required disabled>
                                @error('lat') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="description">Longitude<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="long" placeholder="Enter Longitude" required disabled>
                                @error('long') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Assessment Expires</label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="expiry_date"  />
                                @error('expiry_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Description</label>
                               <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="3"></textarea>
                                @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

