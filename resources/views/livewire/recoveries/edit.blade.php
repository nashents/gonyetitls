<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Edit Recovery</h5>
                            </div>
                        </div>
                        <div class="panel-body">
                        <form wire:submit.prevent="update()" >
                            <div class="modal-body">
                          
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="name">Date<span class="required" style="color: red">*</span></label>
                                            <input type="datetime-local" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Date" required>
                                            @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="country">Drivers<span class="required" style="color: red">*</span></label>
                                           <select wire:model.debounce.300ms="selectedDriver" class="form-control" required >
                                               <option value="">Select Driver</option>
                                             @foreach ($drivers as $driver) 
                                                <option value="{{$driver->id}}">{{$driver->employee ? $driver->employee->name : ""}} {{$driver->employee ? $driver->employee->surname : ""}} | {{ $driver->driver_number }} </option>
                                             @endforeach
                                           </select>
                                            @error('selectedDriver') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                             
                                </div>
                                <div class="row">
                                  
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label for="vat">Trips<span class="required" style="color: red">*</span></label>
                                           <select class="form-control" wire:model.debounce.300ms="trip_id" required>
                                            <option value="">Select Trip</option>
                                            @foreach ($trips as $trip)
                                                    <option value="{{ $trip->id }}">{{$trip->trip_number}} | Driver: {{ $trip->driver->employee ? $trip->driver->employee->name : "" }} {{ $trip->driver->employee ? $trip->driver->employee->surname : "" }} | Customer: {{$trip->customer ? $trip->customer->name : ""}} | <strong>From:</strong> {{$trip->loading_point ? $trip->loading_point->name : "undefined"}} <strong>To:</strong> {{$trip->offloading_point ? $trip->offloading_point->name : ""}}</option>                                        
                                            @endforeach
                                           </select>
                                            @error('trip_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="vat">Location</label>
                                               <select class="form-control" wire:model.debounce.300ms="destination_id" >
                                                <option value="">Select Destination</option>
                                                @foreach ($destinations as $destination)
                                                        <option value="{{ $destination->id }}">{{ $destination->country ? $destination->country->name : "" }} {{ $destination->city }} </option>                                        
                                                @endforeach
                                               </select>
                                               <small>  <a href="{{ route('destinations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Location</a></small> 
                                                @error('destination_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                            </div>
    
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="vat">Deductions<span class="required" style="color: red">*</span></label>
                                           <select class="form-control" wire:model.debounce.300ms="deduction_id" required>
                                            <option value="">Select Deduction</option>
                                            @foreach ($deductions as $deduction)
                                                    <option value="{{ $deduction->id }}">{{ $deduction->name }} </option>                                        
                                            @endforeach
                                           </select>
                                           <small>  <a href="{{ route('deductions.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Deduction</a></small> 
                                            @error('deduction_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                        </div>
    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="vat">Type<span class="required" style="color: red">*</span></label>
                                           <select class="form-control" wire:model.debounce.300ms="type" required>
                                            <option value="">Select Type</option>
                                            <option value="Gain">Gain</option>
                                            <option value="Loss">Loss</option>
                                           </select>
                                            @error('type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                        </div>
                                       
                                   
                               
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="footer">Weight <small>(Tons)</small></label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="weight" placeholder="Enter Loss/Gain Weight" >
                                            @error('weight') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                        </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="footer">Litreage <small>(Litres)</small> </label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="litreage" placeholder="Enter Loss/Gain Litreage" >
                                            @error('litreage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                        </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="footer">Quantity</label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="quantity" placeholder="Enter Loss/Gain Quantity" >
                                            @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="vat">Measurement</label>
                                           <select class="form-control" wire:model.debounce.300ms="measurement">
                                            <option value="">Select Quantity Measurement</option>
                                            @foreach ($measurements as $measurement)
                                                    <option value="{{ $measurement->name }}">{{ $measurement->name }} </option>                                        
                                            @endforeach
                                           </select>
                                           <small>  <a href="{{ route('measurements.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Measurement</a></small> 
                                            @error('measurement') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="vat">Currencies<span class="required" style="color: red">*</span></label>
                                           <select class="form-control" wire:model.debounce.300ms="currency_id" required>
                                            <option value="">Select Currency</option>
                                            @foreach ($currencies as $currency)
                                                    <option value="{{ $currency->id }}">{{ $currency->name }} </option>                                        
                                            @endforeach
                                           </select>
                                            @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                        @if (!is_null($currency_id))
                                        @if (Auth::user()->employee->company)
                                            @if ($currency_id != Auth::user()->employee->company->currency_id)
                                                <div class="form-group">
                                                    <label for="customer">Exchange Rate</label>
                                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate" placeholder="The exchange rate @ trip date">
                                                    @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div> 
                                            @endif
                                        @endif
                                    @endif  
                                        </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="footer">Rate</label>
                                            <input type="number" step="any"  min="0" class="form-control" wire:model.debounce.300ms="rate" placeholder="Enter Deduction Unit Price" >
                                            @error('rate') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                        </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="footer">Amount<span class="required" style="color: red">*</span></label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="amount" required placeholder="Enter Recovery Total Amount">
                                            @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                        </div>
                                 
                                </div>
                                <div class="form-group">
                                    <label for="description">Notes</label>
                                   <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="3" placeholder="Enter Recovery Notes"></textarea>
                                    @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                              
                            </div>

                      
                        <div class="modal-footer">
                            <div class="btn-group" role="group">
                                <button type="button" onclick="goBack()" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-arrow-left"></i>Back</button>
                                <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
                            </div>
                            <!-- /.btn-group -->
                        </div>
                    </form>

                </div>



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
