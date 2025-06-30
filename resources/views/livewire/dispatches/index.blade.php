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
                                <a href="#" data-toggle="modal" data-target="#dispatchModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Dispatch</a>
                                <a href="#" wire:click="exportCargosExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                <a href="#" wire:click="exportCargosCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                <a href="#" wire:click="exportCargosPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="dispatchesTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Dispatch#
                                    </th>
                                    <th class="th-sm">DispatchedBy
                                    </th>
                                    <th class="th-sm">RequestedBy
                                    </th>
                                    <th class="th-sm">Narration
                                    </th>
                                    <th class="th-sm">Items
                                    </th>
                                    <th class="th-sm">Auth
                                    </th>
                                    <th class="th-sm">Total Items
                                    </th>
                                    <th class="th-sm">Total Value
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($dispatches->count()>0)
                                <tbody>
                                    @foreach ($dispatches as $dispatch)
                                  <tr>
                                    <td>{{$dispatch->dispatch_number}}</td>
                                    <td>{{$dispatch->user ? $dispatch->user->name : ""}} {{$dispatch->user ? $dispatch->user->surname : ""}}</td>
                                    <td>
                                        @php
                                            $requested_by = App\Models\Employee::find($dispatch->requested_by_id);
                                        @endphp
                                        @if ($requested_by)
                                            {{$requested_by->name}} {{$requested_by->surname}}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($dispatch->ticket)
                                            Ticket#: <a href="">{{$dispatch->ticket ? $dispatch->ticket->ticket_number : ""}}</a>
                                        @endif
                                        @if ($dispatch->horse)
                                            Horse: <a href="">{{$dispatch->horse ? $dispatch->horse->registration_number : ""}} {{$dispatch->horse->fleet_number ? "(".$dispatch->horse->fleet_number.")" : ""}}</a>
                                        @endif
                                        @if ($dispatch->vehicle)
                                            Vehicle: <a href="">{{$dispatch->vehicle ? $dispatch->vehicle->registration_number : ""}} {{$dispatch->vehicle->fleet_number ? "(".$dispatch->vehicle->fleet_number.")" : ""}}</a>
                                        @endif
                                        @if ($dispatch->trailer)
                                            Trailer: <a href="">{{$dispatch->trailer ? $dispatch->trailer->registration_number : ""}} {{$dispatch->trailer->fleet_number ? "(".$dispatch->trailer->fleet_number.")" : ""}}</a>
                                        @endif
                                        @if ($dispatch->employee)
                                            Employee: {{$dispatch->employee ? $dispatch->employee->name : ""}} {{$dispatch->employee ? $dispatch->employee->surname : ""}}
                                                @if ($dispatch->department)
                                                    Department: {{$dispatch->department ? $dispatch->department->name : ""}}
                                                @endif
                                                @if ($dispatch->branch)
                                                    Branch: {{$dispatch->branch ? $dispatch->branch->name : ""}}
                                                @endif
                                        @endif
                                       
                                    </td>
                                    <td><span class="badge bg-{{($dispatch->authorization == 'approved') ? 'success' : (($dispatch->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($dispatch->authorization == 'approved') ? 'approved' : (($dispatch->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('dispatches.show', $dispatch->id) }}" ><i class="fa fa-eye color-default"></i> View</a></li>
                                                <li><a href="#"  wire:click="edit({{$dispatch->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#dispatchDeleteModal{{ $dispatch->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('dispatches.delete')
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

 
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="dispatchModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog  mw-100 w-80" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Dispatch <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="purchase_date">Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Date" required>
                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="purchase_date">Requested By</label>
                                <select class="form-control" wire:model.debounce.300ms="requested_by_id">
                                    <option value="">Select Employee</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{$employee->id}}">{{$employee->name}} {{$employee->surname}}</option>
                                    @endforeach
                                </select>
                                @error('requested_by_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    @if ($department == "inventory" || $department == "tyre")
                    <div class="form-group">
                    <label for="country">Tickets<span class="required" style="color: red">*</span></label>
                    <input type="text" wire:model.debounce.300ms="searchTicket" placeholder="Search tickets by ticket#, booking#, registration#, fleet#, driver..." class="form-control">
                       <select wire:model.debounce.300ms="selectedTicket" class="form-control" required size="4">
                           <option value="">Select Ticket</option>
                           @foreach ($tickets as $ticket)
                               <option value="{{$ticket->id}}">
                                    {{$ticket->booking->booking_number ? "Booking#: ".$ticket->booking->booking_number : ""}}
                                    {{$ticket->ticket_number ? "Ticket#: ".$ticket->ticket_number : ""}}
                                    {{$ticket->in_date ? "Date: ".$ticket->in_date : ""}}
                                    @if ($ticket->booking->service_type)
                                        , Service Type:  {{$ticket->booking->service_type ? $ticket->booking->service_type->name : ""}}
                                    @endif
                                    @if ($ticket->horse)
                                        , Horse: {{$ticket->horse->registration_number}} {{$ticket->horse->fleet_number ? "(".$ticket->horse->fleet_number.")" : ""}}
                                    @endif
                                    @if ($ticket->trailer)
                                        , Trailer: {{$ticket->trailer->registration_number}} {{$ticket->trailer->fleet_number ? "(".$ticket->trailer->fleet_number.")" : ""}}
                                    @endif
                                    @if ($ticket->vehicle)
                                        , Vehicle: {{$ticket->vehicle->registration_number}} {{$ticket->vehicle->fleet_number ? "(".$ticket->vehicle->fleet_number.")" : ""}}
                                    @endif
                                </option>
                           @endforeach
                       </select>
                        @error('selectedTicket') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    @elseif($department == "asset")
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="purchase_date">Employees</label>
                                <select class="form-control" wire:model.debounce.300ms="selectedEmployee">
                                    <option value="">Select Employee</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{$employee->id}}">{{$employee->name}} {{$employee->surname}}</option>
                                    @endforeach
                                </select>
                                @error('selectedEmployee') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                             <div class="form-group">
                                <label for="purchase_date">Department</label>
                                <select class="form-control" wire:model.debounce.300ms="department_id">
                                    <option value="">Select Department</option>
                                    @foreach ($departments as $department)
                                        <option value="{{$department->id}}">{{$department->name}}</option>
                                    @endforeach
                                </select>
                                @error('department_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                             <div class="form-group">
                                <label for="purchase_date">Branch</label>
                                <select class="form-control" wire:model.debounce.300ms="branch_id">
                                    <option value="">Select Branch</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{$branch->id}}">{{$branch->name}}</option>
                                    @endforeach
                                </select>
                                @error('branch_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="mb-10">
                        <input type="checkbox" wire:model.debounce.300ms="expand"   class="line-style" />
                        <label for="one" class="radio-label">Select Specific Items In Store </label>
                        @error('expand') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                    @if ($expand == False)
                        <div style="background-color: lightgrey; padding:5px; border: 1px solid #333; border-radius: 5px;">
                            <div class="row">
                                <div class="col-md-10">
                                    <div class="form-group">
                                        <label for="horse">Products<span class="required" style="color: red">*</span></label>
                                        <input type="text" wire:model.debounce.300ms="searchProduct" placeholder="Search products by name, brand name, ID/model/part#,..." class="form-control" >
                                        <select wire:model.debounce.300ms="selectedProduct.0" class="form-control" required size="4">
                                                <option value="" selected>Select Products</option>
                                                @foreach ($products as $product)
                                                    <option value="{{$product->id}}"> {{$product->name}} {{$product->brand ? $product->brand->name : ""}}, {{$product->product_number ? "Code: ".$product->product_number : ""}}, {{$product->identification_number ? "Part/ID#, ".$product->identification_number : ""}} <strong>{{$product->inventories->where('status',1)->where('balance','>',0)->count()}} Item(s)</strong> </option>
                                                @endforeach
                                        </select>
                                        @error('selectedProduct.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="purchase_date">Qty<span class="required" style="color: red">*</span></label>
                                        @if ($max)
                                            <input type="number" step="any" min="0"  max="{{ $max[0] ?? '' }}"  class="form-control" wire:model.debounce.300ms="qty.0" placeholder="Enter Qty" required>
                                        @else
                                            <input type="number" step="any" min="0"  class="form-control" wire:model.debounce.300ms="qty.0" placeholder="Enter Qty" required>
                                        @endif
                                        @error('qty.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                        @foreach ($inputs as $key => $value)
                            <div style="background-color: lightgrey; padding:5px; border: 1px solid #333; border-radius: 5px;">
                                    <div class="row">
                                        <div class="col-md-9">
                                            <div class="form-group">
                                                <label for="horse">Products<span class="required" style="color: red">*</span></label>
                                                <input type="text" wire:model.debounce.300ms="searchProduct" placeholder="Search products by name, brand name, ID/model/part#,..." class="form-control" >
                                                <select wire:model.debounce.300ms="selectedProduct.{{$value}}" class="form-control" required size="4">
                                                    <option value="" selected>Select Products</option>
                                                    @foreach ($products as $product)
                                                    <option value="{{$product->id}}"> {{$product->name}} {{$product->brand ? $product->brand->name : ""}}, {{$product->product_number ? "Code: ".$product->product_number : ""}}, {{$product->identification_number ? "Part/ID#, ".$product->identification_number : ""}} <strong>{{$product->inventories->where('status',1)->where('balance','>',0)->count()}} Item(s)</strong> </option>
                                                    @endforeach                                                       
                                                </select>
                                                @error('selectedProduct.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="purchase_date">Qty<span class="required" style="color: red">*</span></label>
                                                @if ($max)
                                            <input type="number" step="any" min="0"  max="{{ $max[$value] ?? '' }}"  class="form-control" wire:model.debounce.300ms="qty.{{$value}}" placeholder="Enter Qty" required>
                                        @else
                                            <input type="number" step="any" min="0"  class="form-control" wire:model.debounce.300ms="qty.{{$value}}" placeholder="Enter Qty" required>
                                        @endif
                                                @error('qty.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <br>
                        @endforeach
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Product</button>
                                </div>
                            </div>
                        </div>
                    @else
                        <div style="background-color: lightgrey; padding:5px; border: 1px solid #333; border-radius: 5px;">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="horse">Products<span class="required" style="color: red">*</span></label>
                                        <input type="text" wire:model.debounce.300ms="searchProduct.0" placeholder="Search products by name, brand name, ID/model/part#,..." class="form-control" >
                                        <select wire:model.debounce.300ms="selectedProduct.0" class="form-control" required size="4">
                                            <option value="" selected>Select Products</option>
                                                    <option value="" selected>Select Products</option>
                                                    @foreach ($products as $product)
                                                        <option value="{{$product->id}}"> {{$product->name}} {{$product->brand ? $product->brand->name : ""}}, {{$product->product_number ? "Code: ".$product->product_number : ""}}, {{$product->identification_number ? "Part/ID#, ".$product->identification_number : ""}} <strong>{{$product->inventories->where('status',1)->where('balance','>',0)->count()}} Item(s)</strong> </option>
                                                    @endforeach
                                        </select>
                                        @error('selectedProduct.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="horse">Items in Inventory<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="selectedInventory.0" class="form-control" required multiple>
                                            <option value="" selected>Select Item</option>
                                                @foreach ($inventories as $inventory)
                                                    <option value="{{$inventory->id}}">
                                                        {{$inventory->inventory_number}} {{$inventory->serial_number ? "SN#: ".$inventory->serial_number : ""}} {{$inventory->balance ? "Bal: ".$inventory->balance: ""}} {{$inventory->product->unit_of_measure ? $inventory->product->unit_of_measure : ""}}
                                                        @if ($inventory->total)
                                                            {{$inventory->currency ? $inventory->currency->name : ""}} {{$inventory->currency ? $inventory->currency->symbol : ""}}{{number_format($inventory->total,2)}}  
                                                        @endif
                                                    </option>
                                                @endforeach
                                        </select>
                                        @error('selectedInventory.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="weight">Required Quantities<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="weight.0" placeholder="Enter Qty" required>
                                        @error('weight.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                        @foreach ($inputs as $key => $value)
                            <div style="background-color: lightgrey; padding:5px; border: 1px solid #333; border-radius: 5px;">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="horse">Products<span class="required" style="color: red">*</span></label>
                                                <input type="text" wire:model.debounce.300ms="searchProduct" placeholder="Search products by name, brand name, ID/model/part#,..." class="form-control" >
                                                <select wire:model.debounce.300ms="selectedProduct.{{$value}}" class="form-control" required size="4">
                                                    <option value="" selected>Select Products</option>
                                                    @foreach ($products as $product)
                                                        <option value="{{$product->id}}"> {{$product->name}} {{$product->brand ? $product->brand->name : ""}}, {{$product->product_number ? "Code: ".$product->product_number : ""}}, {{$product->identification_number ? "Part/ID#, ".$product->identification_number : ""}} <strong>{{$product->inventories->where('status',1)->where('balance','>',0)->count()}} Item(s)</strong> </option>
                                                    @endforeach
                                                </select>
                                                @error('selectedProduct.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                          <div class="col-md-5">
                                            <div class="form-group">
                                                <label for="horse">Items in Inventory<span class="required" style="color: red">*</span></label>
                                                <select wire:model.debounce.300ms="selectedInventory.{{$value}}" class="form-control" required multiple>
                                                    <option value="" selected>Select Item</option>
                                                        @foreach ($inventories as $inventory)
                                                            <option value="{{$inventory->id}}">
                                                                {{$inventory->inventory_number}} {{$inventory->serial_number ? "SN#: ".$inventory->serial_number : ""}} {{$inventory->balance ? "Bal: ".$inventory->balance: ""}} {{$inventory->product->unit_of_measure ? $inventory->product->unit_of_measure : ""}}
                                                                @if ($inventory->total)
                                                                    {{$inventory->currency ? $inventory->currency->name : ""}} {{$inventory->currency ? $inventory->currency->symbol : ""}}{{number_format($inventory->total,2)}}  
                                                                @endif
                                                            </option>
                                                        @endforeach
                                                </select>
                                                @error('selectedInventory.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="weight">Required Quantities<span class="required" style="color: red">*</span></label>
                                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="weight.{{$value}}" placeholder="Enter Qty" required>
                                                @error('weight.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <br>
                        @endforeach
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Product</button>
                                </div>
                            </div>
                        </div>
                    @endif
                
                <div class="form-group">
                    <label for="weight">Comments/Notes</label>
                    <textarea class="form-control" wire:model.debounce.300ms="description" placeholder="Enter Dispatch Notes / Comments" cols="30" rows="4"></textarea>
                    @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
   
</div>

