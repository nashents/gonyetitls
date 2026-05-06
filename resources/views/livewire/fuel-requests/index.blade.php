<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                           
                            
                            <div class="panel-title">
                                <a href="" data-toggle="modal" data-target="#fuel_requestModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Fuel Request</a>

                            </div>

                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                             <div class="col-md-5" style="float: right;">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search fuel requests....">
                                </div>   
                            </div>
                             <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>

                                    <th class="th-sm">Request#
                                    </th>
                                    <th class="th-sm">Employee
                                    </th>
                                    <th class="th-sm">
                                        RequestFor
                                    </th>
                                    <th class="th-sm">Fuel Type
                                    </th>
                                    <th class="th-sm">Qty
                                    </th>
                                    <th class="th-sm">Reason
                                    </th>
                                    <th class="th-sm">Auth
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($fuel_requests))
                                    <tbody>
                                        @forelse ($fuel_requests as $fuel_request)
                                            <tr>
                                                <td>
                                                    {{$fuel_request->request_number}}
                                                    <small class="text-muted">
                                                        @if ($fuel_request->allocation)
                                                            <strong>Allocation#:</strong>{{ucfirst($fuel_request->allocation ? $fuel_request->allocation->allocation_number : "")}}
                                                        @endif
                                                    </small>
                                                </td>
                                                <td>{{ucfirst($fuel_request->employee->name)}} {{ucfirst($fuel_request->employee->surname)}}</td>
                                                <td>
                                                    @if ($fuel_request->horse)
                                                         Horse | {{$fuel_request->horse ? $fuel_request->horse->registration_number : ""}} {{$fuel_request->horse->fleet_number ? "(".$fuel_request->horse->fleet_number.")" : ""}}  
                                                    @elseif($fuel_request->vehicle)
                                                         Vehicle | {{  $fuel_request->vehicle ? $fuel_request->vehicle->registration_number : "" }} {{$fuel_request->vehicle->fleet_number ? "(".$fuel_request->vehicle->fleet_number.")" : ""}}  
                                                    @elseif($fuel_request->asset)
                                                         Asset | {{$fuel_request->asset->product->brand ? $fuel_request->asset->product->brand->name : ""}} {{$fuel_request->asset->product ? $fuel_request->asset->product->name : ""}}
                                                    @else
                                                        Other
                                                    @endif
                                                </td>
                                                <td>{{$fuel_request->fuel_type}}</td>
                                                <td>{{$fuel_request->quantity ? $fuel_request->quantity."Litres" : ""}}</td>
                                                <td>{{$fuel_request->reason }}</td>
                                                <td><span class="badge bg-{{($fuel_request->authorization == 'approved') ? 'success' : (($fuel_request->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($fuel_request->authorization == 'approved') ? 'approved' : (($fuel_request->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                                <td class="w-10 line-height-35 table-dropdown">
                                                    <div class="dropdown">
                                                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <i class="fa fa-bars"></i>
                                                            <span class="caret"></span>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            @if ($fuel_request->status == "pending")
                                                                <li><a href="#"  wire:click.prevent="edit({{$fuel_request->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                                <li><a href="#" wire:click.prevent="delete({{$fuel_request->id}})" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                                            @endif
                                                        </ul>
                                                    </div>
                                                   
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8">
                                                    <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                        No Fuel Requests Found ....
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
                                    @if (isset($fuel_requests))
                                        {{ $fuel_requests->links() }} 
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
   <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                    <center> <strong>Are you sure you want to delete this Fuel Request?</strong> </center>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="fuel_requestModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Fuel Request <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                  
                        <div class="row">
                            <div class="col-md-6">
                                
                            </div>
                            <div class="col-md-6">
                                <label for="gender">Fuel request for?<span class="required" style="color: red">*</span></label>
                                <div class="mb-10">
                                    <input type="radio" wire:model.debounce.300ms="category" value="Horse"  class="line-style" required  />
                                    <label for="one" class="radio-label">Horse</label>
                                    <input type="radio" wire:model.debounce.300ms="category" value="Vehicle"  class="line-style" />
                                    <label for="one" class="radio-label">Vehicle</label>
                                    <input type="radio" wire:model.debounce.300ms="category" value="Asset"  class="line-style"  />
                                    <label for="one" class="radio-label">Asset</label>
                                    <input type="radio" wire:model.debounce.300ms="category" value="Other"  class="line-style"  />
                                    <label for="one" class="radio-label">Other</label>
                                </div>  
                                @error('category') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="request_type">Employees<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="selectedEmployee" class="form-control" required >
                                        <option value="">Select Employee</option>
                                        @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }} {{ $employee->surname }}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedEmployee') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                @if ($category == "Horse")
                                    <div class="form-group">
                                        <label for="horses">Horses<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="selectedHorse" class="form-control" required>
                                            <option value="">Select Horse</option>
                                            @foreach ($horses as $horse)
                                                <option value="{{$horse->id}}">{{$horse->registration_number}} {{$horse->fleet_number ? "(".$horse->fleet_number.")" : ""}}</option>
                                            @endforeach
                                        </select>
                                        @error('selectedHorse') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                @elseif($category == "Vehicle")
                                    <div class="form-group">
                                        <label for="vehicles">Vehicles<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="selectedVehicle" class="form-control" required>
                                            <option value="">Select Vehicle</option>
                                            @foreach ($vehicles as $vehicle)
                                                <option value="{{$vehicle->id}}"> {{$vehicle->registration_number}} {{$vehicle->fleet_number ? "(".$vehicle->fleet_number.")" : ""}} </option>
                                            @endforeach
                                        </select>
                                        @error('selectedVehicle') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                @elseif($category == "Vehicle")
                                    <div class="form-group">
                                        <label for="drivers">Assets<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="asset_id" class="form-control" required >
                                            <option value="">Select Asset</option>
                                            @if (!is_null($selectedCategory))
                                            @foreach ($assets as $asset)
                                                <option value="{{$asset->id}}">{{$asset->product->brand ? $asset->product->brand->name : ""}} {{$asset->product ? $asset->product->name : ""}}</option>
                                            @endforeach
                                            @endif
                                        </select>
                                        @error('asset_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fuel_type">Fuel Type<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="fuel_type" class="form-control" required >
                                    <option value="">Select Fuel Type</option>
                                    <option value="Petrol">Petrol</option>
                                    <option value="Diesel">Diesel</option>
                                </select>
                                    @error('fuel_type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="quantity">Quantity<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" min="0"   class="form-control" wire:model.debounce.300ms="quantity" placeholder="Enter Quantity" required >  
                                    @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="quantity">Reason for request?<span class="required" style="color: red">*</span></label>
                            <textarea class="form-control" wire:model.debounce.300ms="reason"  cols="30" rows="2" placeholder="Explain why you want fuel?"></textarea>
                            @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="fuel_requestEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Edit Fuel Request <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    
                        <div class="row">
                            <div class="col-md-6">
                                
                            </div>
                            <div class="col-md-6">
                                <label for="gender">Fuel request for?<span class="required" style="color: red">*</span></label>
                                <div class="mb-10">
                                    <input type="radio" wire:model.debounce.300ms="category" value="Horse"  class="line-style" required  />
                                    <label for="one" class="radio-label">Horse</label>
                                    <input type="radio" wire:model.debounce.300ms="category" value="Vehicle"  class="line-style" />
                                    <label for="one" class="radio-label">Vehicle</label>
                                    <input type="radio" wire:model.debounce.300ms="category" value="Asset"  class="line-style"  />
                                    <label for="one" class="radio-label">Asset</label>
                                    <input type="radio" wire:model.debounce.300ms="category" value="Other"  class="line-style"  />
                                    <label for="one" class="radio-label">Other</label>
                                </div>  
                                @error('category') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="request_type">Employees<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="selectedEmployee" class="form-control" required >
                                        <option value="">Select Employee</option>
                                        @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }} {{ $employee->surname }}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedEmployee') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                @if ($category == "Horse")
                                    <div class="form-group">
                                        <label for="horses">Horses<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="selectedHorse" class="form-control" required>
                                            <option value="">Select Horse</option>
                                            @foreach ($horses as $horse)
                                                <option value="{{$horse->id}}">{{$horse->registration_number}} {{$horse->fleet_number ? "(".$horse->fleet_number.")" : ""}}</option>
                                            @endforeach
                                        </select>
                                        @error('selectedHorse') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                @elseif($category == "Vehicle")
                                    <div class="form-group">
                                        <label for="vehicles">Vehicles<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="selectedVehicle" class="form-control" required>
                                            <option value="">Select Vehicle</option>
                                            @foreach ($vehicles as $vehicle)
                                                <option value="{{$vehicle->id}}"> {{$vehicle->registration_number}} {{$vehicle->fleet_number ? "(".$vehicle->fleet_number.")" : ""}} </option>
                                            @endforeach
                                        </select>
                                        @error('selectedVehicle') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                @elseif($category == "Vehicle")
                                    <div class="form-group">
                                        <label for="drivers">Assets<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="asset_id" class="form-control" required >
                                            <option value="">Select Asset</option>
                                            @if (!is_null($selectedCategory))
                                            @foreach ($assets as $asset)
                                                <option value="{{$asset->id}}">{{$asset->product->brand ? $asset->product->brand->name : ""}} {{$asset->product ? $asset->product->name : ""}}</option>
                                            @endforeach
                                            @endif
                                        </select>
                                        @error('asset_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fuel_type">Fuel Type<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="fuel_type" class="form-control" required >
                                    <option value="">Select Fuel Type</option>
                                    <option value="Petrol">Petrol</option>
                                    <option value="Diesel">Diesel</option>
                                </select>
                                    @error('fuel_type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="quantity">Quantity<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" min="0"   class="form-control" wire:model.debounce.300ms="quantity" placeholder="Enter Quantity" required >  
                                    @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="quantity">Reason for request?<span class="required" style="color: red">*</span></label>
                            <textarea class="form-control" wire:model.debounce.300ms="reason"  cols="30" rows="2" placeholder="Explain why you want fuel?"></textarea>
                            @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

