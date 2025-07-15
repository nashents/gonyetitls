<div>
    <div class="row mt-30">
    
        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1" >

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab">Reminder Details</a></li>
            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">

                        <tbody class="text-center line-height-35 ">

                            <tr>
                                <th class="w-10 text-center line-height-35">CreatedBy</th>
                                <td class="w-20 line-height-35">{{$fitness->user ? $fitness->user->name : ""}} {{$fitness->user ? $fitness->user->surname : ""}} </td>
                            </tr>
                            @if ($fitness->type)
                            <tr>
                                <th class="w-10 text-center line-height-35">Type</th>
                                <td class="w-20 line-height-35">{{$fitness->type}}</td>
                            </tr>
                            @endif
                          
                            <tr>
                                <th class="w-10 text-center line-height-35">Reminder</th>
                                <td class="w-20 line-height-35">{{$fitness->reminder_item ? $fitness->reminder_item->name : ""}}</td>
                            </tr>
                                @if ($fitness->horse)
                                <tr>
                                    <th class="w-10 text-center line-height-35">Reminder For</th>
                                    <td class="w-20 line-height-35"> {{$fitness->horse->horse_make ? $fitness->horse->horse_make->name : ""}} {{$fitness->horse->horse_model ? $fitness->horse->horse_model->name : ""}} {{$fitness->horse ? $fitness->horse->registration_number : ""}}</td>
                                </tr>
                                @elseif ($fitness->vehicle)
                                <tr>
                                    <th class="w-10 text-center line-height-35">Reminder For</th>
                                    <td class="w-20 line-height-35"> {{$fitness->vehicle->vehicle_make ? $fitness->vehicle->vehicle_make->name : ""}} {{$fitness->vehicle->vehicle_model ? $fitness->vehicle->vehicle_model->name : ""}} {{$fitness->vehicle ? $fitness->vehicle->registration_number : ""}}</td>
                                </tr>
                                @elseif($fitness->trailer)  
                                <tr>
                                    <th class="w-10 text-center line-height-35">Reminder For</th>
                                    <td class="w-20 line-height-35"> {{$fitness->trailer ? $fitness->trailer->registration_number : ""}}</td>
                                </tr>  
                                @elseif($fitness->employee)    
                                <tr>
                                    <th class="w-10 text-center line-height-35">Reminder For</th>
                                    <td class="w-20 line-height-35"> {{$fitness->employee ? $fitness->employee->name : ""}} {{$fitness->employee ? $fitness->employee->surname : ""}}</td>
                                </tr>  
                                @endif
                                
                                <tr>
                                    <th class="w-10 text-center line-height-35">Issued @</th>
                                    <td class="w-20 line-height-35">
                                            @if ((preg_match($pattern, $fitness->issued_at)) )
                                                On {{ \Carbon\Carbon::parse($fitness->issued_at)->format('d M Y g:i A')}}
                                            @else
                                                On {{$fitness->issued_at}}
                                            @endif  
                                    </td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">First Reminder</th>
                                    <td class="w-20 line-height-35">
                                        @if ((preg_match($pattern, $fitness->first_reminder_at)) )
                                                On {{ \Carbon\Carbon::parse($fitness->first_reminder_at)->format('d M Y g:i A')}}
                                        @else
                                            On {{$fitness->first_reminder_at}}
                                        @endif  
                                     
                                        <span class="badge bg-{{$fitness->first_reminder_at_status == 1 ? "success" : "warning"}}">{{$fitness->first_reminder_at_status == 1 ? "Sent" : "Not Sent"}}</span></td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">2nd Reminder</th>
                                    <td class="w-20 line-height-35">
                                        @if ((preg_match($pattern, $fitness->second_reminder_at)) )
                                            On {{ \Carbon\Carbon::parse($fitness->second_reminder_at)->format('d M Y g:i A')}}
                                        @else
                                            On {{$fitness->second_reminder_at}}
                                        @endif   
                                        <span class="badge bg-{{$fitness->second_reminder_at_status == 1 ? "success" : "warning"}}">{{$fitness->second_reminder_at_status == 1 ? "Sent" : "Not Sent"}}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">3rd Reminder</th>
                                    <td class="w-20 line-height-35">
                                        @if ((preg_match($pattern, $fitness->third_reminder_at)) )
                                            On {{ \Carbon\Carbon::parse($fitness->third_reminder_at)->format('d M Y g:i A')}}
                                        @else
                                            On {{$fitness->third_reminder_at}}
                                        @endif  
                                        <span class="badge bg-{{$fitness->third_reminder_at_status == 1 ? "success" : "warning"}}">{{$fitness->third_reminder_at_status == 1 ? "Sent" : "Not Sent"}}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Expiry Date</th>
                                    <td class="w-20 line-height-35">
                                        @if ((preg_match($pattern, $fitness->expires_at)) )
                                            On {{ \Carbon\Carbon::parse($fitness->expires_at)->format('d M Y g:i A')}}
                                        @else
                                            On {{$fitness->expires_at}}
                                        @endif  
                                    </td>
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Status</th>
                                    <td class="w-20 line-height-35">
                                        @if ($fitness->expires_at >= now()->toDateTimeString())
                                        <span class="badge bg-success">Valid</span>
                                        @else
                                        <span class="badge bg-danger">Expired</span>        
                                        @endif
                                    </td>
                                   
                                </tr>
                                <tr>
                                    <th class="w-10 text-center line-height-35">Closed</th>
                                    <td class="w-20 line-height-35">
                                        @if ($fitness->closed == 0)
                                        <span class="badge bg-success">Open</span>
                                        @else
                                        <span class="badge bg-danger">Closed</span>        
                                        @endif
                                    </td>
                                   
                                </tr>
                                <tr>
                                    <th colspan="2">
                                        <div class="row">
                                            <center>
                                                @if ($fitness->closed == 0)
                                                <a href="#" wire:click="edit({{ $fitness->id }})"  class="btn btn-success border-primary btn-rounded btn-wide"><i class="fa fa-edit"></i>Edit</a>
                                                <a href="#" wire:click="close({{ $fitness->id }})"  class="btn btn-danger border-primary btn-rounded btn-wide"><i class="fa fa-remove"></i>Close</a>
                                                <a href="#" wire:click="snooze({{ $fitness->id }})"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-bell"></i>Snoose</a>
                                                @endif
                                               
                                            </center>
                                            
                                        </div>
                                    </th>
                                   
                                    
                                </tr>
                             
                        </tbody>
                    </table>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="btn-group pull-right mt-10" >
                           <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                        </div>
                    </div>
                    </div>

            </div>
        </div>
        <!-- /.col-md-9 -->
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
                                @if ($fitness->horse)
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
                                @elseif ($fitness->vehicle)
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
                                @elseif ($fitness->trailer)
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
                                @elseif ($fitness->employee)
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
                                @else 
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
                                @endif
                          
                      
                       
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
