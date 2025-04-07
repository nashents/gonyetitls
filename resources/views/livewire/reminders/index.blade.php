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
                                <a href="" data-toggle="modal" data-target="#fitnessModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Reminder</a>
                                <a href="#" wire:click="exportRemindersExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                <a href="#" wire:click="exportRemindersCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                <a href="#" wire:click="exportRemindersPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search reminders...">
                                </div>
                            </div>
                            <table  class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead >
                                    <th class="th-sm">Reminder
                                    </th>
                                    <th class="th-sm">Issued @
                                    </th>
                                    <th class="th-sm">Expires At
                                    </th>
                                    <th class="th-sm">1st Reminder @
                                    </th>
                                    <th class="th-sm">2nd Reminder @
                                    </th>
                                    <th class="th-sm">3rd Reminder @
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                <tbody>
                                    @forelse ($reminders as $reminder)
                                  <tr>
                                    <td>{{$reminder->reminder_item ? $reminder->reminder_item->name : ""}}
                                        @if (isset($reminder->horse))
                                        for {{$reminder->horse->horse_make ? $reminder->horse->horse_make->name : ""}} {{$reminder->horse->horse_make ? $reminder->horse->horse_model->name : ""}} {{$reminder->horse->registration_number}}
                                        @elseif ($reminder->employee)
                                        for {{$reminder->employee->name}} {{$reminder->employee->surname}}
                                        @elseif (isset($reminder->vehicle))
                                        for {{$reminder->vehicle->vehicle_make ? $reminder->vehicle->vehicle_make->name : ""}} {{$reminder->vehicle->vehicle_make ? $reminder->vehicle->vehicle_model->name : ""}} {{$reminder->vehicle->registration_number}}
                                        @elseif (isset($reminder->trailer))
                                        for trailer {{$reminder->trailer->registration_number}}
                                        @endif
                                    </td>
                                    <td>{{Carbon\Carbon::parse($reminder->issued_at)->format('d M Y h:i a')}}</td>
                                    <td>{{Carbon\Carbon::parse($reminder->expires_at)->format('d M Y h:i a')}}</td>
                                    <td>{{Carbon\Carbon::parse($reminder->first_reminder_at)->format('d M Y h:i a')}}</td>
                                    <td>{{Carbon\Carbon::parse($reminder->second_reminder_at)->format('d M Y h:i a')}}</td>
                                    <td>{{Carbon\Carbon::parse($reminder->third_reminder_at)->format('d M Y h:i a')}}</td>
                                    <td>
                                        @if ($reminder->expires_at >= now()->toDateTimeString())
                                        <span class="badge bg-success">Valid</span>
                                        @else
                                        <span class="badge bg-danger">Expired</span>        
                                        @endif
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('fitnesses.show',$reminder->id) }}" ><i class="fa fa-eye color-default"></i> View</a></li>
                                                <li><a href="#" wire:click="edit({{$reminder->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#reminderDeleteModal{{$reminder->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                            @include('reminders.delete')
                                        </div>
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="8">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Reminders Found ....
                                        </div>
                                       
                                    </td>
                                  </tr>  
                                    @endforelse
                                </tbody>
                              </table>
                              <nav class="text-center" style="float: right">
                                <ul class="pagination rounded-corners">
                                    @if (isset($reminders))
                                        {{ $reminders->links() }} 
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="fitnessModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Reminder <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        @if (!isset($type))
                        <div class="col-md-6">
                            {{-- <h5 class="underline mt-30">Reminder For ?</h5> --}}
                            <label for="exampleInputEmail13">Reminder For ?<span class="required" style="color: red">*</span></label>
                            <div class="mb-10">
                                <input type="radio" wire:model.debounce.300ms="type" value="Horse"  class="line-style"  />
                                <label for="one" class="radio-label">Horse</label>
                                <input type="radio" wire:model.debounce.300ms="type" value="Vehicle"  class="line-style"  />
                                <label for="one" class="radio-label">Vehicle</label>
                                <input type="radio" wire:model.debounce.300ms="type" value="Trailer"  class="line-style"  />
                                <label for="one" class="radio-label">Trailer</label>
                                <br>
                                <input type="radio" wire:model.debounce.300ms="type" value="Employee"  class="line-style"  />
                                <label for="one" class="radio-label">Employee</label>
                                <input type="radio" wire:model.debounce.300ms="type" value="Other"  class="line-style"  />
                                <label for="one" class="radio-label">Other</label>
                            </div>  
                        </div>
                        @endif
                        
                            @if (isset($type))
                                @if ($type == "Horse")
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="exampleInputEmail13">Horses<span class="required" style="color: red">*</span></label>
                                            <input type="text" wire:model.debounce.300ms="searchHorse" placeholder="Search horse..." class="form-control" >
                                            <select wire:model.debounce.300ms="selectedHorse" class="form-control" required size="4">
                                                <option value="">Select Horse </option>
                                                @foreach ($horses as $horse)
                                                    <option value="{{$horse->id}}">{{$horse->registration_number}} {{$horse->horse_make ? $horse->horse_make->name : ""}} {{$horse->horse_model ? $horse->horse_model->name : ""}}</option>
                                                @endforeach
                                            </select>
                                            @error('selectedHorse') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                @elseif ($type == "Vehicle")
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="exampleInputEmail13">Vehicles<span class="required" style="color: red">*</span></label>
                                            <input type="text" wire:model.debounce.300ms="searchVehicle" placeholder="Search vehicle..." class="form-control" >
                                            <select wire:model.debounce.300ms="selectedVehicle" class="form-control" required size="4">
                                                <option value="">Select Vehicle </option>
                                                @foreach ($vehicles as $vehicle)
                                                    <option value="{{$vehicle->id}}">{{$vehicle->registration_number}} {{$vehicle->vehicle_make ? $vehicle->vehicle_make->name : ""}} {{$vehicle->vehicle_model ? $vehicle->vehicle_model->name : ""}}</option>
                                                @endforeach
                                            </select>
                                            @error('selectedVehicle') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                @elseif ($type == "Trailer")
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="exampleInputEmail13">Trailers<span class="required" style="color: red">*</span></label>
                                            <input type="text" wire:model.debounce.300ms="searchTrailer" placeholder="Search trailer..." class="form-control" >
                                            <select wire:model.debounce.300ms="selectedTrailer" class="form-control" required size="4">
                                                <option value="">Select Trailer</option>
                                                @foreach ($trailers as $trailer)
                                                    <option value="{{$trailer->id}}">{{$trailer->registration_number}} {{$trailer->make}} {{$trailer->model}}</option>
                                                @endforeach
                                            </select>
                                            @error('selectedTrailer') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                @elseif ($type == "Employee")
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="exampleInputEmail13">Employees<span class="required" style="color: red">*</span></label>
                                            <input type="text" wire:model.debounce.300ms="searchEmployee" placeholder="Search employee..." class="form-control" >
                                            <select wire:model.debounce.300ms="selectedEmployee" class="form-control" required size="4">
                                                <option value="">Select Trailer</option>
                                                @foreach ($employees as $employee)
                                                    <option value="{{$employee->id}}">{{$employee->name}} {{$employee->surname}}</option>
                                                @endforeach
                                            </select>
                                            @error('selectedEmployee') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                @elseif ($type == "Other")
                                <div class="col-md-6">
                                    <label for="exampleInputEmail13">Reminder For ?<span class="required" style="color: red">*</span></label>
                                    <div class="mb-10">
                                        <input type="radio" wire:model.debounce.300ms="type" value="Horse"  class="line-style"  />
                                        <label for="one" class="radio-label">Horse</label>
                                        <input type="radio" wire:model.debounce.300ms="type" value="Vehicle"  class="line-style"  />
                                        <label for="one" class="radio-label">Vehicle</label>
                                        <input type="radio" wire:model.debounce.300ms="type" value="Trailer"  class="line-style"  />
                                        <label for="one" class="radio-label">Trailer</label>
                                        <br>
                                        <input type="radio" wire:model.debounce.300ms="type" value="Employee"  class="line-style"  />
                                        <label for="one" class="radio-label">Employee</label>
                                        <input type="radio" wire:model.debounce.300ms="type" value="Other"  class="line-style"  />
                                        <label for="one" class="radio-label">Other</label>
                                    </div>  
                                </div>
                                @endif
                            @endif
                      
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Reminder Item(s)<span class="required" style="color: red">*</span></label>
                                <select wire:model.lazy="reminder_item_id" class="form-control" required>
                                    <option value="">Select Reminder</option>
                                   @foreach ($reminder_items as $reminder_item)
                                       <option value="{{ $reminder_item->id }}">{{ $reminder_item->name }}</option>
                                   @endforeach
                                </select>
                                <small>  <a href="{{ route('reminder_items.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Reminder Item</a></small> 
                                @error('reminder_item_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="number">Issued@<span class="required" style="color: red">*</span></label>
                            <input type="datetime-local" class="form-control"  wire:model.debounce.300ms="issued_at" placeholder="Issue Date" required>
                            @error('issued_at') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="number">Expires@<span class="required" style="color: red">*</span></label>
                            <input type="datetime-local" class="form-control"  wire:model.debounce.300ms="expires_at" placeholder="Expiry Date" required>
                            @error('expires_at') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
      <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="fitnessEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Reminder <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="row">
                        @if (!isset($type))
                        <div class="col-md-6">
                            {{-- <h5 class="underline mt-30">Reminder For ?</h5> --}}
                            <label for="exampleInputEmail13">Reminder For ?<span class="required" style="color: red">*</span></label>
                            <div class="mb-10">
                                <input type="radio" wire:model.debounce.300ms="type" value="Horse"  class="line-style"  />
                                <label for="one" class="radio-label">Horse</label>
                                <input type="radio" wire:model.debounce.300ms="type" value="Vehicle"  class="line-style"  />
                                <label for="one" class="radio-label">Vehicle</label>
                                <input type="radio" wire:model.debounce.300ms="type" value="Trailer"  class="line-style"  />
                                <label for="one" class="radio-label">Trailer</label>
                                <br>
                                <input type="radio" wire:model.debounce.300ms="type" value="Employee"  class="line-style"  />
                                <label for="one" class="radio-label">Employee</label>
                                <input type="radio" wire:model.debounce.300ms="type" value="Other"  class="line-style"  />
                                <label for="one" class="radio-label">Other</label>
                            </div>  
                        </div>
                        @endif
                        
                            @if (isset($type))
                                @if ($type == "Horse")
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="exampleInputEmail13">Horses<span class="required" style="color: red">*</span></label>
                                            <input type="text" wire:model.debounce.300ms="searchHorse" placeholder="Search horse..." class="form-control" >
                                            <select wire:model.debounce.300ms="selectedHorse" class="form-control" required size="4">
                                                <option value="">Select Horse </option>
                                                @foreach ($horses as $horse)
                                                    <option value="{{$horse->id}}">{{$horse->registration_number}} {{$horse->horse_make ? $horse->horse_make->name : ""}} {{$horse->horse_model ? $horse->horse_model->name : ""}}</option>
                                                @endforeach
                                            </select>
                                            @error('selectedHorse') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                @elseif ($type == "Vehicle")
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="exampleInputEmail13">Vehicles<span class="required" style="color: red">*</span></label>
                                            <input type="text" wire:model.debounce.300ms="searchVehicle" placeholder="Search vehicle..." class="form-control" >
                                            <select wire:model.debounce.300ms="selectedVehicle" class="form-control" required size="4">
                                                <option value="">Select Vehicle </option>
                                                @foreach ($vehicles as $vehicle)
                                                    <option value="{{$vehicle->id}}">{{$vehicle->registration_number}} {{$vehicle->vehicle_make ? $vehicle->vehicle_make->name : ""}} {{$vehicle->vehicle_model ? $vehicle->vehicle_model->name : ""}}</option>
                                                @endforeach
                                            </select>
                                            @error('selectedVehicle') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                @elseif ($type == "Trailer")
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="exampleInputEmail13">Trailers<span class="required" style="color: red">*</span></label>
                                            <input type="text" wire:model.debounce.300ms="searchTrailer" placeholder="Search trailer..." class="form-control" >
                                            <select wire:model.debounce.300ms="selectedTrailer" class="form-control" required size="4">
                                                <option value="">Select Trailer</option>
                                                @foreach ($trailers as $trailer)
                                                    <option value="{{$trailer->id}}">{{$trailer->registration_number}} {{$trailer->make}} {{$trailer->model}}</option>
                                                @endforeach
                                            </select>
                                            @error('selectedTrailer') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                @elseif ($type == "Employee")
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="exampleInputEmail13">Employees<span class="required" style="color: red">*</span></label>
                                            <input type="text" wire:model.debounce.300ms="searchEmployee" placeholder="Search employee..." class="form-control" >
                                            <select wire:model.debounce.300ms="selectedEmployee" class="form-control" required size="4">
                                                <option value="">Select Trailer</option>
                                                @foreach ($employees as $employee)
                                                    <option value="{{$employee->id}}">{{$employee->name}} {{$employee->surname}}</option>
                                                @endforeach
                                            </select>
                                            @error('selectedEmployee') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                @elseif ($type == "Other")
                                <div class="col-md-6">
                                    <label for="exampleInputEmail13">Reminder For ?<span class="required" style="color: red">*</span></label>
                                    <div class="mb-10">
                                        <input type="radio" wire:model.debounce.300ms="type" value="Horse"  class="line-style"  />
                                        <label for="one" class="radio-label">Horse</label>
                                        <input type="radio" wire:model.debounce.300ms="type" value="Vehicle"  class="line-style"  />
                                        <label for="one" class="radio-label">Vehicle</label>
                                        <input type="radio" wire:model.debounce.300ms="type" value="Trailer"  class="line-style"  />
                                        <label for="one" class="radio-label">Trailer</label>
                                        <br>
                                        <input type="radio" wire:model.debounce.300ms="type" value="Employee"  class="line-style"  />
                                        <label for="one" class="radio-label">Employee</label>
                                        <input type="radio" wire:model.debounce.300ms="type" value="Other"  class="line-style"  />
                                        <label for="one" class="radio-label">Other</label>
                                    </div>  
                                </div>
                                @endif
                            @endif
                      
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Reminder Item(s)<span class="required" style="color: red">*</span></label>
                                <select wire:model.lazy="reminder_item_id" class="form-control" required>
                                    <option value="">Select Reminder</option>
                                   @foreach ($reminder_items as $reminder_item)
                                       <option value="{{ $reminder_item->id }}">{{ $reminder_item->name }}</option>
                                   @endforeach
                                </select>
                                <small>  <a href="{{ route('reminder_items.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Reminder Item</a></small> 
                                @error('reminder_item_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="number">Issued@<span class="required" style="color: red">*</span></label>
                            <input type="datetime-local" class="form-control"  wire:model.debounce.300ms="issued_at" placeholder="Issue Date" required>
                            @error('issued_at') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="number">Expires@<span class="required" style="color: red">*</span></label>
                            <input type="datetime-local" class="form-control"  wire:model.debounce.300ms="expires_at" placeholder="Expiry Date" required>
                            @error('expires_at') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                        </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Update</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>



</div>

