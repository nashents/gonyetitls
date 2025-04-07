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
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="allocationsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Allocation#
                                    </th>
                                    <th class="th-sm">Type
                                    </th>
                                    <th class="th-sm">Vehicle
                                    </th>
                                    <th class="th-sm">Station
                                    </th>
                                    <th class="th-sm">Fuel Type
                                    </th>
                                    <th class="th-sm">Quantity
                                    </th>
                                    <th class="th-sm">Rate
                                    </th>
                                    <th class="th-sm">Total
                                    </th>
                                    <th class="th-sm">Balance
                                    </th>
                                    <th class="th-sm">Expires
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    @php
                                    $roles = Auth::user()->roles;
                                    foreach ($roles as $role) {
                                      $names[] = $role->name;
                                    }
                                    @endphp
                                     @if (in_array("Admin", $names) || in_array("Super Admin", $names))
                                    {{-- <th class="th-sm">Action
                                    </th> --}}
                                    @endif
                                  </tr>
                                </thead>
                                @if ($allocations->count()>0)
                                <tbody>
                                    @foreach ($allocations as $allocation)
                                  <tr>
                                    <td>{{$allocation->allocation_number}}</td>
                                    <td>{{$allocation->allocation_type}}</td>
                                    <td>{{$allocation->vehicle->make}} {{$allocation->vehicle->model}} ({{$allocation->vehicle->registration_number}})</td>
                                    <td>{{$allocation->container->name}}</td>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="allocationEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-building-o"></i> Edit Allocation <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >

                    <div class="modal-body">
                        <div class="form-group">
                            <label for="allocation_type">Allocation Type</label>
                           <select wire:model.debounce.300ms="allocation_type" class="form-control" required>
                               <option value="">Select Allocation Type</option>
                                <option value="Directors">Directors</option>
                                <option value="Management">Management</option>
                                <option value="Employee">Employee</option>
                           </select>
                            @error('allocation_type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="employee_id">Employee(s)</label>
                           <select wire:model.debounce.300ms="employee_id" class="form-control" >
                               <option value="">Select Employee</option>
                               @foreach ($employees as $employee)
                                <option value="{{$employee->id}}">{{$employee->name}} {{$employee->surname}} ({{$employee->employee_number}})</option>
                               @endforeach
                           </select>
                            @error('employee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="vehicle_id">Vehicle(s)</label>
                           <select wire:model.debounce.300ms="vehicle_id" class="form-control" required>
                               <option value="">Select Vehicle</option>
                               @foreach ($vehicles as $vehicle)
                                <option value="{{$vehicle->id}}">{{$vehicle->make}} {{$vehicle->model}} ({{$vehicle->registration_number}})</option>
                               @endforeach
                           </select>
                            @error('vehicle_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="container_id">Container(s)</label>
                           <select wire:model.debounce.300ms="container_id" class="form-control" required>
                               <option value="">Select Vehicle</option>
                               @foreach ($containers as $container)
                                <option value="{{$container->id}}">{{$container->name}} </option>
                               @endforeach
                           </select>
                            @error('container_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="fuel_type">Fuel Type</label>
                           <select wire:model.debounce.300ms="fuel_type" class="form-control" required>
                               <option value="">Select Fuel Type</option>
                                <option value="Petrol">Petrol</option>
                                <option value="Diesel">Diesel</option>
                           </select>
                            @error('fuel_type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="quantity">Quantity </label>
                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="quantity" placeholder="Enter Quantity" required>
                            @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="rate">Rate</label>
                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="rate" placeholder="Enter Rate" required>
                            @error('rate') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="amount">Amount</label>
                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="amount" disabled required>
                            @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="balance">Balance</label>
                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="balance" disabled required>
                            @error('balance') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

