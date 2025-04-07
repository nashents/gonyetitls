<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Edit Fuel Order</h5>
                            </div>
                        </div>
                        <div class="panel-body">

                            <form wire:submit.prevent="update()" >
                                <div class="modal-body">
                                    <div class="row">
                                    <div class="form-group">
                                             <div class="col-sm-10">
                                                <label for="gender">Fuel Order For?<span class="required" style="color: red">*</span></label>
                                                <div class="radio">
                                                    <label>
                                                    <input type="radio" wire:model.debounce.300ms="type" id="optionsRadios1" value="Horse" required >
                                                    Horse
                                                    </label>
                                                </div>
                                                <div class="radio">
                                                    <label>
                                                    <input type="radio" wire:model.debounce.300ms="type" id="optionsRadios1" value="Vehicle" required >
                                                    Vehicle
                                                    </label>
                                                </div>
                                                <div class="radio">
                                                    <label>
                                                    <input type="radio"  wire:model.debounce.300ms="type" id="optionsRadios2" value="Asset" required>
                                                    Asset
                                                    </label>
                                                </div>
                                                <div class="radio">
                                                    <label>
                                                    <input type="radio"  wire:model.debounce.300ms="type" id="optionsRadios2" value="Other" required>
                                                    Other
                                                    </label>
                                                </div>
                                            </div>
                                            @error('type') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <hr>
                                    <br>
                                     @if (isset($type) && $type == "Horse")
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="vehicles">Horses<span class="required" style="color: red">*</span></label>
                                               <select wire:model.debounce.300ms="selectedHorse" class="form-control" required>
                                                   <option value="">Select Horse</option>
                                                  @foreach ($horses as $horse)
                                                      <option value="{{$horse->id}}">{{$horse->registration_number}} {{$horse->horse_make ? $horse->horse_make->name : ""}} {{$horse->horse_model ? $horse->horse_model->name : ""}}</option>
                                                  @endforeach
                                               </select>
                                                @error('selectedHorse') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="employees">Trips</label>
                                               <select wire:model.debounce.300ms="selectedTrip" class="form-control" >
                                                   <option value="">Select Trip</option>
                                                  @foreach ($trips as $trip)
                                                      <option value="{{$trip->id}}">{{ $trip->trip_number }} <strong>From:</strong> {{$trip->loading_point ? $trip->loading_point->name : ""}} <strong>To:</strong> {{$trip->offloading_point ? $trip->offloading_point->name : ""}}</option>
                                                  @endforeach
                                               </select>
                                                @error('selectedTrip') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="mileage">Mileage<span class="required" style="color: red">*</span></label>
                                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="mileage" placeholder="Enter Horse Mileage" required>
                                                @error('mileage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="vendors">Fueling Station<span class="required" style="color: red">*</span></label>
                                               <select wire:model.debounce.300ms="selectedContainer" class="form-control" required>
                                                   <option value="">Select Fueling Station</option>
                                                  @foreach ($containers as $container)
                                                      <option value="{{$container->id}}">{{$container->name}}</option>
                                                  @endforeach
                                               </select>
                                                @error('selectedContainer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                <small>  <a href="{{ route('containers.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Fueling Station</a></small> 
                                                @if (!is_null($selectedContainer))
                                                <br>
                                                <small style="color:green">Available fuel balance is {{ $container_balance }}Litres</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="date">Fill Up Date<span class="required" style="color: red">*</span></label>
                                                <input type="datetime-local" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter FillUp Date" required>
                                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <div class="col-sm-10">
                                                    <label for="gender">Type for fillup<span class="required" style="color: red">*</span></label>
                                                    <div class="radio">
                                                        <label>
                                                        <input type="radio" wire:model.debounce.300ms="fillup" id="optionsRadios1" value="1" required>
                                                        Initial
                                                        </label>
                                                    </div>
                                                    <div class="radio">
                                                        <label>
                                                        <input type="radio"  wire:model.debounce.300ms="fillup" id="optionsRadios2" value="0" required>
                                                        Top Up
                                                        </label>
                                                    </div>
                                                    </div>
                                                    @error('fillup') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="quantity">Quantity<span class="required" style="color: red">*</span></label>
                                                <input type="number" step="any"  max="{{$container_balance}}" class="form-control"  wire:model.debounce.300ms="quantity" placeholder="Enter Fillup Quantity" required />
                                                @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                               @if (isset($trip_fuel))
                                                @if ($trip_fuel > $horse_fuel_total)
                                                <small style="color: red">Total horse fuel is less than trip fuel.</small>
                                                @endif
                                               @endif
                                               @if (isset($container_balance))
                                                @if ($container_balance < $quantity)
                                                <small style="color: red">Fuel order exceeds {{ $container_balance }} litres, which is the fueling station balance.</small>
                                                @endif
                                               @endif
                                               @if ($fuel_tank_capacity > 0)
                                                @if ($fuel_tank_capacity < $quantity)
                                                <small style="color: red">Fuel order exceeds {{ $fuel_tank_capacity }} litres, which is horse fuel tank capacity.</small>
                                                @endif
                                                @else   
                                                @if ($selected_horse)
                                                <small style="color: green">Horse <a href="{{ route('horses.show',$selected_horse->id) }}" target="_blank" style="color: blue">Horse {{ $selected_horse->registration_number }}</a> fuel tank capacity not set.</small>
                                                @endif
                                               @endif
                                               
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="currencies">Currencies</label>
                                                <select class="form-control" wire:model.debounce.300ms="currency_id" >
                                                    <option value="">Select Currency </option>
                                                    @foreach ($currencies as $currency)
                                                        <option value="{{$currency->id}}">{{$currency->name}}</option>
                                                    @endforeach
                                                </select>
                                                @error('currency_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="unit_price">Rate</label>
                                                <input type="number" class="form-control" step="any" min="0"  wire:model.debounce.300ms="unit_price" placeholder="Enter Fuel Price" >
                                                @error('unit_price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="amount">Total</label>
                                                <input type="number" step="any" min="0" class="form-control"  wire:model.debounce.300ms="amount" placeholder="Enter Fillup Total">
                                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="file">Comments</label>
                                       <textarea wire:model.debounce.300ms="comments" class="form-control" cols="30" rows="4" placeholder="Write fuel order comments..."></textarea>
                                        @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                
                                    @elseif (isset($type) && $type == "Vehicle")
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="vehicles">Vehicles<span class="required" style="color: red">*</span></label>
                                               <select wire:model.debounce.300ms="selectedVehicle" class="form-control" required>
                                                   <option value="">Select Vehicle</option>
                                                  @foreach ($vehicles as $vehicle)
                                                      <option value="{{$vehicle->id}}"> {{$vehicle->registration_number}} {{$vehicle->vehicle_make ? $vehicle->vehicle_make->name : ""}} {{$vehicle->vehicle_model ? $vehicle->vehicle_model->name : ""}}</option>
                                                  @endforeach
                                               </select>
                                                @error('selectedVehicle') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="employees">Employees<span class="required" style="color: red">*</span></label>
                                               <select wire:model.debounce.300ms="employee_id" class="form-control" required>
                                                   <option value="">Select Employee</option>
                                                  @foreach ($employees as $employee)
                                                      <option value="{{$employee->id}}">{{ $employee->name }} {{$employee->surname}}</option>
                                                  @endforeach
                                               </select>
                                                @error('employee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="mileage">Mileage<span class="required" style="color: red">*</span></label>
                                                <input type="text" class="form-control" wire:model.debounce.300ms="mileage" required placeholder="Enter Vehicle Mileage">
                                                @error('mileage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="vendors">Fueling Station<span class="required" style="color: red">*</span></label>
                                               <select wire:model.debounce.300ms="selectedContainer" class="form-control" required>
                                                   <option value="">Select Fueling Station</option>
                                                  @foreach ($containers as $container)
                                                      <option value="{{$container->id}}">{{$container->name}}</option>
                                                  @endforeach
                                               </select>
                                                @error('selectedContainer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                <small>  <a href="{{ route('containers.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Fueling Station</a></small> 
                                                @if (!is_null($selectedContainer))
                                                <br>
                                                <small style="color:green">Available fuel balance is {{ $container_balance }}Litres</small>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                 
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="date">Fill Up Date<span class="required" style="color: red">*</span></label>
                                                <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter FillUp Date" required>
                                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                             <div class="col-sm-10">
                                                <label for="gender">Type for fillup<span class="required" style="color: red">*</span></label>
                                                <div class="radio">
                                                    <label>
                                                    <input type="radio" wire:model.debounce.300ms="fillup" id="optionsRadios1" value="1" required>
                                                    Initial
                                                    </label>
                                                </div>
                                                <div class="radio">
                                                    <label>
                                                    <input type="radio"  wire:model.debounce.300ms="fillup" id="optionsRadios2" value="0" required>
                                                    Top Up
                                                    </label>
                                                </div>
                                            </div>
                                            @error('fillup') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="quantity">Quantity<span class="required" style="color: red">*</span></label>
                                                <input type="number" step="any" max="{{ $container_balance }}" class="form-control"  wire:model.debounce.300ms="quantity" placeholder="Enter Fillup Quantity" required >
                                                @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                @if ($quantity > $container_balance)
                                                <small style="color:red">Quantity entered is greater than station balance</small>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="currencies">Currencies</label>
                                                <select class="form-control" wire:model.debounce.300ms="currency_id" >
                                                    <option value="">Select Currency </option>
                                                    @foreach ($currencies as $currency)
                                                        <option value="{{$currency->id}}">{{$currency->name}}</option>
                                                    @endforeach
                                                </select>
                                                @error('currency_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="unit_price">Rate</label>
                                                <input type="number" class="form-control" step="any" min="0"  wire:model.debounce.300ms="unit_price" placeholder="Enter Fuel Price" >
                                                @error('unit_price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="amount">Total</label>
                                                <input type="number" step="any" min="0" class="form-control"  wire:model.debounce.300ms="amount" placeholder="Enter Fillup Total">
                                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>
                                  
                                    <div class="form-group">
                                        <label for="file">Comments</label>
                                       <textarea wire:model.debounce.300ms="comments" class="form-control" cols="30" rows="4" placeholder="Write fuel order comments..."></textarea>
                                        @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                
                                @elseif (isset($type) && $type == "Asset")
                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="drivers">Categories<span class="required" style="color: red">*</span></label>
                                           <select wire:model.debounce.300ms="selectedCategory" class="form-control" required >
                                               <option value="">Select Category</option>
                                              @foreach ($categories as $category)
                                                  <option value="{{$category->id}}">{{$category->name}} </option>
                                              @endforeach
                                           </select>
                                            @error('selectedCategory') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="drivers">Sub Categories<span class="required" style="color: red">*</span></label>
                                           <select wire:model.debounce.300ms="selectedCategoryValue" class="form-control" required >
                                               <option value="">Select Sub Category</option>
                                              @foreach ($category_values as $category_value)
                                                  <option value="{{$category_value->id}}">{{$category_value->name}} </option>
                                              @endforeach
                                           </select>
                                            @error('selectedCategoryValue') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
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
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="vendors">Fueling Station<span class="required" style="color: red">*</span></label>
                                           <select wire:model.debounce.300ms="selectedContainer" class="form-control" required>
                                               <option value="">Select Fueling Station</option>
                                              @foreach ($containers as $container)
                                                  <option value="{{$container->id}}">{{$container->name}}</option>
                                              @endforeach
                                           </select>
                                            @error('selectedContainer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            <small>  <a href="{{ route('containers.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Fueling Station</a></small> 
                                            @if (!is_null($selectedContainer))
                                                <br>
                                                <small style="color:green">Available fuel balance is {{ $container_balance }}Litres</small>
                                                @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="date">Fill Up Date<span class="required" style="color: red">*</span></label>
                                            <input type="datetime-local" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter FillUp Date" required>
                                            @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                             <div class="col-sm-10">
                                                <label for="gender">Type for fillup<span class="required" style="color: red">*</span></label>
                                                <div class="radio">
                                                    <label>
                                                    <input type="radio" wire:model.debounce.300ms="fillup" id="optionsRadios1" value="1" required>
                                                    Initial
                                                    </label>
                                                </div>
                                                <div class="radio">
                                                    <label>
                                                    <input type="radio"  wire:model.debounce.300ms="fillup" id="optionsRadios2" value="0" required>
                                                    Top Up
                                                    </label>
                                                </div>
                                            </div>
                                            @error('fillup') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="quantity">Quantity<span class="required" style="color: red">*</span></label>
                                            <input type="number" step="any" max="{{ $container_balance }}" class="form-control"  wire:model.debounce.300ms="quantity" placeholder="Enter Fillup Quantity" required >
                                            @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            @if ($quantity > $container_balance)
                                            <small style="color:red">Quantity entered is greater than station balance</small>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="currencies">Currencies</label>
                                            <select class="form-control" wire:model.debounce.300ms="currency_id" >
                                                <option value="">Select Currency </option>
                                                @foreach ($currencies as $currency)
                                                    <option value="{{$currency->id}}">{{$currency->name}}</option>
                                                @endforeach
                                            </select>
                                            @error('currency_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="unit_price">Rate</label>
                                            <input type="number" class="form-control" step="any" min="0"  wire:model.debounce.300ms="unit_price" placeholder="Enter Fuel Price" >
                                            @error('unit_price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="amount">Total</label>
                                            <input type="number" step="any" min="0" class="form-control"  wire:model.debounce.300ms="amount" placeholder="Enter Fillup Total">
                                            @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="file">Comments</label>
                                   <textarea wire:model.debounce.300ms="comments" class="form-control" cols="30" rows="4" placeholder="Write fuel order comments..."></textarea>
                                    @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                
                                @elseif (isset($type) && $type == "Other")
                                <div class="form-group">
                                    <label for="vendors">Fueling Station<span class="required" style="color: red">*</span></label>
                                   <select wire:model.debounce.300ms="selectedContainer" class="form-control" required>
                                       <option value="">Select Fueling Station</option>
                                      @foreach ($containers as $container)
                                          <option value="{{$container->id}}">{{$container->name}}</option>
                                      @endforeach
                                   </select>
                                    @error('selectedContainer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    <small>  <a href="{{ route('containers.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Fueling Station</a></small> 
                                    @if (!is_null($selectedContainer))
                                                <br>
                                                <small style="color:green">Available fuel balance is {{ $container_balance }}Litres</small>
                                                @endif
                                </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="date">Fill Up Date<span class="required" style="color: red">*</span></label>
                                                <input type="datetime-local" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter FillUp Date" required>
                                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                         <div class="col-md-6">
                                            <div class="form-group">
                                                <div class="col-sm-10">
                                                    <label for="gender">Type for fillup<span class="required" style="color: red">*</span></label>
                                                    <div class="radio">
                                                        <label>
                                                        <input type="radio" wire:model.debounce.300ms="fillup" id="optionsRadios1" value="1" required>
                                                        Initial
                                                        </label>
                                                    </div>
                                                    <div class="radio">
                                                        <label>
                                                        <input type="radio"  wire:model.debounce.300ms="fillup" id="optionsRadios2" value="0" required>
                                                        Top Up
                                                        </label>
                                                    </div>
                                                </div>
                                                @error('fillup') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="quantity">Quantity<span class="required" style="color: red">*</span></label>
                                                <input type="number" step="any" max="{{ $container_balance }}" class="form-control"  wire:model.debounce.300ms="quantity" placeholder="Enter Fillup Quantity" required >
                                                @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                @if ($quantity > $container_balance)
                                                <small style="color:red">Quantity entered is greater than station balance</small>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="currencies">Currencies</label>
                                                <select class="form-control" wire:model.debounce.300ms="currency_id" >
                                                    <option value="">Select Currency </option>
                                                    @foreach ($currencies as $currency)
                                                        <option value="{{$currency->id}}">{{$currency->name}}</option>
                                                    @endforeach
                                                </select>
                                                @error('currency_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="unit_price">Rate</label>
                                                <input type="number" class="form-control" step="any" min="0"  wire:model.debounce.300ms="unit_price" placeholder="Enter Fuel Price" >
                                                @error('unit_price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="amount">Total</label>
                                                <input type="number" step="any" min="0" class="form-control"  wire:model.debounce.300ms="amount" placeholder="Enter Fillup Total">
                                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>
                                        <div class="form-group">
                                            <label for="file">Comments<span class="required" style="color: red">*</span></label>
                                                <textarea wire:model.debounce.300ms="comments" class="form-control" cols="30" rows="4" required placeholder="Write fuel order comments..."></textarea>
                                            @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                @endif
                                </div>
                                <div class="modal-footer">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
                                    </div>
                                    <!-- /.btn-group -->
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


</div>
