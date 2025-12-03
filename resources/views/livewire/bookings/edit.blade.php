<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="panel">
                    
                        <div class="panel-body">
                            <form wire:submit.prevent="update()" class="p-20" enctype="multipart/form-data">
                                
                             <h5 class="underline mt-30 mb-30">Edit Workshop Booking</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label for="exampleInputEmail13">Transaction Type<span class="required" style="color: red">*</span></label>
                                        <div class="mb-10">
                                            <input type="radio" wire:model.debounce.300ms="transaction_type" value="expense"  class="line-style"  required/>
                                            <label for="one" class="radio-label">Expense</label>
                                            <input type="radio" wire:model.debounce.300ms="transaction_type" value="income"  class="line-style"  required/>
                                            <label for="one" class="radio-label">Income</label>
                                        </div>     
                                    </div>
                                    <div class="col-md-6">
                                        <label for="exampleInputEmail13">Booking For<span class="required" style="color: red">*</span></label>
                                        <div class="mb-10">
                                            <input type="radio" wire:model.debounce.300ms="type" value="Asset"  class="line-style"  required/>
                                            <label for="one" class="radio-label">Asset</label>
                                            <input type="radio" wire:model.debounce.300ms="type" value="Horse"  class="line-style"  required/>
                                            <label for="one" class="radio-label">Horse</label>
                                            <input type="radio" wire:model.debounce.300ms="type" value="Trailer"  class="line-style"  required/>
                                            <label for="one" class="radio-label">Trailer</label>
                                            <input type="radio" wire:model.debounce.300ms="type" value="Vehicle"  class="line-style" required/>
                                            <label for="one" class="radio-label">Vehicle</label>
                                        </div>     
                                    </div>
                                </div>
                             
                                <div class="row">
                                    <div class="col-md-4">
                                        @if ($type == "Horse")
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
                                        @elseif ($type == "Asset")
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
                                        @elseif ($type == "Vehicle")
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
                                        @elseif ($type == "Trailer")
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
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="number">Mileage<span class="required" style="color: red">*</span></label>
                                            <input type="number" step="any" class="form-control" wire:model.debounce.300ms="mileage" {{$type == "Asset" ? "disabled" : ""}} placeholder="Enter Mileage" required>
                                            @error('mileage') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="number">Hours</label>
                                            <input type="number" step="any" class="form-control" wire:model.debounce.300ms="hours" {{$type == "Asset" ? "disabled" : ""}} placeholder="Enter Engine Hours">
                                            @error('hours') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="exampleInputEmail13">Job Types<span class="required" style="color: red">*</span></label>
                                       <select wire:model.debounce.300ms="service_type_id" class="form-control" required>
                                           <option value="">Select Job Type</option>
                                         @foreach ($service_types as $service_type)
                                            <option value="{{$service_type->id}}">{{$service_type->name}}</option>
                                         @endforeach
                                       </select>
                                            @error('service_type_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small><a href="{{ route('service_types.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Service Type</a></small> <a href="#" wire:click.prevent="refresh('service_types')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                </div>
                                 <label for="exampleInputEmail13">Assigned To<span class="required" style="color: red">*</span></label>
                                    <div class="mb-10">
                                        <input type="radio" wire:model.debounce.300ms="assigned_to" value="Mechanic"  class="line-style"  />
                                        <label for="one" class="radio-label">Mechanic</label>
                                        <input type="radio" wire:model.debounce.300ms="assigned_to" value="Vendor"  class="line-style"  />
                                        <label for="one" class="radio-label">Vendor</label>
                                    </div>    
                                            <div class="row">

                                    <div class="col-md-6">
                                            @if ($assigned_to == "Mechanic")   
                                                @foreach ($inputs as $key => $value)
                                                <div class="row">
                                                    <div class="col-md-10">
                                                        <div class="form-group">
                                                            <label for="stops">AssignedTo<span class="required" style="color: red">*</span></label>
                                                                <select class="form-control" wire:model.debounce.300ms="mechanic_id.{{ $value }}" required size="4">
                                                                  <option value="" disabled>Select Mechanic </option>
                                                                    @foreach ($mechanics as $mechanic)
                                                                        <option value="{{$mechanic->id}}"
                                                                        @if(in_array($mechanic->id, $mechanic_id ?? []) && ($mechanic_id[$value] ?? null) != $mechanic->id) 
                                                                            disabled 
                                                                        @endif
                                                                            >{{$mechanic->name}} {{$mechanic->surname}}</option>
                                                                    @endforeach
                                                                </select>
                                                                @error('mechanic_id.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                        </div> 
                                                    </div>
                                                   
                                                    <div class="col-md-1">
                                                        <div class="form-group" style="padding-top:60px;">
                                                            <label for=""></label>
                                                            <button class="btn btn-danger btn-rounded btn-xs"   wire:click.prevent="remove({{$key}})" > <i class="fa fa-times"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                                <br>
                                                @endforeach
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <button class="btn btn-success btn-rounded btn-xs" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i>Mechanic</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                @else
                                                <div class="form-group">
                                                    <label for="stops">Vendor(s)</label>
                                                    <input type="text" wire:model.debounce.300ms="searchVendor" placeholder="Search vendor..." class="form-control" >
                                                        <select class="form-control" wire:model.debounce.300ms="vendor_id"  size="4">
                                                          <option value="">Select Vendor </option>
                                                            @foreach ($vendors as $vendor)
                                                                <option value="{{$vendor->id}}">{{$vendor->name}} {{$vendor->surname}}</option>
                                                            @endforeach
                                                        </select>
                                                        <small>  <a href="{{ route('vendors.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Vendor</a></small> 
                                                        @error('vendor_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div> 
                                            @endif
                                      
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="exampleInputEmail13">RequestedBy<span class="required" style="color: red">*</span></label>
                                            <input type="text" wire:model.debounce.300ms="searchEmployee" placeholder="Search employee..." class="form-control" >
                                       <select wire:model.debounce.300ms="employee_id" class="form-control"  required size="4">
                                           <option value="">Select Employee</option>
                                           @foreach ($employees as $employee)
                                               <option value="{{$employee->id}}">{{ucfirst($employee->name)}} {{ucfirst($employee->surname)}}</option>
                                           @endforeach
                                       </select>
                                            @error('employee_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <!-- /.col-md-6 -->
                                </div>
                                
                                <div class="form-group">
                                    <label for="stops">Incident Report(s)</label>
                                        <select class="form-control" wire:model.debounce.300ms="breakdown_id"  size="4">
                                            <option value="" disabled>Select Incident </option>
                                            @foreach ($breakdowns as $breakdown)
                                                <option value="{{$breakdown->id}}">{{$breakdown->location}} {{$breakdown->date}}
                                                    @if ($breakdown->horse)
                                                        {{$breakdown->horse->registration_number}} {{$breakdown->horse->fleet_number ? "(".$breakdown->horse->fleet_number.")" : ""}}                                                        
                                                    @elseif($breakdown->vehicle)    
                                                        {{$breakdown->vehicle->registration_number}} {{$breakdown->vehicle->fleet_number ? "(".$breakdown->vehicle->fleet_number.")" : ""}}                                                        
                                                    @elseif($breakdown->trailer)
                                                        {{$breakdown->trailer->registration_number}} {{$breakdown->trailer->fleet_number ? "(".$breakdown->trailer->fleet_number.")" : ""}}                                                        
                                                    @endif
                                                    {{$breakdown->description}}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('breakdown_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div> 
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="number">In Date<span class="required" style="color: red">*</span></label>
                                            <input type="date" class="form-control" wire:model.debounce.300ms="in_date" placeholder="Enter In Date" required>
                                            @error('in_date') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="number">In Time<span class="required" style="color: red">*</span></label>
                                            <input type="time" class="form-control" wire:model.debounce.300ms="in_time" placeholder="Enter In Time" required>
                                            @error('in_time') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="number">Estimated Out Date</label>
                                            <input type="date" class="form-control" wire:model.debounce.300ms="estimated_out_date" placeholder="Enter Estimated Finish Date" >
                                            @error('estimated_out_date') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="number">Estimated Out Time</label>
                                            <input type="time" class="form-control" wire:model.debounce.300ms="estimated_out_time" placeholder="Enter Estimated Finish Time">
                                            @error('estimated_out_time') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="number">Work Stations</label>
                                            <select class="form-control" wire:model.debounce.300ms="station_id">
                                                 <option value="">Select Work Station</option>
                                                 @foreach ($stations as $station)
                                                     <option value="{{$station->id}}">{{$station->name}}</option>
                                                 @endforeach
                                            </select>
                                            
                                            @error('station_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small><a href="{{ route('stations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Work Station</a></small> <a href="#" wire:click.prevent="refresh('stations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="description">Reason/Problem<span class="required"  style="color: red">*</span></label>
                                            <textarea wire:model.debounce.300ms="description" id="" cols="30" class="form-control" rows="5" placeholder="Enter reason for booking" required></textarea>
                                            @error('description') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="btn-group pull-right mt-10" >
                                           <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                                            <button type="submit" class="btn bg-success btn-wide btn-rounded" > <i class="fa fa-refresh"></i>Update</button>
                                        </div>
                                    </div>
                                    </div>
                            </form>

                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>
                <!-- /.col-md-6 -->


            </div>

        </div>
        <!-- /.container-fluid -->
    </section>

@section('extra-js')
<script>

    //define the variable globally
    var selectStr = "";

    function searchSelect(field) {

    key = window.event.keyCode;

    //Only allow the space and upper/lower case letters
    if ( key==32 || (key>=65 && key<=90) || (key>=97 && key<=122) ) {
        letter = String.fromCharCode(key);
        selectStr += letter;
    }

    for (i=0; i<field.options.length; i++) {
        if (field.options[i].text.substr(0,selectStr.length).toLowerCase() == selectStr.toLowerCase()) {
            field.selectedIndex = i;
            break;
        }
    }
    }

    </script>
@endsection
</div>
