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
                                <a href="" data-toggle="modal" data-target="#rentalModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Rental</a>
                                <a href="#" wire:click="exportRentalsExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                <a href="#" wire:click="exportRentalsCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                <a href="#" wire:click="exportRentalsPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                             <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search rentals...">
                                </div>
                              
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Rental#
                                    </th>
                                    <th class="th-sm">Customer
                                    </th>
                                    <th class="th-sm">Vehicle
                                    </th>
                                    <th class="th-sm">Timelines
                                    </th>
                                    <th class="th-sm">Mileage
                                    </th>
                                    <th class="th-sm">Fuel Level
                                    </th>
                                    <th class="th-sm">Ccy
                                    </th>
                                    <th class="th-sm">Rate
                                    </th>
                                    <th class="th-sm">Deposit
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($rentals))
                                <tbody>
                                    @forelse ($rentals as $rental)
                                  <tr>
                                    <td>
                                        {{$rental->car_rental_number}}
                                        
                                    </td>
                                    <td>{{$rental->customer ? $rental->customer->name : ""}}</td>
                                    <td>
                                        @if ($rental->vehicle)
                                            {{$rental->vehicle->vehicle_make ? $rental->vehicle->vehicle_make->name : ""}} {{$rental->vehicle->vehicle_model ? $rental->vehicle->vehicle_model->name : ""}} {{$rental->vehicle->registration_number ? " - ".$rental->vehicle->registration_number : ""}} {{$rental->vehicle->color ? '('.$rental->vehicle->color.')' : "" }}
                                        @endif
                                        <br>
                                        <small>
                                            <strong>Transporter:</strong> {{$rental->transporter ? $rental->transporter->name : ""}} 
                                            @if ($rental->transporter_agreement == true)
                                                <i>(Subcontracted)</i>
                                            @endif
                                        </small>
                                    </td>
                                    <td>
                                        <small>
                                            <strong>Pickup At: </strong>{{$rental->pickup_at}}
                                            <strong>Due At: </strong>{{$rental->due_at}}
                                            <strong>Total Day(s): </strong>{{$rental->days}}
                                            <strong>Return At: </strong>{{$rental->return_at}}
                                        </small>
                                    </td>
                                    <td>
                                        <small>
                                            <strong>Pickup: </strong>{{$rental->pickup_odometer}}
                                            <strong>Return: </strong>{{$rental->return_odometer}}
                                        </small>
                                    </td>
                                    <td>
                                        <small>
                                            <strong>Pickup: </strong>{{$rental->pickup_fuel_level}}
                                            <strong>Return: </strong>{{$rental->return_fuel_level}}
                                        </small>
                                    </td>
                                    <td>
                                        {{$rental->currency ? $rental->currency->name : ""}}
                                    </td>
                                    <td>
                                        {{$rental->currency ? $rental->currency->symbol : ""}}{{number_format($rental->rate_amount ? $rental->rate_amount : 0, 2)}}
                                        @if ($rental->transporter_agreement == True)
                                            <br>
                                            <small>Transporter Rate: {{$rental->currency ? $rental->currency->symbol : ""}}{{number_format($rental->transporter_rate_amount ? $rental->transporter_rate_amount : 0, 2)}}</small>
                                        @endif
                                    </td>
                                    <td>
                                        {{$rental->currency ? $rental->currency->symbol : ""}}{{number_format($rental->deposit_amount ? $rental->deposit_amount : 0, 2)}}
                                    </td>
                                   
                                   <td>
                                        <span class="badge bg-{{ $rental->status == 'active' ? 'success' : ($rental->status == 'closed' ? 'secondary' : ($rental->status == 'cancelled' ? 'danger' : 'warning')) }}">
                                            {{ $rental->status == 'active' ? 'Active' : ($rental->status == 'closed' ? 'Closed' : ($rental->status == 'cancelled' ? 'Cancelled' : 'Reserved')) }}
                                        </span>
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('rentals.show', $rental->id) }}"  ><i class="fa fa-eye color-default"></i> View</a></li>
                                                <li><a href="#" wire:click="edit({{$rental->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#"  wire:click="delete({{$rental->id}})" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="16">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Rentals Found ....
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
                                        @if (isset($rentals))
                                            {{ $rentals->links() }} 
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="rentalsImportModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-upload"></i>Import Rentals <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form action="#" method="POST" enctype="multipart/form-data">
                    {{ csrf_field() }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Upload Rental(s) Excel File</label>
                        <input type="file" class="form-control" name="file" placeholder="Upload rental File" >
                        @error('file') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button  onClick="this.form.submit(); this.disabled=true; this.value='Sending…'; "  class="btn bg-success btn-wide btn-rounded"><i class="fa fa-upload"></i>Upload</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>

<div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="rentalDeleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content bg-danger">
            <div class="modal-body">
               <center> <strong>Are you sure you want to delete this Rental {{$selected_rental?->car_rental_number}}?</strong> </center>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="rentalModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog  mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Rental <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="exampleInputEmail13"><a href="{{ route('transporters.index') }}" target="_blank" style="color: blue">Transporter(s)<span class="required" style="color: red">*</span></a></label>
                                <select wire:model.debounce.300ms="selectedTransporter" class="form-control" required>
                                    <option value="">Select Transporter</option>
                                    @foreach ($transporters as $transporter)
                                    <option value="{{$transporter->id}}">{{$transporter->name}}</option>
                                    @endforeach
                                </select>
                                @error('selectedTransporter') <span class="text-danger error">{{ $message }}</span>@enderror
                                <small>  <a href="{{ route('transporters.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Transporter</a></small> <a href="#" wire:click.prevent="refresh('transporters')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                            </div>
                        </div>
                       
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="horse"><a href="{{ route('vehicles.index') }}" target="_blank" style="color: blue">Vehicle(s)<span class="required" style="color: red">*</span></a></label>
                                <input type="checkbox" wire:model.debounce.300ms="all_vehicles"   class="line-style" />
                                <label for="one" class="radio-label">Select from all vehicles</label>
                                <input type="text" wire:model.debounce.300ms="searchVehicle" placeholder="Search with reg..." class="form-control">
                                <select class="form-control" wire:model.debounce.300ms="selectedVehicle"  size="4" required>
                                    <option value="">Select Vehicle </option>
                                    @if (!is_null($selectedTransporter))
                                    @foreach ($vehicles as $vehicle)
                                    <option value="{{$vehicle->id}}"> {{$vehicle->registration_number}} {{$vehicle->vehicle_make ? $vehicle->vehicle_make->name : ""}} {{$vehicle->vehicle_model ? $vehicle->vehicle_model->name : ""}}</option>
                                    @endforeach
                                    @endif

                                </select>
                                @error('selectedVehicle') <span class="text-danger error">{{ $message }}</span>@enderror
                                <small>  <a href="{{ route('vehicles.create') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Vehicle</a></small> <a href="#" wire:click.prevent="refresh('vehicles')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vat"><a href="{{route('customers.index')}}">Customers<span class="required" style="color: red">*</span></a></label>
                                <select class="form-control" wire:model.debounce.300ms="selectedCustomer" required>
                                    <option value="">Select Customers</option>
                                    @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->name }} </option>                                        
                                    @endforeach
                                </select>
                                <small><a href="{{ route('customers.create') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Customer</a></small> 
                                    <a href="#" wire:click.prevent="refresh('customers')" class="float-end" style="float: right"><i class="fa fa-refresh"></i></a>
                                @error('selectedCustomer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="driver"><a href="{{ route('drivers.index') }}" target="_blank" style="color: blue">Driver(s)</a></label> 
                                <select class="form-control" wire:model.debounce.300ms="driver_id">
                                    <option value="">Select Driver</option>
                                    @foreach ($drivers as $driver)
                                        <option value="{{$driver->id}}">{{$driver->employee->name}} {{$driver->employee->surname}}</option>
                                    @endforeach
                                </select>
                                @error('driver_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                <small>  <a href="{{ route('drivers.create') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Driver</a></small> <a href="#" wire:click.prevent="refresh('drivers')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="mb-10">
                        <input type="checkbox" wire:model.debounce.300ms="transporter_agreement"   class="line-style" />
                        <label for="one" class="radio-label">Is vehicle sub contracted</label>
                        @error('transporter_agreement') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                    <div class="row">
                        @if ($transporter_agreement)
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="vat">Currencies<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="selectedCurrency" required >
                                        <option value="">Select Currency</option>
                                        @foreach ($currencies as $currency)
                                            <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>                                              
                                        @endforeach
                                    </select>
                                    @error('selectedCurrency') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                                @if (!is_null($selectedCurrency))
                                    @if ($company)
                                        @if ($selectedCurrency != $company->currency_id)
                                            <div class="form-group">
                                                <label for="customer">Conversion Rate<span class="required" style="color: red">*</span></label>
                                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate {{$selected_currency ? "From ".$selected_currency->name : ""}} {{$company->currency ? "To ".$company->currency->name : ""}}" required>
                                                @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                                <small style="color: green">{{$selected_currency ? " 1 ".$selected_currency->name." is how much in" : ""}} {{$company->currency ? $company->currency->name." ?" : ""}}</small>
                                                <small>{{$exchange_amount ? "The converted amount is: ".$exchange_amount : ""}}</small> <br>
                                            </div> 
                                        @endif
                                    @endif
                                @endif
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="rate_amount">Rate<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="rate_amount" placeholder="Enter Rate" required/>
                                    @error('rate_amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="transporter_rate_amount">Transporter Rate Amount</label>
                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="transporter_rate_amount" placeholder="Transporter Rate Amount">
                                    @error('transporter_rate_amount') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="deposit_amount">Deposit</label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="deposit_amount" placeholder="Enter Deposit" />
                                    @error('deposit_amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @else
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="vat">Currencies<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="selectedCurrency" required >
                                        <option value="">Select Currency</option>
                                        @foreach ($currencies as $currency)
                                            <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>                                              
                                        @endforeach
                                    </select>
                                    @error('selectedCurrency') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                                @if (!is_null($selectedCurrency))
                                    @if ($company)
                                        @if ($selectedCurrency != $company->currency_id)
                                            <div class="form-group">
                                                <label for="customer">Conversion Rate<span class="required" style="color: red">*</span></label>
                                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate {{$selected_currency ? "From ".$selected_currency->name : ""}} {{$company->currency ? "To ".$company->currency->name : ""}}" required>
                                                @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                                <small style="color: green">{{$selected_currency ? " 1 ".$selected_currency->name." is how much in" : ""}} {{$company->currency ? $company->currency->name." ?" : ""}}</small>
                                                <small>{{$exchange_amount ? "The converted amount is: ".$exchange_amount : ""}}</small> <br>
                                            </div> 
                                        @endif
                                    @endif
                                @endif
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="rate_amount">Rate<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="rate_amount" placeholder="Enter Rate" required/>
                                    @error('rate_amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="deposit_amount">Deposit</label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="deposit_amount" placeholder="Enter Deposit" />
                                    @error('deposit_amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="pickup_at">Pickup At<span class="required" style="color: red">*</span></label>
                                <input type="datetime-local" class="form-control" wire:model.debounce.300ms="pickup_at" placeholder="Enter Pickup Time"  required/>
                                @error('pickup_at') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="due_back_at">Due Back At<span class="required" style="color: red">*</span></label>
                                <input type="datetime-local" class="form-control" wire:model.debounce.300ms="due_back_at" placeholder="Enter Due Back Time"  required/>
                                @error('due_back_at') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="days">Total Days<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="days" placeholder="Total Rental Days"  required/>
                                @error('days') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="returned_at">Returned At</label>
                                <input type="datetime-local" class="form-control" wire:model.debounce.300ms="returned_at" placeholder="Enter Returned Time"  />
                                @error('returned_at') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Pickup Mileage</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="pickup_odometer" placeholder="Enter Pickup Mileage"  />
                                @error('pickup_odometer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="street_address">Return Mileage</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="return_odometer" placeholder="Enter Return Mileage" />
                                @error('return_odometer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Pickup Fuel Level</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="pickup_fuel_level" placeholder="Enter Pickup Fuel Level"  />
                                @error('pickup_fuel_level') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="street_address">Return Fuel Level</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="return_fuel_level" placeholder="Enter Return Fuel Level" />
                                @error('return_fuel_level') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Notes</label>
                                <textarea class="form-control" wire:model.debounce.300ms="notes" cols="30" rows="3" placeholder="Enter Rental Notes"></textarea>
                                @error('notes') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">Status<span class="required" style="color: red">*</span></label>
                                <select  wire:model.debounce.300ms="status" class="form-control" required>
                                    <option value="">Select Option</option>
                                    <option value="Reserved">Reserved</option>
                                    <option value="Active">Active</option>
                                    <option value="Closed">Closed</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                                @error('status') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="rentalEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog  mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Edit Rental <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="exampleInputEmail13"><a href="{{ route('transporters.index') }}" target="_blank" style="color: blue">Transporter(s)<span class="required" style="color: red">*</span></a></label>
                                <select wire:model.debounce.300ms="selectedTransporter" class="form-control" required   >
                                    <option value="">Select Transporter</option>
                                    @foreach ($transporters as $transporter)
                                    <option value="{{$transporter->id}}">{{$transporter->name}}</option>
                                    @endforeach
                                </select>
                                @error('selectedTransporter') <span class="text-danger error">{{ $message }}</span>@enderror
                                <small>  <a href="{{ route('transporters.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Transporter</a></small> <a href="#" wire:click.prevent="refresh('transporters')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                            </div>
                        </div>
                       
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="horse"><a href="{{ route('vehicles.index') }}" target="_blank" style="color: blue">Vehicle(s)<span class="required" style="color: red">*</span></a></label>
                                <input type="checkbox" wire:model.debounce.300ms="all_vehicles"   class="line-style" />
                                <label for="one" class="radio-label">Select from all vehicles</label>
                                <input type="text" wire:model.debounce.300ms="searchVehicle" placeholder="Search with reg..." class="form-control" required>
                                <select class="form-control" wire:model.debounce.300ms="selectedVehicle"  size="4">
                                    <option value="">Select Vehicle </option>
                                    @if (!is_null($selectedTransporter))
                                    @foreach ($vehicles as $vehicle)
                                    <option value="{{$vehicle->id}}"> {{$vehicle->registration_number}} {{$vehicle->vehicle_make ? $vehicle->vehicle_make->name : ""}} {{$vehicle->vehicle_model ? $vehicle->vehicle_model->name : ""}}</option>
                                    @endforeach
                                    @endif

                                </select>
                                @error('selectedVehicle') <span class="text-danger error">{{ $message }}</span>@enderror
                                <small>  <a href="{{ route('vehicles.create') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Vehicle</a></small> <a href="#" wire:click.prevent="refresh('vehicles')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vat"><a href="{{route('customers.index')}}">Customers<span class="required" style="color: red">*</span></a></label>
                                <select class="form-control" wire:model.debounce.300ms="selectedCustomer" required>
                                    <option value="">Select Customers</option>
                                    @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->name }} </option>                                        
                                    @endforeach
                                </select>
                                <small><a href="#" data-toggle="modal" data-target="#customerModal"><i class="fa fa-plus-square-o"></i> New Customer</a></small> 
                                    <a href="#" wire:click.prevent="refresh('customers')" class="float-end" style="float: right"><i class="fa fa-refresh"></i></a>
                                @error('selectedCustomer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="driver"><a href="{{ route('drivers.index') }}" target="_blank" style="color: blue">Driver(s)</a></label> 
                                <select class="form-control" wire:model.debounce.300ms="driver_id">
                                    <option value="">Select Driver</option>
                                    @foreach ($drivers as $driver)
                                        <option value="{{$driver->id}}">{{$driver->employee->name}} {{$driver->employee->surname}}</option>
                                    @endforeach
                                </select>
                                @error('driver_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                <small>  <a href="{{ route('drivers.create') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Driver</a></small> <a href="#" wire:click.prevent="refresh('drivers')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="mb-10">
                        <input type="checkbox" wire:model.debounce.300ms="transporter_agreement"   class="line-style" />
                        <label for="one" class="radio-label">Is vehicle sub contracted</label>
                        @error('transporter_agreement') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                    <div class="row">
                        @if ($transporter_agreement)
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="vat">Currencies<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="selectedCurrency" required >
                                        <option value="">Select Currency</option>
                                        @foreach ($currencies as $currency)
                                            <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>                                              
                                        @endforeach
                                    </select>
                                    @error('selectedCurrency') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                                @if (!is_null($selectedCurrency))
                                    @if ($company)
                                        @if ($selectedCurrency != $company->currency_id)
                                            <div class="form-group">
                                                <label for="customer">Conversion Rate<span class="required" style="color: red">*</span></label>
                                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate {{$selected_currency ? "From ".$selected_currency->name : ""}} {{$company->currency ? "To ".$company->currency->name : ""}}" required>
                                                @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                                <small style="color: green">{{$selected_currency ? " 1 ".$selected_currency->name." is how much in" : ""}} {{$company->currency ? $company->currency->name." ?" : ""}}</small>
                                                <small>{{$exchange_amount ? "The converted amount is: ".$exchange_amount : ""}}</small> <br>
                                            </div> 
                                        @endif
                                    @endif
                                @endif
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="rate_amount">Rate<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="rate_amount" placeholder="Enter Rate" required/>
                                    @error('rate_amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="transporter_rate_amount">Transporter Rate Amount</label>
                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="transporter_rate_amount" placeholder="Transporter Rate Amount">
                                    @error('transporter_rate_amount') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="deposit_amount">Deposit</label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="deposit_amount" placeholder="Enter Deposit" />
                                    @error('deposit_amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @else
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="vat">Currencies<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="selectedCurrency" required >
                                        <option value="">Select Currency</option>
                                        @foreach ($currencies as $currency)
                                            <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>                                              
                                        @endforeach
                                    </select>
                                    @error('selectedCurrency') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                                @if (!is_null($selectedCurrency))
                                    @if ($company)
                                        @if ($selectedCurrency != $company->currency_id)
                                            <div class="form-group">
                                                <label for="customer">Conversion Rate<span class="required" style="color: red">*</span></label>
                                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate {{$selected_currency ? "From ".$selected_currency->name : ""}} {{$company->currency ? "To ".$company->currency->name : ""}}" required>
                                                @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                                <small style="color: green">{{$selected_currency ? " 1 ".$selected_currency->name." is how much in" : ""}} {{$company->currency ? $company->currency->name." ?" : ""}}</small>
                                                <small>{{$exchange_amount ? "The converted amount is: ".$exchange_amount : ""}}</small> <br>
                                            </div> 
                                        @endif
                                    @endif
                                @endif
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="rate_amount">Rate<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="rate_amount" placeholder="Enter Rate" required/>
                                    @error('rate_amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="deposit_amount">Deposit</label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="deposit_amount" placeholder="Enter Deposit" />
                                    @error('deposit_amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="pickup_at">Pickup At<span class="required" style="color: red">*</span></label>
                                <input type="datetime-local" class="form-control" wire:model.debounce.300ms="pickup_at" placeholder="Enter Pickup Time"  required/>
                                @error('pickup_at') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="due_back_at">Due Back At<span class="required" style="color: red">*</span></label>
                                <input type="datetime-local" class="form-control" wire:model.debounce.300ms="due_back_at" placeholder="Enter Due Back Time"  required/>
                                @error('due_back_at') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="days">Total Days<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="days" placeholder="Total Rental Days"  required/>
                                @error('days') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="returned_at">Returned At</label>
                                <input type="datetime-local" class="form-control" wire:model.debounce.300ms="returned_at" placeholder="Enter Returned Time"  />
                                @error('returned_at') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Pickup Mileage</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="pickup_odometer" placeholder="Enter Pickup Mileage"  />
                                @error('pickup_odometer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="street_address">Return Mileage</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="return_odometer" placeholder="Enter Return Mileage" />
                                @error('return_odometer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Pickup Fuel Level</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="pickup_fuel_level" placeholder="Enter Pickup Fuel Level"  />
                                @error('pickup_fuel_level') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="street_address">Return Fuel Level</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="return_fuel_level" placeholder="Enter Return Fuel Level" />
                                @error('return_fuel_level') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Notes</label>
                                <textarea class="form-control" wire:model.debounce.300ms="notes" cols="30" rows="3" placeholder="Enter Rental Notes"></textarea>
                                @error('notes') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">Status<span class="required" style="color: red">*</span></label>
                                <select  wire:model.debounce.300ms="status" class="form-control" required>
                                    <option value="">Select Option</option>
                                    <option value="Reserved">Reserved</option>
                                    <option value="Active">Active</option>
                                    <option value="Closed">Closed</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                                @error('status') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

