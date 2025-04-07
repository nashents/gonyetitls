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
                                <center>
                                    <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12" style="float: right">
                                        <a class="dashboard-stat-2 bg-primary" href="#">
                                            <div class="stat-content">
                                                <span class="name">{{$fuel_balance ? $fuel_balance  : "No fuel allocation found"}}</span>
                                            </div>
                                            <span class="stat-footer"><i class="fa fa-arrow-up color-success"></i> FUEL BALANCE</span>
                                        </a>
                                        <!-- /.dashboard-stat-2 -->
                                    </div>
                                </center>

                            </div>
                            <div class="panel-title" style="float: right">
                                {{-- <a href="{{route('fuel_requests.export.excel')}}"  class="btn btn-default"><i class="fa fa-download"></i>Excel</a>
                                <a href="{{route('fuel_requests.export.csv')}}"  class="btn btn-default"><i class="fa fa-download"></i>CSV</a>
                                <a href="{{route('fuel_requests.export.pdf')}}"  class="btn btn-default"><i class="fa fa-download"></i>PDF</a> --}}
                                {{-- <a href="" data-toggle="modal" data-target="#fuel_requestsImportModal" class="btn btn-default"><i class="fa fa-upload"></i>Import</a> --}}
                            </div>
                            <div class="panel-title">
                                <a href="" data-toggle="modal" data-target="#fuel_requestModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Fuel Request</a>

                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="fuel_requestsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>

                                    <th class="th-sm">Request#
                                    </th>
                                    <th class="th-sm">Allocation#
                                    </th>
                                    <th class="th-sm">Employee
                                    </th>
                                    <th class="th-sm">Fuel Type
                                    </th>
                                    <th class="th-sm">Quantity
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($fuel_requests->count()>0)
                                <tbody>
                                    @foreach ($fuel_requests as $fuel_request)
                                  <tr>
                                    <td>{{ucfirst($fuel_request->request_number)}}</td>
                                    <td>{{ucfirst($fuel_request->allocation ? $fuel_request->allocation->allocation_number : "")}}</td>
                                    <td>{{ucfirst($fuel_request->employee->name)}} {{ucfirst($fuel_request->employee->surname)}}</td>
                                    <td>{{$fuel_request->fuel_type}}</td>
                                    <td>{{$fuel_request->quantity ? $fuel_request->quantity."Litres" : ""}}</td>
                                    @if ($fuel_request->status == "pending")
                                    <td><span class="label label-warning label-rounded">{{$fuel_request->status}}</span></td>
                                    @elseif($fuel_request->status == "approved")
                                    <td><span class="label label-success label-rounded">{{$fuel_request->status}}</span></td>
                                    @elseif($fuel_request->status == "rejected")
                                    <td><span class="label label-danger label-rounded">{{$fuel_request->status}}</span></td>
                                    @endif

                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                @if ($fuel_request->status == "approved" | $fuel_request->status == "rejected")
                                                @else
                                                <li><a href="#"  wire:click="edit({{$fuel_request->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                @endif
                                                <li><a href="#" data-toggle="modal" data-target="#fuel_requestDeleteModal{{ $fuel_request->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('fuel_requests.delete')
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="fuel_requestModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Fuel Request <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="request_type">Allocations<span class="required" style="color: red">*</span></label>
                       <select wire:model.debounce.300ms="selectedAllocation" class="form-control" required>
                           <option value="">Select Allocation</option>
                           @foreach ($allocations as $allocation)
                           <option value="{{ $allocation->id }}">{{ $allocation->allocation_number }} {{ $allocation->fuel_type }} {{ $allocation->quantity }}L  Expires On: {{ $allocation->expiry_date }}</option>
                           @endforeach
                       </select>
                        @error('selectedAllocation') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fuel_type">Fuel Type<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="fuel_type" class="form-control" required disabled>
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
                                @if (isset($fuel_balance))
                                <input type="number" step="any" min="0" max="{{$fuel_balance}}"  class="form-control" wire:model.debounce.300ms="quantity" placeholder="Enter quantity below or equal to {{$fuel_balance}} Litres" required >  
                                @else
                                <input type="number" step="any" min="0"   class="form-control" wire:model.debounce.300ms="quantity" placeholder="Enter Quantity" required >  
                                @endif
                               
                                @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="fuel_requestEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Fuel Request <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >

                <div class="modal-body">
                    <div class="form-group">
                        <label for="request_type">Allocations<span class="required" style="color: red">*</span></label>
                       <select wire:model.debounce.300ms="selectedAllocation" class="form-control" required>
                           <option value="">Select Allocation</option>
                           @foreach ($allocations as $allocation)
                           <option value="{{ $allocation->id }}">{{ $allocation->allocation_number }} {{ $allocation->fuel_type }} {{ $allocation->quantity }}L  Expires On: {{ $allocation->expiry_date }}</option>
                           @endforeach
                       </select>
                        @error('selectedAllocation') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fuel_type">Fuel Type<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="fuel_type" class="form-control" required disabled>
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
                                @if (isset($fuel_balance))
                                <input type="number" step="any" min="0" max="{{$fuel_balance}}"  class="form-control" wire:model.debounce.300ms="quantity" placeholder="Enter quantity below or equal to {{$fuel_balance}} Litres" required >  
                                @else
                                <input type="number" step="any" min="0"   class="form-control" wire:model.debounce.300ms="quantity" placeholder="Enter Quantity" required >  
                                @endif
                               
                                @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

