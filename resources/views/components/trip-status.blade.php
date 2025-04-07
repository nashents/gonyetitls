<div>
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i>Update Trip {{ isset($trip) ? $trip->trip_number : "" }} Status <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Trip Statuses<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="selectedStatus" required>
                                        <option value="">Select Status</option>
                                        <option value="Scheduled">Scheduled</option>
                                        <option value="Started">Started</option>
                                        <option value="Loading Point">Loading Point</option>
                                        <option value="Loaded">Loaded</option>
                                        <option value="InTransit">InTransit</option>
                                        <option value="Offloading Point">Offloading Point</option>
                                        <option value="Offloaded">Offloaded</option>
                                        <option value="OnHold">OnHold</option>
                                        <option value="Cancelled">Cancelled</option>
                                    </select>
                                    @error('selectedStatus') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Trip Status Date<span class="required" style="color: red">*</span></label>
                             <input type="datetime-local"  class="form-control" wire:model.debounce.300ms="trip_status_date" placeholder="Status Date" required>
                                @error('trip_status_date') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                       
                    </div>
                    <div class="form-group">
                        <label for="title">Trip Status Description</label>
                        <textarea  class="form-control" wire:model.debounce.300ms="trip_status_description" placeholder="Trip status additional notes.." cols="30" rows="4"></textarea>
                        @error('trip_status_description') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                
    
    
                    @if (isset($selectedDeliveryNote) && $selectedDeliveryNote === True)
    
                    @if ($selectedStatus == "Loaded")
                    @php
                    $measurements = App\Models\Measurement::latest()->get();
                     @endphp
    
                    <h5 class="underline mt-30">Freight Calculation Method</h5>
                    <div class="mb-10">
                        <input type="radio" wire:model.debounce.300ms="freight_calculation" value="flat_rate"  class="line-style" required disabled />
                        <label for="one" class="radio-label">Flat Rate</label>
                        <input type="radio" wire:model.debounce.300ms="freight_calculation" value="rate_weight"  class="line-style" required disabled />
                        <label for="one" class="radio-label">Rate * Weight/Litreage</label>
                        <input type="radio" wire:model.debounce.300ms="freight_calculation" value="rate_weight_distance"  class="line-style" required disabled />
                        <label for="one" class="radio-label">Rate * Distance * Weight/Litreage</label>
                        <input type="radio" wire:model.debounce.300ms="freight_calculation" value="rate_distance"  class="line-style" required disabled/>
                        <label for="one" class="radio-label">Rate * Distance</label>
                        @error('freight_calculation') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
    
                    <h5 class="underline mt-30">Loading Details</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Date<span class="required" style="color: red">*</span></label>
                                    <input type="datetime-local" class="form-control" wire:model.debounce.300ms="loaded_date" placeholder="Enter Loading Date" required >
                                    @error('loaded_date') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Distance</label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="distance" placeholder="Enter Trip Distance"  >
                                    @error('distance') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            @if ($cargo_type == "Solid")
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Weight<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="loaded_weight" placeholder="Enter Loading Weight" required >
                                    @error('loaded_weight') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            @else 
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Weight</label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="loaded_weight" placeholder="Enter Loading Weight" >
                                    @error('loaded_weight') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="row">
                                @if ($cargo_type == "Solid")
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="title">Quantity</label>
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="loaded_quantity" placeholder="Enter Loaded Quantity">
                                        @error('loaded_quantity') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="customer">Measurement</label>
                                      <select class="form-control" wire:model.debounce.300ms="measurement" disabled>
                                          <option value="">Select Measurement</option>
                                          @foreach ($measurements as $measurement)
                                          <option value="{{ $measurement->name }}">{{ $measurement->name }}</option>
                                         @endforeach
                                      </select>
                                        @error('measurement') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                @elseif ($cargo_type == "Liquid")
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="title">Litreage @ Ambient</label>
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="loaded_litreage" placeholder="Enter Loaded Litreage @ Ambient Temperature"  >
                                        @error('loaded_litreage') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="title">Litreage @ 20 Degrees<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="loaded_litreage_at_20" placeholder="Enter Loaded Litreage @ 20 Degrees" required >
                                        @error('loaded_litreage_at_20') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="customer">Measurement</label>
                                      <select class="form-control" wire:model.debounce.300ms="measurement" disabled>
                                          <option value="">Select Measurement</option>
                                          @foreach ($measurements as $measurement)
                                          <option value="{{ $measurement->name }}">{{ $measurement->name }}</option>
                                         @endforeach
                                      </select>
                                        @error('measurement') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                @endif
                               
                           
                           
                        </div>
                        @php
                        $employee_department = Auth::user()->employee->departments->first();
    
                        $departments = Auth::user()->employee->departments;
                        foreach($departments as $department){
                            $department_names[] = $department->name;
                        }
                        $roles = Auth::user()->roles;
                        foreach($roles as $role){
                            $role_names[] = $role->name;
                        }
                        $ranks = Auth::user()->employee->ranks;
                        foreach($ranks as $rank){
                            $rank_names[] = $rank->name;
                        }
                    @endphp
    
                    @if (Auth::user()->employee->company->rates_managed_by_finance == 1)
                        @if (in_array('Finance', $department_names) ||  in_array('Super Admin', $role_names))
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Currency<span class="required" style="color: red">*</span></label>
                                 <select class="form-control" wire:model.debounce.300ms="currency_id" disabled>
                                    <option value="">Select Currency</option>
                                    @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                    @endforeach
                                 </select>
                                    @error('currency_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Customer Rate<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any"  class="form-control" wire:model.debounce.300ms="loaded_rate" placeholder="Enter Loading Rate" required >
                                    @error('loaded_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Customer Freight<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="loaded_freight" placeholder="Enter Loading Freight" required >
                                    @error('loaded_freight') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        @if ($trip->transporter_agreement == TRUE)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Transporter Rate<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any"  class="form-control" wire:model.debounce.300ms="transporter_loaded_rate" placeholder="Enter Transporter Loading Rate" required >
                                    @error('transporter_loaded_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Transporter Freight<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="transporter_loaded_freight" placeholder="Enter Transporter Loading Freight" required >
                                    @error('transporter_loaded_freight') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        @endif
                        @endif
                        @else
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Currency<span class="required" style="color: red">*</span></label>
                                 <select class="form-control" wire:model.debounce.300ms="currency_id" disabled>
                                    <option value="">Select Currency</option>
                                    @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                    @endforeach
                                 </select>
                                    @error('currency_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Customer Rate<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any"  class="form-control" wire:model.debounce.300ms="loaded_rate" placeholder="Enter Loading Rate" required >
                                    @error('loaded_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Customer Freight<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="loaded_freight" placeholder="Enter Loading Freight" required >
                                    @error('loaded_freight') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        @if ($trip->transporter_agreement == TRUE)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Transporter Rate<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any"  class="form-control" wire:model.debounce.300ms="transporter_loaded_rate" placeholder="Enter Transporter Loading Rate" required >
                                    @error('transporter_loaded_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Transporter Freight<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="transporter_loaded_freight" placeholder="Enter Transporter Loading Freight" required >
                                    @error('transporter_loaded_freight') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        @endif
                        @endif
                    @elseif ($selectedStatus == "Offloaded")
                    @php
                    $measurements = App\Models\Measurement::orderBy('name','asc')->get();
                     @endphp
    
                    <h5 class="underline mt-30">Freight Calculation Method</h5>
                    <div class="mb-10">
                        <input type="radio" wire:model.debounce.300ms="freight_calculation" value="flat_rate"  class="line-style" required disabled />
                        <label for="one" class="radio-label">Flat Rate</label>
                        <input type="radio" wire:model.debounce.300ms="freight_calculation" value="rate_weight"  class="line-style" required disabled />
                        <label for="one" class="radio-label">Rate * Weight/Litreage</label>
                        <input type="radio" wire:model.debounce.300ms="freight_calculation" value="rate_weight_distance"  class="line-style" required disabled />
                        <label for="one" class="radio-label">Rate * Distance * Weight/Litreage</label>
                        <input type="radio" wire:model.debounce.300ms="freight_calculation" value="rate_distance"  class="line-style" required disabled/>
                        <label for="one" class="radio-label">Rate * Distance</label>
                        @error('freight_calculation') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
    
                    <h5 class="underline mt-30">Loading Details</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Date<span class="required" style="color: red">*</span></label>
                                    <input type="datetime-local" class="form-control" wire:model.debounce.300ms="loaded_date" placeholder="Enter Loading Date" required disabled>
                                    @error('loaded_date') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Distance</label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="distance" placeholder="Enter Trip Distance" disabled>
                                    @error('distance') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            @if ($cargo_type == "Solid")
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Weight<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="loaded_weight" placeholder="Enter Loading Weight" required disabled>
                                    @error('loaded_weight') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            @else 
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Weight</label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="loaded_weight" placeholder="Enter Loading Weight" disabled>
                                    @error('loaded_weight') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="row">
                                @if ($cargo_type == "Solid")
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="title">Quantity</label>
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="loaded_quantity" placeholder="Enter Loaded Quantity"  disabled>
                                        @error('loaded_quantity') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="customer">Measurement</label>
                                      <select class="form-control" wire:model.debounce.300ms="measurement"  disabled>
                                          <option value="">Select Measurement</option>
                                          @foreach ($measurements as $measurement)
                                          <option value="{{ $measurement->name }}">{{ $measurement->name }}</option>
                                         @endforeach
                                      </select>
                                        @error('measurement') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                @elseif ($cargo_type == "Liquid")
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="title">Litreage @ Ambient</label>
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="loaded_litreage" placeholder="Enter Loaded Litreage @ Ambient Temperature" disabled>
                                        @error('loaded_litreage') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="title">Litreage @ 20 Degrees<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="loaded_litreage_at_20" placeholder="Enter Loaded Litreage @ 20 Degrees" required disabled>
                                        @error('loaded_litreage_at_20') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="customer">Measurement</label>
                                      <select class="form-control" wire:model.debounce.300ms="measurement"  disabled>
                                          <option value="">Select Measurement</option>
                                          @foreach ($measurements as $measurement)
                                          <option value="{{ $measurement->name }}">{{ $measurement->name }}</option>
                                         @endforeach
                                      </select>
                                        @error('measurement') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                @endif
                               
                           
                           
                        </div>
                        @php
                        $employee_department = Auth::user()->employee->departments->first();
    
                        $departments = Auth::user()->employee->departments;
                        foreach($departments as $department){
                            $department_names[] = $department->name;
                        }
                        $roles = Auth::user()->roles;
                        foreach($roles as $role){
                            $role_names[] = $role->name;
                        }
                        $ranks = Auth::user()->employee->ranks;
                        foreach($ranks as $rank){
                            $rank_names[] = $rank->name;
                        }
                    @endphp
    
                    @if (Auth::user()->employee->company->rates_managed_by_finance == 1)
                        @if (in_array('Finance', $department_names) ||  in_array('Super Admin', $role_names))
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Currency<span class="required" style="color: red">*</span></label>
                                 <select class="form-control" wire:model.debounce.300ms="currency_id" disabled>
                                    <option value="">Select Currency</option>
                                    @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                    @endforeach
                                 </select>
                                    @error('currency_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Customer Rate<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any"  class="form-control" wire:model.debounce.300ms="loaded_rate" placeholder="Enter Loading Rate" required disabled>
                                    @error('loaded_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Customer Freight<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="loaded_freight" placeholder="Enter Loading Freight" required disabled>
                                    @error('loaded_freight') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        @if ($trip->transporter_agreement == TRUE)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Transporter Rate<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any"  class="form-control" wire:model.debounce.300ms="transporter_loaded_rate" placeholder="Enter Transporter Loading Rate" required disabled>
                                    @error('transporter_loaded_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Transporter Freight<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="transporter_loaded_freight" placeholder="Enter Transporter Loading Freight" required disabled>
                                    @error('transporter_loaded_freight') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        @endif
                        @endif
                        @else
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Currency<span class="required" style="color: red">*</span></label>
                                 <select class="form-control" wire:model.debounce.300ms="currency_id" disabled>
                                    <option value="">Select Currency</option>
                                    @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                    @endforeach
                                 </select>
                                    @error('currency_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Customer Rate<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any"  class="form-control" wire:model.debounce.300ms="loaded_rate" placeholder="Enter Loading Rate" required disabled>
                                    @error('loaded_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Customer Freight<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="loaded_freight" placeholder="Enter Loading Freight" required disabled>
                                    @error('loaded_freight') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        @if ($trip->transporter_agreement == TRUE)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Transporter Rate<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any"  class="form-control" wire:model.debounce.300ms="transporter_loaded_rate" placeholder="Enter Transporter Loading Rate" required disabled>
                                    @error('transporter_loaded_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Transporter Freight<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="transporter_loaded_freight" placeholder="Enter Transporter Loading Freight" required disabled>
                                    @error('transporter_loaded_freight') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        @endif
                        @endif
                       
                        <h5 class="underline mt-30">Offloading Details</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Date<span class="required" style="color: red">*</span></label>
                                    <input type="datetime-local" class="form-control" wire:model.debounce.300ms="offloaded_date" placeholder="Enter Offloading Date" required>
                                    @error('offloaded_date') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                @if (isset($starting_mileage))
                                <div class="form-group">
                                    <label for="title">Ending Mileage</label>
                                    <input type="number" step="any" min="{{$starting_mileage}}" class="form-control" wire:model.debounce.300ms="ending_mileage" placeholder="Enter Ending Mileage" >
                                    @error('ending_mileage') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                                @else
                                <div class="form-group">
                                    <label for="title">Ending Mileage</label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="ending_mileage" placeholder="Enter Ending Mileage" disabled>
                                    <small style="color:red">Please set starting mileage first</small>
                                    @error('ending_mileage') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                             
                                @endif
                               
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                @if ($freight_calculation == "rate_weight_distance" || $freight_calculation == "rate_distance")
                                    <div class="form-group">
                                        <label for="title">Distance<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="offloaded_distance" placeholder="Enter Trip Distance" required>
                                        @error('offloaded_distance') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                @else 
                                <div class="form-group">
                                    <label for="title">Distance</label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="offloaded_distance" placeholder="Enter Trip Distance">
                                    @error('offloaded_distance') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                                @endif
                              
                            </div>
                            @if ($cargo_type == "Solid")
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Weight<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="offloaded_weight" placeholder="Enter Offloaded Weight" required>
                                    @error('offloaded_weight') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            @else
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Weight</label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="offloaded_weight" placeholder="Enter Offloaded Weight">
                                    @error('offloaded_weight') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="row">
                                @if ($cargo_type == "Solid")
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="title">Quantity</label>
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="offloaded_quantity" placeholder="Enter Offloaded Quantity" >
                                        @error('offloaded_quantity') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                     </div>
                                     <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="customer">Measurement</label>
                                          <select class="form-control" wire:model.debounce.300ms="measurement" disabled >
                                              <option value="">Select Measurement</option>
                                              @foreach ($measurements as $measurement)
                                              <option value="{{ $measurement->name }}">{{ $measurement->name }}</option>
                                             @endforeach
                                          </select>
                                            @error('measurement') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                @elseif ($cargo_type == "Liquid")
                               
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="title">Litreage @ Ambient</label>
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="offloaded_litreage" placeholder="Enter Offloaded Litreage @ Ambient" >
                                        @error('offloaded_litreage') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="title">Litreage @ 20 Degrees<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="offloaded_litreage_at_20" placeholder="Enter Offloaded Litreage @ 20" required>
                                        @error('offloaded_litreage_at_20') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="customer">Measurement</label>
                                      <select class="form-control" wire:model.debounce.300ms="measurement" disabled>
                                          <option value="">Select Measurement</option>
                                          @foreach ($measurements as $measurement)
                                          <option value="{{ $measurement->name }}">{{ $measurement->name }}</option>
                                         @endforeach
                                      </select>
                                        @error('measurement') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                @endif
                           
                          
                           
                        </div>
                        @php
                        $employee_department = Auth::user()->employee->departments->first();
    
                        $departments = Auth::user()->employee->departments;
                        foreach($departments as $department){
                            $department_names[] = $department->name;
                        }
                        $roles = Auth::user()->roles;
                        foreach($roles as $role){
                            $role_names[] = $role->name;
                        }
                        $ranks = Auth::user()->employee->ranks;
                        foreach($ranks as $rank){
                            $rank_names[] = $rank->name;
                        }
                    @endphp
    
                    @if (Auth::user()->employee->company->rates_managed_by_finance == 1)
                        @if (in_array('Finance', $department_names) ||  in_array('Super Admin', $role_names))
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Currency<span class="required" style="color: red">*</span></label>
                                 <select class="form-control" wire:model.debounce.300ms="currency_id" disabled>
                                    <option value="">Select Currency</option>
                                    @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                    @endforeach
                                 </select>
                                    @error('currency_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Customer Rate<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any"  class="form-control" wire:model.debounce.300ms="offloaded_rate" placeholder="Offloading Rate" required >
                                    @error('offloaded_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Customer Freight<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="offloaded_freight" placeholder="Offloading Freight" required>
                                    @error('offloaded_freight') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        @if ($trip->transporter_agreement == TRUE)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Transporter Rate<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any"  class="form-control" wire:model.debounce.300ms="transporter_offloaded_rate" placeholder="Enter Transporter Offloading Rate" required>
                                    @error('transporter_offloaded_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Transporter Freight<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="transporter_offloaded_freight" placeholder="Enter Transporter Offloading Freight" required>
                                    @error('transporter_offloaded_freight') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        @endif
                        @endif
                        @else 
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Currency<span class="required" style="color: red">*</span></label>
                                 <select class="form-control" wire:model.debounce.300ms="currency_id" disabled>
                                    <option value="">Select Currency</option>
                                    @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                    @endforeach
                                 </select>
                                    @error('currency_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Customer Rate<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any"  class="form-control" wire:model.debounce.300ms="offloaded_rate" placeholder="Offloading Rate" required >
                                    @error('offloaded_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Customer Freight<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="offloaded_freight" placeholder="Offloading Freight" required>
                                    @error('offloaded_freight') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        @if ($trip->transporter_agreement == TRUE)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Transporter Rate<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any"  class="form-control" wire:model.debounce.300ms="transporter_offloaded_rate" placeholder="Enter Transporter Offloading Rate" required>
                                    @error('transporter_offloaded_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Transporter Freight<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="transporter_offloaded_freight" placeholder="Enter Transporter Offloading Freight" required>
                                    @error('transporter_offloaded_freight') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        @endif
                        @endif
                        <div class="form-group">
                            <label for="title">Comments</label>
                            <textarea  class="form-control" wire:model.debounce.300ms="comments" placeholder="Write down additional notes..." cols="30" rows="4"></textarea>
                            @error('comments') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
                    @endif
                   
    
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
            </div>
        </div>
    </div>
</div>