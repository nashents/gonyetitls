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
                                <a href="" data-toggle="modal" data-target="#fuelModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Fuel Order</a>
                            </div>
                            <div class="panel-title" style="float: right">
                                <a href="{{route('fuels.export.excel')}}"  class="btn btn-default"><i class="fa fa-download"></i>Excel</a>
                                <a href="{{route('fuels.export.csv')}}"  class="btn btn-default"><i class="fa fa-download"></i>CSV</a>
                                <a href="{{route('fuels.export.pdf')}}"  class="btn btn-default"><i class="fa fa-download"></i>PDF</a>
                                {{-- <a href="" data-toggle="modal" data-target="#fuelsImportModal" class="btn btn-default"><i class="fa fa-upload"></i>Import</a> --}}
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="fuelsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Order Number
                                    </th>
                                    <th class="th-sm">Created By
                                    </th>
                                    <th class="th-sm">Fillup Date
                                    </th>
                                    <th class="th-sm">Trip
                                    </th>
                                    <th class="th-sm">Horse
                                    </th>
                                    <th class="th-sm">Driver
                                    </th>
                                    <th class="th-sm">Fuel Station
                                    </th>
                                    <th class="th-sm">Price/Litre
                                    </th>
                                    <th class="th-sm">Quantity
                                    </th>
                                    <th class="th-sm">Amount
                                    </th>
                                    <th class="th-sm">Odometer
                                    </th>
                                    <th class="th-sm">FillUp
                                    </th>
                                    <th class="th-sm">Comments
                                    </th>
                                    <th class="th-sm">Authorization
                                    </th>
                                    {{-- <th class="th-sm">Collection Status
                                    </th> --}}
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($fuels->count()>0)
                                <tbody>
                                    @foreach ($fuels as $fuel)
                                  <tr>
                                    <td>{{$fuel->order_number}}</td>
                                    <td>{{ucfirst($fuel->user ? $fuel->user->name : "undefined")}} {{ucfirst($fuel->user ? $fuel->user->surname : "")}}</td>
                                    <td>{{$fuel->date}}</td>
                                    <td>({{ucfirst($fuel->trip->trip_number)}}) <strong>From:</strong> {{ucfirst($fuel->trip->loading_point->name)}} <strong>To:</strong> {{ucfirst($fuel->trip->offloading_point->name)}}</td>
                                    <td>{{ucfirst($fuel->horse->horse_make ? $fuel->horse->horse_make->name : "undefined")}} {{ucfirst($fuel->horse->horse_model ? $fuel->horse->horse_model->name : "")}} ({{ucfirst($fuel->horse->registration_number)}})</td>
                                    <td>{{ucfirst($fuel->driver->employee->name)}} {{ucfirst($fuel->driver->employee->surname)}}</td>
                                    <td>{{ucfirst($fuel->container->name)}}</td>
                                    <td>{{$fuel->unit_price}}</td>
                                    <td>{{$fuel->quantity}}</td>
                                    <td>{{$fuel->amount}}</td>
                                    <td>{{$fuel->odometer}}</td>
                                    <td>{{$fuel->fillup == "1" ? "Initial" : "Top Up"}}</td>
                                    <td>{{$fuel->comments? $fuel->comments : "No comment recorded"}}</td>
                                    <td><span class="badge bg-{{($fuel->authorization == 'approved') ? 'success' : (($fuel->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($fuel->authorization == 'approved') ? 'approved' : (($fuel->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                @if ($fuel->authorization == "approved")
                                                <li><a href="{{route('fuels.preview',$fuel->id)}}"  ><i class="fa fa-eye color-default"></i>Preview</a></li>
                                                @endif
                                                <li><a href="#"  wire:click="edit({{$fuel->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#fuelDeleteModal{{ $fuel->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('fuels.delete')
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
        <!-- /.container-fluid -->
    </section>
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="fuelModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-gas-pump"></i> Add Fuel Order <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="trips">Trips<span class="required" style="color: red">*</span></label>
                        <select wire:model.debounce.300ms="selectedTrip" class="form-control" required >
                            <option value="">Select Trip</option>
                            @foreach ($trips as $trip)
                            @if ($trip->fuel)
                            <option value="{{$trip->id}}" disabled style="background-color: red"> {{$trip->trip_number}} <strong>From:</strong> {{$trip->loading_point->name}} <strong>To:</strong> {{$trip->offloading_point->name}}  </option>
                            @else
                            <option value="{{$trip->id}}"> {{$trip->trip_number}} <strong>From:</strong> {{$trip->loading_point->name}} <strong>To:</strong> {{$trip->offloading_point->name}}  </option>
                            @endif

                            @endforeach
                        </select>
                        @error('selectedTrip') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="horses">Horses<span class="required" style="color: red">*</span></label>
                       <select wire:model.debounce.300ms="horse_id" class="form-control" required disabled>
                           <option value="">Select Horse</option>
                          @foreach ($horses as $horse)
                              <option value="{{$horse->id}}"> {{$horse->horse_make ? $horse->horse_make->name : "undefined"}} {{$horse->horse_model ? $horse->horse_model->name : "undefined"}} ({{$horse->registration_number}})</option>
                          @endforeach
                       </select>
                        @error('horse_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="drivers">Drivers<span class="required" style="color: red">*</span></label>
                       <select wire:model.debounce.300ms="driver_id" class="form-control" required disabled>
                           <option value="">Select Driver</option>
                          @foreach ($drivers as $driver)
                              <option value="{{$driver->id}}">{{$driver->employee ? $driver->employee->name : ""}} {{$driver->employee ? $driver->employee->surname : ""}}</option>
                          @endforeach
                       </select>
                        @error('driver_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="vendors">Fuel Supplier<span class="required" style="color: red">*</span></label>
                       <select wire:model.debounce.300ms="selectedContainer" class="form-control" required>
                           <option value="">Select Fuel Supplier</option>
                          @foreach ($containers as $container)
                              <option value="{{$container->id}}">{{$container->name}}</option>
                          @endforeach
                       </select>
                        @error('selectedContainer') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                        <label for="gender">Fill Up</label>
                             <div class="col-sm-10">
                                <div class="radio">
                                    <label>
                                    <input type="radio" wire:model.debounce.300ms="fillup" id="optionsRadios1" value="1" >
                                    Initial
                                    </label>
                                </div>
                                <div class="radio">
                                    <label>
                                    <input type="radio"  wire:model.debounce.300ms="fillup" id="optionsRadios2" value="0">
                                    Top Up
                                    </label>
                                </div>
                            </div>
                            @error('fillup') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
                        <br>
                        <br>
                        <br>
                    </div>
                    </div>
                        <div class="row">
                            <div class="col-md-6">
                    <div class="form-group">
                        <label for="unit_price">Rate</label>
                        <input type="number" class="form-control" step="any" min="0"  wire:model.debounce.300ms="unit_price" placeholder="Enter Pump Price/Litre" disabled>
                        @error('unit_price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                            </div>
                            <div class="col-md-6">
                    <div class="form-group">
                        <label for="quantity">Quantity<span class="required" style="color: red">*</span></label>
                        <input type="number" step="any" class="form-control"  wire:model.debounce.300ms="quantity" placeholder="Enter Fillup Quantity" required disabled>
                        @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                            </div>
                        </div>
                    <div class="form-group">
                        <label for="amount">Total Amount</label>
                        <input type="number" class="form-control" required="required" wire:model.debounce.300ms="amount" disabled>
                        @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="form-group">
                        <label for="odometer">Odometer<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="odometer" required>
                        @error('odometer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label for="invoice_number">Invoice Number</label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="invoice_number" >
                        @error('invoice_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="file">Invoice</label>
                        <input type="file" class="form-control" wire:model.debounce.300ms="file" >
                        @error('file') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>

                    </div>
                    <div class="form-group">
                        <label for="file">Comments</label>
                       <textarea wire:model.debounce.300ms="comments" class="form-control" cols="30" rows="4"></textarea>
                        @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="fuelEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-gas-pump"></i> Edit Fuel Order <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >

               <div class="modal-body">
                <div class="form-group">
                    <label for="trips">Trips<span class="required" style="color: red">*</span></label>
                    <select wire:model.debounce.300ms="selectedTrip" class="form-control" required >
                        <option value="">Select Trip</option>
                        @foreach ($trips as $trip)
                        @if ($trip->fuel)
                        <option value="{{$trip->id}}" disabled style="background-color: red"> {{$trip->trip_number}} <strong>From:</strong> {{$trip->loading_point->name}} <strong>To:</strong> {{$trip->offloading_point->name}}  </option>
                        @else
                        <option value="{{$trip->id}}"> {{$trip->trip_number}} <strong>From:</strong> {{$trip->loading_point->name}} <strong>To:</strong> {{$trip->offloading_point->name}}  </option>
                        @endif
                        @endforeach
                    </select>
                    @error('selectedTrip') <span class="error" style="color:red">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="horses">Horses<span class="required" style="color: red">*</span></label>
                   <select wire:model.debounce.300ms="horse_id" class="form-control" required>
                       <option value="">Select Horse</option>
                      @foreach ($horses as $horse)
                          <option value="{{$horse->id}}"> {{$horse->horse_make ? $horse->horse_make->name : "undefined"}} {{$horse->horse_model ? $horse->horse_model->name : ""}} ({{$horse->registration_number}})</option>
                      @endforeach
                   </select>
                    @error('horse_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label for="drivers">Drivers<span class="required" style="color: red">*</span></label>
                   <select wire:model.debounce.300ms="driver_id" class="form-control" required >
                       <option value="">Select Driver</option>
                      @foreach ($drivers as $driver)
                          <option value="{{$driver->id}}">{{$driver->employee ? $driver->employee->name : ""}} {{$driver->employee ? $driver->employee->surname : ""}}</option>
                      @endforeach
                   </select>
                    @error('driver_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="vendors">Fuel Supplier<span class="required" style="color: red">*</span></label>
                   <select wire:model.debounce.300ms="selectedContainer" class="form-control" required >
                       <option value="">Select Supplier</option>
                      @foreach ($containers as $container)
                          <option value="{{$container->id}}">{{$container->name}}</option>
                      @endforeach
                   </select>
                    @error('selectedContainer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                </div>
                <div class="row">
                    <div class="col-md-6">

                <div class="form-group">
                    <label for="date">Fill Up Date</label>
                    <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter FillUp Date" required>
                    @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="gender">Partial Fill Up</label>
                         <div class="col-sm-10">
                            <div class="radio">
                                <label>
                                <input type="radio" wire:model.debounce.300ms="fillup" id="optionsRadios1" value="1" >
                                Yes
                                </label>
                            </div>
                            <div class="radio">
                                <label>
                                <input type="radio"  wire:model.debounce.300ms="fillup" id="optionsRadios2" value="0">
                                No
                                </label>
                            </div>
                        </div>
                        @error('fillup') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                    <br>
                    <br>
                    <br>
                </div>
                </div>
                    <div class="row">
                        <div class="col-md-6">
                <div class="form-group">
                    <label for="unit_price">Unit Price</label>
                    <input type="number" class="form-control" step="any" min="0" required="required" wire:model.debounce.300ms="unit_price" placeholder="Enter Pump Price/Litre"  disabled>
                    @error('unit_price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                </div>
                        </div>
                        <div class="col-md-6">
                <div class="form-group">
                    <label for="quantity">Quantity<span class="required" style="color: red">*</span></label>
                    <input type="number" class="form-control" step="any" required="required" wire:model.debounce.300ms="quantity" placeholder="Enter Fillup Quantity" required disabled >
                    @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                </div>
                        </div>
                    </div>
                <div class="form-group">
                    <label for="amount">Total Amount</label>
                    <input type="number" class="form-control" required="required" wire:model.debounce.300ms="amount" disabled>
                    @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label for="odometer">Odometer<span class="required" style="color: red">*</span></label>
                    <input type="text" class="form-control" wire:model.debounce.300ms="odometer" required >
                    @error('odometer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                </div>

                <div class="row">
                <div class="col-md-6">
                <div class="form-group">
                    <label for="invoice_number">Invoice Number</label>
                    <input type="text" class="form-control" wire:model.debounce.300ms="invoice_number" >
                    @error('invoice_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="file">Invoice</label>
                    <input type="file" class="form-control" wire:model.debounce.300ms="file" >
                    @error('file') <span class="error" style="color:red">{{ $message }}</span> @enderror
                </div>
            </div>
                </div>
                <div class="form-group">
                    <label for="comments">Comments</label>
                  <textarea class="form-control" cols="30" rows="5" wire:model.debounce="comments"></textarea>
                    @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

