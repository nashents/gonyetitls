<div>
    <section class="section">
        <x-loading/>
        <div class="allocation-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div>
                                @include('includes.messages')
                            </div>
                            <div class="panel-title" style="float: right">
                                {{-- <a href="{{route('allocations.export.excel')}}"  class="btn btn-default"><i class="fa fa-download"></i>Excel</a>
                                <a href="{{route('allocations.export.csv')}}"  class="btn btn-default"><i class="fa fa-download"></i>CSV</a>
                                <a href="{{route('allocations.export.pdf')}}"  class="btn btn-default"><i class="fa fa-download"></i>PDF</a> --}}
                                {{-- <a href="" data-toggle="modal" data-target="#allocationsImportModal" class="btn btn-default"><i class="fa fa-upload"></i>Import</a> --}}
                            </div>
                            <div class="panel-title">
                                <a href="" data-toggle="modal" data-target="#allocationModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Allocation</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="allocationsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Allocation#
                                    </th>
                                    <th class="th-sm">Type
                                    </th>
                                    <th class="th-sm">Employee
                                    </th>
                                    <th class="th-sm">Vehicle
                                    </th>
                                    <th class="th-sm">Station
                                    </th>
                                    <th class="th-sm">Fuel Type
                                    </th>
                                    <th class="th-sm">Qty
                                    </th>
                                    <th class="th-sm">Rate
                                    </th>
                                    <th class="th-sm">Total
                                    </th>
                                    <th class="th-sm">Bal
                                    </th>
                                    <th class="th-sm">Validity
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($allocations->count()>0)
                                <tbody>
                                    @foreach ($allocations as $allocation)
                                  <tr>
                                    <td>{{$allocation->allocation_number}}</td>
                                    <td>{{$allocation->allocation_type}}</td>
                                    <td>{{$allocation->employee->name}} {{$allocation->employee->surname}} ({{$allocation->employee->employee_number}})</td>
                                    <td>{{$allocation->vehicle->vehicle_make ? $allocation->vehicle->vehicle_make->name : ""}} {{$allocation->vehicle->vehicle_model ? $allocation->vehicle->vehicle_model->name : ""}} ({{$allocation->vehicle->registration_number}})</td>
                                    <td>{{$allocation->container ? $allocation->container->name : ""}}</td>
                                    <td>{{$allocation->fuel_type}}</td>
                                    <td>{{$allocation->quantity}}l</td>
                                    <td>{{$allocation->container->currency ? $allocation->container->currency->symbol : ""}}{{number_format($allocation->rate,2)}}</td>
                                    <td>{{$allocation->container->currency ? $allocation->container->currency->symbol : ""}}{{number_format($allocation->amount,2)}}</td>
                                    <td>{{$allocation->balance}}l</td>
                                    @php
                                        $now =  Carbon\Carbon::now();
                                    @endphp
                                    <td><span class="label label-{{$allocation->expiry_date >= $now  ? "success" : "danger"}} label-rounded">{{$allocation->expiry_date >= $now ? "Active" : "Expired"}}</span></td>
                                    <td><span class="label label-{{$allocation->status == 1 ? "success" : "danger"}} label-rounded">{{$allocation->status == 1 ? "Active" : "Expired"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('allocations.show',$allocation->id) }}"  ><i class="fa fa-eye color-default"></i> View</a></li>
                                                <li><a href="#"  wire:click="edit({{$allocation->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#allocationDeleteModal{{ $allocation->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('allocations.delete')
                                </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                                @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                 @endif
                              </table>

                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.row -->

        </div>
        <!-- /.allocation-fluid -->
    </section>
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="allocationModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Fuel Allocation <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                        <div class="form-group">
                            <label for="allocation_type">Allocation Type<span class="required" style="color: red">*</span></label>
                           <select wire:model.debounce.300ms="allocation_type" class="form-control" required >
                               <option value="">Select Allocation Type</option>
                                <option value="Directors">Directors</option>
                                <option value="Management">Management</option>
                                <option value="Employee">Employee</option>
                           </select>
                            @error('allocation_type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        </div>
                        <div class="col-md-6">
                        <div class="form-group">
                            <label for="employee_id">Employee(s)<span class="required" style="color: red">*</span></label>
                           <select wire:model.debounce.300ms="employee_id" class="form-control" required>
                               <option value="">Select Employee</option>
                               @foreach ($employees as $employee)
                               @if (!$employee->driver)
                               <option value="{{$employee->id}}">{{$employee->name}} {{$employee->surname}} ({{$employee->employee_number}})</option>
                               @endif
                               
                               @endforeach
                           </select>
                            @error('employee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vehicle_id">Vehicle(s)<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="selectedVehicle" class="form-control" required>
                                   <option value="">Select Vehicle</option>
                                   @foreach ($vehicles as $vehicle)
                                    <option value="{{$vehicle->id}}">{{$vehicle->registration_number}} {{$vehicle->vehicle_make ? $vehicle->vehicle_make->name : ""}} {{$vehicle->vehicle_model ? $vehicle->vehicle_model->name : ""}}</option>
                                   @endforeach
                               </select>
                                @error('selectedVehicle') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="container_id">Fuel Station(s)<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="selectedContainer" class="form-control" required>
                                   <option value="">Select Fueling Station</option>
                                   @foreach ($containers as $container)
                                    <option value="{{$container->id}}">{{$container->name}} ({{$container->fuel_type}}) </option>
                                   @endforeach
                               </select>
                               @if (isset($container_balance))
                                   <small style="color: green">Available fuel in station is {{ $container_balance }} Litres</small>
                               @endif
                                @error('selectedContainer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fuel_type">Fuel Type<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="fuel_type" class="form-control" required>
                                   <option value="">Select Fuel Type</option>
                                    <option value="petrol">Petrol</option>
                                    <option value="diesel">Diesel</option>
                               </select>
                                @error('fuel_type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="expiry">Expiry Date<span class="required" style="color: red">*</span></label>
                                <input type="date"  class="form-control" wire:model.debounce.300ms="expiry" placeholder="Enter Expiry Date" required>
                                @error('expiry') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="quantity">Quantity<span class="required" style="color: red">*</span></label>
                                @if (isset($fuel_tank_capacity) && $fuel_tank_capacity > 0 )
                                    <input type="number" step="any" min="0" max="{{ $fuel_tank_capacity }}" class="form-control" wire:model.debounce.300ms="quantity" placeholder="Enter Quantity" required >
                                @else
                                <input type="number" step="any" min="0"  class="form-control" wire:model.debounce.300ms="quantity" placeholder="Enter Quantity" required >
                                @if (!is_null($selected_vehicle))
                                    <small style="color: red">Fuel tank capacity for <a href="{{ route('vehicles.show',$selected_vehicle->id) }}" style="color: blue" target="_blank">{{ $selected_vehicle->registration_number }}</a> not set.</small>
                                @endif 
                                @endif
                                @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="rate">Rate</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="rate" placeholder="Enter Rate">
                                @error('rate') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                  <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="amount">Total</label>
                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="amount" disabled>
                            @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="balance">Balance</label>
                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="balance" disabled required>
                            @error('balance') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="allocationEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Allocation <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >

                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                            <div class="form-group">
                                <label for="allocation_type">Allocation Type<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="allocation_type" class="form-control"  required>
                                   <option value="">Select Allocation Type</option>
                                    <option value="Directors">Directors</option>
                                    <option value="Management">Management</option>
                                    <option value="Employee">Employee</option>
                               </select>
                                @error('allocation_type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                            </div>
                            <div class="col-md-6">
                            <div class="form-group">
                                <label for="employee_id">Employee(s)<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="employee_id" class="form-control" required>
                                   <option value="">Select Employee</option>
                                   @foreach ($employees as $employee)
                                   @if (!$employee->driver)
                                   <option value="{{$employee->id}}">{{$employee->name}} {{$employee->surname}} ({{$employee->employee_number}})</option>
                                   @endif
                                   
                                   @endforeach
                               </select>
                                @error('employee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                            </div>
    
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="vehicle_id">Vehicle(s)<span class="required" style="color: red">*</span></label>
                                   <select wire:model.debounce.300ms="selectedVehicle" class="form-control" required>
                                       <option value="">Select Vehicle</option>
                                       @foreach ($vehicles as $vehicle)
                                        <option value="{{$vehicle->id}}">{{$vehicle->registration_number}} {{$vehicle->vehicle_make ? $vehicle->vehicle_make->name : ""}} {{$vehicle->vehicle_model ? $vehicle->vehicle_model->name : ""}}</option>
                                       @endforeach
                                   </select>
                                    @error('selectedVehicle') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="container_id">Fuel Station(s)<span class="required" style="color: red">*</span></label>
                                   <select wire:model.debounce.300ms="selectedContainer" class="form-control" required>
                                       <option value="">Select Fueling Station</option>
                                       @foreach ($containers as $container)
                                        <option value="{{$container->id}}">{{$container->name}} ({{$container->fuel_type}}) </option>
                                       @endforeach
                                   </select>
                                   @if (isset($container_balance))
                                   <small style="color: green">Available fuel in station is {{ $container_balance }} Litres</small>
                               @endif
                                    @error('selectedContainer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="fuel_type">Fuel Type<span class="required" style="color: red">*</span></label>
                                   <select wire:model.debounce.300ms="fuel_type" class="form-control" required>
                                       <option value="">Select Fuel Type</option>
                                        <option value="petrol">Petrol</option>
                                        <option value="diesel">Diesel</option>
                                   </select>
                                    @error('fuel_type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="expiry">Expiry Date <span class="required" style="color: red">*</span></label>
                                    <input type="date"  class="form-control" wire:model.debounce.300ms="expiry" placeholder="Enter Expiry Date" required>
                                    @error('expiry') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
    
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="quantity">Quantity<span class="required" style="color: red">*</span></label>
                                    @if (isset($fuel_tank_capacity) && $fuel_tank_capacity > 0 )
                                    <input type="number" step="any" min="0" max="{{ $fuel_tank_capacity }}" class="form-control" wire:model.debounce.300ms="quantity" placeholder="Enter Quantity" required >
                                    @else
                                    <input type="number" step="any" min="0"  class="form-control" wire:model.debounce.300ms="quantity" placeholder="Enter Quantity" required >
                                    @if (!is_null($selected_vehicle))
                                        <small style="color: red">Fuel tank capacity for <a href="{{ route('vehicles.show',$selected_vehicle->id) }}" style="color: blue" target="_blank">{{ $selected_vehicle->registration_number }}</a> not set.</small>
                                    @endif 
                                    @endif
                                    
                                    @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="rate">Rate</label>
                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="rate" placeholder="Enter Rate">
                                    @error('rate') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                      <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="amount">Total</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="amount" disabled>
                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="balance">Balance</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="balance" disabled required>
                                @error('balance') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

