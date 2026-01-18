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
                                <div class="row">       
                                    <div class="col-lg-3">
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                Filter By
                                            </span>
                                            <select wire:model.debounce.300ms="dispatch_filter" class="form-control" aria-label="..." >
                                                <option value="created_at">Dispatch Created At</option>
                                                <option value="date">Dispatch Date</option>
                                            </select>
                                        </div>
                                        <!-- /input-group -->
                                    </div>

                                
                                    <div class="col-lg-2" >
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                From
                                            </span>
                                            <input type="date" wire:model.debounce.300ms="from"  class="form-control" aria-label="...">
                                        </div>
                                        <!-- /input-group -->
                                    </div>
                                    <div class="col-lg-2" >
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                To
                                            </span>
                                            <input type="date" wire:model.debounce.300ms="to"  class="form-control" aria-label="...">
                                        </div>
                                        <!-- /input-group -->
                                    </div>
                                </div>
                                <a href="#" data-toggle="modal" data-target="#dispatchModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Dispatch</a>
                                {{-- <a href="#" data-toggle="modal" data-target="#ticket_expenseModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>None Inventory Item(s) Dispatch</a> --}}
                                <a href="#" wire:click="exportDispatchesExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                <a href="#" wire:click="exportDispatchesCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                <a href="#" wire:click="exportDispatchesPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                               
                               <i class="fa fa-info-circle text-primary" style="margin-right:16px;float: right"
                                    title="Once a dispatch is approved or rejected, it cannot be edited or deleted. If changes are required, create a new dispatch for the same ticket, provided the ticket is still open."
                                    style="cursor: pointer;">
                                </i>
                            </div>
                            
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                             <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search dispatches...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Dispatch#
                                    </th>
                                    <th class="th-sm">DispatchedBy
                                    </th>
                                    <th class="th-sm">Date
                                    </th>
                                    <th class="th-sm">RequestedBy
                                    </th>
                                    <th class="th-sm">Narration
                                    </th>
                                    <th class="th-sm">Items
                                    </th>
                                    <th class="th-sm">Qty
                                    </th>
                                    <th class="th-sm">Ccy
                                    </th>
                                    <th class="th-sm">Total
                                    </th>
                                    <th class="th-sm">Auth
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($dispatches))
                                <tbody>
                                    @forelse ($dispatches as $dispatch)
                                  <tr>
                                    <td>{{$dispatch->dispatch_number}}</td>
                                    <td>{{$dispatch->user ? $dispatch->user->name : ""}} {{$dispatch->user ? $dispatch->user->surname : ""}}</td>
                                    <td>{{$dispatch->date}}</td>
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
                                           <strong>Ticket#: </strong><a href="">{{$dispatch->ticket ? $dispatch->ticket->ticket_number : ""}}</a>
                                        @endif
                                        @if ($dispatch->horse)
                                           <strong>Horse: </strong><a href="">{{$dispatch->horse ? $dispatch->horse->registration_number : ""}} {{$dispatch->horse->fleet_number ? "(".$dispatch->horse->fleet_number.")" : ""}} 
                                            @if ($dispatch->horse->horse_make)
                                                {{$dispatch->horse->horse_make ? $dispatch->horse->horse_make->name : ""}} {{$dispatch->horse->horse_model ? $dispatch->horse->horse_model->name : ""}}       
                                           @endif
                                        </a>
                                        @endif
                                        @if ($dispatch->vehicle)
                                            <strong>Vehicle: </strong><a href="">{{$dispatch->vehicle ? $dispatch->vehicle->registration_number : ""}} {{$dispatch->vehicle->fleet_number ? "(".$dispatch->vehicle->fleet_number.")" : ""}}</a>
                                        @endif
                                        @if ($dispatch->trailer)
                                           <strong></strong>  Trailer:<a href="">{{$dispatch->trailer ? $dispatch->trailer->registration_number : ""}} {{$dispatch->trailer->fleet_number ? "(".$dispatch->trailer->fleet_number.")" : ""}}</a>
                                        @endif
                                                @if ($dispatch->employee)
                                                    <strong>Employee:</strong> {{$dispatch->employee ? $dispatch->employee->name : ""}} {{$dispatch->employee ? $dispatch->employee->surname : ""}} <br>
                                                @php
                                                    $asset_department = App\Models\Department::find($dispatch->department_id);
                                                @endphp
                                                @if ( $asset_department )
                                                    <strong>Department:</strong> {{ $asset_department->name}} <br>
                                                @endif
                                                @if ($dispatch->branch)
                                                    <strong>Branch:</strong> {{$dispatch->branch ? $dispatch->branch->name : ""}}
                                                @endif
                                        @endif
                                         @if ($dispatch->vendor)
                                            <strong>Vendor: </strong>{{$dispatch->vendor ? $dispatch->vendor->name : ""}}
                                        @endif
                                    </td>
                                    <td>
                                        
                                        @if ($dispatch->dispatch_items)
                                            @foreach ($dispatch->dispatch_items as $dispatch_item)
                                                @if ($dispatch_item->inventory)
                                                    {{$dispatch_item->inventory->product ? $dispatch_item->inventory->product->name : ""}} 
                                                @elseif($dispatch_item->asset)
                                                    {{$dispatch_item->asset->product ? $dispatch_item->asset->product->name : ""}}
                                                @elseif($dispatch_item->product)
                                                    {{$dispatch_item->product ? $dispatch_item->product->name : ""}}
                                                @elseif($dispatch_item->tyre)
                                                    {{$dispatch_item->tyre->product ? $dispatch_item->tyre->product->name : ""}}
                                                @endif
                                                @if (!$loop->last),@endif
                                            @endforeach
                                        @endif
                                    </td>
                                    <td>{{$dispatch->dispatch_items->sum('qty')}}</td>
                                    <td>{{$dispatch->currency ? $dispatch->currency->name : ""}}</td>
                                    <td> {{$dispatch->currency ? $dispatch->currency->symbol : ""}}{{number_format($dispatch->total ? $dispatch->total : 0 , 2)}}</td>
                                    <td>
                                        <span class="badge bg-{{($dispatch->authorization == 'approved') ? 'success' : (($dispatch->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($dispatch->authorization == 'approved') ? 'approved' : (($dispatch->authorization == 'rejected') ? 'rejected' : 'pending') }}</span>
                                        @php
                                            $user = App\Models\User::find($dispatch->authorized_by_id);
                                        @endphp
                                        <small>
                                        @if ($user)
                                            <br>
                                          <strong>{{$dispatch->authorization == "approved" ? "ApprovedBy" : "RejectedBy"}}:</strong> {{$user->name}} {{$user->surname}}
                                        @endif
                                        @if ($dispatch->authorization_date)
                                            <br>
                                         <strong>AuthorizedOn:</strong> {{Carbon\Carbon::parse($dispatch->authorization_date)->format('Y-m-d')}}
                                        @endif
                                        @if ($dispatch->authorization_comments)
                                            <br>
                                         <strong>Comments:</strong> {{$dispatch->authorization_comments}}
                                        @endif
                                        </small>
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('dispatches.show', $dispatch->id) }}" ><i class="fa fa-eye color-default"></i> View</a></li>
                                                @if ($dispatch->authorization == "pending" || Auth::user()->is_admin())
                                                    {{-- <li><a href="#"  wire:click="edit({{$dispatch->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li> --}}
                                                    <li><a href="#" wire:click="showDelete({{$dispatch->id}})"  ><i class="fa fa-trash color-danger"></i> Delete</a></li>
                                                @endif
                                                
                                            </ul>
                                        </div>
                                       
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="10">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Dispatches Found ....
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
                                    @if (isset($dispatches))
                                        {{ $dispatches->links() }} 
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


    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="dispatchDeleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content bg-danger">
            <div class="modal-body">
               <center> <strong>Are you sure you want to delete Dispatch Record {{$dispatch?->dispatch_number}} ?</strong> </center> 
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
 
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="dispatchModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog  mw-100 w-90" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add {{ucfirst($department)}} Dispatch <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                        @if ($company->valuation_method)
                           <p style="color: green"> {{$company->valuation_method == 'AVCO' ? 'Weighted Average Cost (AVCO)' : 'First In First Out(FIFO)'}} Inventory Valuation Method Selected </p> 
                        @endif
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
                    @if (in_array($department,['inventory','tyre']))
                    
                        <div class="form-group">
                            <label for="country">Tickets<span class="required" style="color: red">*</span></label>
                            <input type="text" wire:model.debounce.300ms="searchTicket" placeholder="Search tickets by ticket#, booking#, registration#, fleet#, driver..." class="form-control">
                            <select wire:model.debounce.300ms="selectedTicket" class="form-control" required size="4">
                                <option value="">Select Ticket</option>
                                @foreach ($tickets as $ticket)
                                    <option value="{{$ticket->id}}">
                                            @if ($ticket->booking)
                                                 {{$ticket->booking->booking_number ? "Booking#: ".$ticket->booking->booking_number : ""}}
                                            @endif
                                            {{$ticket->ticket_number ? "Ticket#: ".$ticket->ticket_number : ""}}
                                            {{$ticket->in_date ? "Date: ".$ticket->in_date : ""}}
                                            @if ($ticket->booking->service_type)
                                                , Service Type:  {{$ticket->booking->service_type ? $ticket->booking->service_type->name : ""}}
                                            @endif
                                            @if ($ticket->horse)
                                                , Horse: {{$ticket->horse->registration_number}} {{$ticket->horse->fleet_number ? "(".$ticket->horse->fleet_number.")" : ""}} {{$ticket->horse->horse_make ? $ticket->horse->horse_make->name : ""}} {{$ticket->horse->horse_model ? $ticket->horse->horse_model->name : ""}}
                                            @endif
                                            @if ($ticket->trailer)
                                                , Trailer: {{$ticket->trailer->registration_number}} {{$ticket->trailer->fleet_number ? "(".$ticket->trailer->fleet_number.")" : ""}} {{$ticket->trailer->make}} {{$ticket->trailer->model}}
                                            @endif
                                            @if ($ticket->vehicle)
                                                , Vehicle: {{$ticket->vehicle->registration_number}} {{$ticket->vehicle->fleet_number ? "(".$ticket->vehicle->fleet_number.")" : ""}} {{$ticket->vehicle->vehicle_make ? $ticket->vehicle->vehicle_make->name : ""}} {{$ticket->vehicle->vehicle_model ? $ticket->vehicle->vehicle_model->name : ""}}
                                            @endif
                                        </option>
                                @endforeach
                            </select>
                            @if (!is_null($selectedTicket))
                              <small style="color: green"><a href="{{route('tickets.show',$selectedTicket)}}" target="_blank"> Click to view ticket <strong>{{$ticket->ticket_number}}</strong></a></small>  
                            @endif
                            @error('selectedTicket') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                         
                    @endif
                    @if($department == "asset")
                        <h5 class="underline mt-30">Dispatch destination</h5>
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
                                    <select class="form-control" wire:model.debounce.300ms="asset_department_id">
                                        <option value="">Select Department</option>
                                        @foreach ($all_departments as $department)
                                            <option value="{{$department->id}}">{{$department->name}}</option>
                                        @endforeach
                                    </select>
                                    @error('asset_department_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                    <div class="form-group" >
                        <label for="name">Select what to dispatch</label>
                        <label class="radio-inline">
                            <input type="radio" wire:model.debounce.300ms="dispatch_for" value="inventory" name="optradio">Inventory Items
                        </label>
                        <label class="radio-inline">
                            <input type="radio" wire:model.debounce.300ms="dispatch_for" value="expenses" name="optradio">Non Inventory Items
                        </label>
                    </div>

                @if (!empty($dispatch_for) && $dispatch_for == "inventory")
                  

                     {{-- <h5 class="underline mt-30">Select store to dispatch from</h5> --}}
                    <div class="form-group">
                        <label for="purchase_date">Select store to dispatch from</label>
                            <select class="form-control" wire:model.debounce.300ms="selectedStore" >
                                <option value="">Select Store </option>
                                @foreach ($stores as $store)
                                    <option value="{{$store->id}}">{{$store->name}} </option>
                                @endforeach
                            </select>
                        @error('selectedStore') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-10">
                        <input type="checkbox" wire:model.debounce.300ms="expand"   class="line-style"  @if(count($inputs) > 0) disabled @endif />
                        <label for="one" class="radio-label">Select specific items to dispatch </label>
                        @error('expand') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                   
                    @if (!is_null($expand) && $expand == False)
                        <div style="background-color: lightgrey; padding:5px; border: 1px solid #333; border-radius: 5px;">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="horse">Requested Items</label>
                                        <select wire:model.debounce.300ms="requestedItem.0" class="form-control"  size="6">
                                                <option value="" disabled>Select Item</option>
                                                @if (!empty($ticket_requests))
                                                    @foreach ($ticket_requests as $ticket_request)
                                                        <option value="{{$ticket_request->id}}"
                                                            @if(in_array($ticket_request->id, $requestedItem ?? []) && ($requestedItem[0] ?? null) != $ticket_request->id) 
                                                                disabled 
                                                            @endif
                                                            >
                                                            @if ($ticket_request->product)
                                                                {{ $ticket_request->product->name }} {{ $ticket_request->product->brand ? $ticket_request->product->brand->name : "" }} {{ $ticket_request->product->identification_number ? "Part/ID#, ".$ticket_request->product->identification_number : "" }}
                                                            @endif
                                                              {{$ticket_request->product_name}} {{$ticket_request->qty ? "Qty: ".$ticket_request->qty." ".$ticket_request->measurement : ""}}
                                                        </option>
                                                    @endforeach
                                                @endif
                                        </select>
                                        @error('requestedItem.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div class="form-group">
                                        <label for="horse">Products<span class="required" style="color: red">*</span></label>
                                        <input type="text" wire:model.debounce.300ms="searchProduct" placeholder="Search products by name, brand name, ID/Model/Part#,..." class="form-control" >
                                        <select wire:model.debounce.300ms="selectedProduct.0" class="form-control" required size="4">
                                                <option value="" disabled>Select Products</option>
                                                @foreach ($products as $product)
                                                    <option value="{{$product->id}}"
                                                        @if(in_array($product->id, $selectedProduct ?? []) && ($selectedProduct[0] ?? null) != $product->id) 
                                                            disabled 
                                                        @endif
                                                        >  {{$product->name}} {{$product->brand ? $product->brand->name : ""}}, {{$product->product_number ? "Inventory#:  ".$product->product_number : ""}}, {{$product->identification_number ? "ID#: ".$product->identification_number : ""}} 
                                                      
                                                    @php
                                                        $relation = match ($department) {
                                                            'inventory' => 'inventories',
                                                            'tyre'      => 'tyres',
                                                            'asset'     => 'assets',
                                                            default     => null,
                                                        };

                                                        $count = null;

                                                        if ($relation) {
                                                            $count = $product->{$relation}()  // 👈 note the () = query, not collection
                                                                ->where('status', 1)
                                                                ->where('balance', '>', 0)
                                                                ->when($selectedStore, fn($q) => $q->where('store_id', $selectedStore))
                                                                ->sum('balance');
                                                        }
                                                    @endphp

                                                    @if(!is_null($count))
                                                        {{ $count }} {{ $product->unit_of_measure ?: "Unit(s)" }}
                                                    @endif
                                                     
                                                    </option>
                                                @endforeach
                                        </select>
                                        @error('selectedProduct.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label for="purchase_date">Qty<span class="required" style="color: red">*</span></label>
                                        @if ($max)
                                            <input type="number" step="any" min="0"  max="{{ $max[0] ?? '' }}"  class="form-control" wire:model.debounce.300ms="qty.0" placeholder="Qty" required>
                                        @else
                                            <input type="number" step="any" min="0"  class="form-control" wire:model.debounce.300ms="qty.0" placeholder="Qty" required>
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
                                    <div class="col-md-4">
                                    <div class="form-group">
                                            <label for="horse">Requested Items</label>
                                            <select wire:model.debounce.300ms="requestedItem.{{$value}}" class="form-control"  size="6">
                                                    <option value="" disabled>Select Item</option>
                                                    @if (!empty($ticket_requests))
                                                        @foreach ($ticket_requests as $ticket_request)
                                                            <option value="{{$ticket_request->id}}"
                                                                @if(in_array($ticket_request->id, $requestedItem ?? []) && ($requestedItem[0] ?? null) != $ticket_request->id) 
                                                                    disabled 
                                                                @endif
                                                                >
                                                                @if ($ticket_request->product)
                                                                    {{ $ticket_request->product->name }} {{ $ticket_request->product->brand ? $ticket_request->product->brand->name : "" }} {{ $ticket_request->product->identification_number ? "Part/ID#, ".$ticket_request->product->identification_number : "" }}
                                                                @endif
                                                                {{$ticket_request->product_name}} {{$ticket_request->qty ? "Qty: ".$ticket_request->qty." ".$ticket_request->measurement : ""}}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                            </select>
                                            @error('requestedItem.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="horse">Products<span class="required" style="color: red">*</span></label>
                                            <input type="text" wire:model.debounce.300ms="searchProduct" placeholder="Search products by name, brand name, ID/model/part#,..." class="form-control" >
                                            <select wire:model.debounce.300ms="selectedProduct.{{$value}}" class="form-control" required size="4">
                                                <option value="" selected>Select Products</option>
                                                @foreach ($products as $product)
                                                <option value="{{$product->id}}"
                                                        @if(in_array($product->id, $selectedProduct ?? []) && ($selectedProduct[$value] ?? null) != $product->id) 
                                                            disabled 
                                                        @endif
                                                    > 
                                                    {{$product->name}} {{$product->brand ? $product->brand->name : ""}}, {{$product->product_number ? "Code: ".$product->product_number : ""}}, {{$product->identification_number ? "Part/ID#, ".$product->identification_number : ""}} 
                                                           @php
                                                        $relation = match ($department) {
                                                            'inventory' => 'inventories',
                                                            'tyre'      => 'tyres',
                                                            'asset'     => 'assets',
                                                            default     => null,
                                                        };

                                                        $count = null;

                                                        if ($relation) {
                                                            $count = $product->{$relation}()  // 👈 note the () = query, not collection
                                                                ->where('status', 1)
                                                                ->where('balance', '>', 0)
                                                                ->when($selectedStore, fn($q) => $q->where('store_id', $selectedStore))
                                                                ->sum('balance');
                                                        }
                                                    @endphp

                                                    @if(!is_null($count))
                                                        {{ $count }} {{ $product->unit_of_measure ?: "Unit(s)" }}
                                                    @endif
                                                 </option>
                                                @endforeach                                                       
                                            </select>
                                            @error('selectedProduct.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label for="purchase_date">Qty<span class="required" style="color: red">*</span></label>
                                            @if ($max)
                                                <input type="number" step="any" min="0"  max="{{ $max[$value] ?? '' }}"  class="form-control" wire:model.debounce.300ms="qty.{{$value}}" placeholder="Qty" required>
                                            @else
                                                <input type="number" step="any" min="0"  class="form-control" wire:model.debounce.300ms="qty.{{$value}}" placeholder="Qty" required>
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
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="horse">Requested Items</label>
                                        <select wire:model.debounce.300ms="requestedItem.0" class="form-control"  size="6">
                                                <option value="" disabled>Select Item</option>
                                                @if (!empty($ticket_requests))
                                                    @foreach ($ticket_requests as $ticket_request)
                                                        <option value="{{$ticket_request->id}}"
                                                            @if(in_array($ticket_request->id, $requestedItem ?? []) && ($requestedItem[0] ?? null) != $ticket_request->id) 
                                                                disabled 
                                                            @endif
                                                            >
                                                            @if ($ticket_request->product)
                                                                {{ $ticket_request->product->name }} {{ $ticket_request->product->brand ? $ticket_request->product->brand->name : "" }} {{ $ticket_request->product->identification_number ? "Part/ID#, ".$ticket_request->product->identification_number : "" }}
                                                            @endif
                                                              {{$ticket_request->product_name}} {{$ticket_request->qty ? "Qty: ".$ticket_request->qty." ".$ticket_request->measurement : ""}}
                                                        </option>
                                                    @endforeach
                                                @endif
                                        </select>
                                        @error('requestedItem.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="horse">Products<span class="required" style="color: red">*</span></label>
                                        <input type="text" wire:model.debounce.300ms="searchProduct" placeholder="Search products by name, brand name, ID/model/part#,..." class="form-control" >
                                        <select wire:model.debounce.300ms="selectedProduct.0" class="form-control" required size="4">
                                            <option value="" selected>Select Products</option>
                                            @foreach ($products as $product)
                                                <option value="{{$product->id}}"> {{$product->name}} {{$product->brand ? $product->brand->name : ""}}, {{$product->product_number ? "Code: ".$product->product_number : ""}}, {{$product->identification_number ? "Part/ID#, ".$product->identification_number : ""}} 
                                                        @php
                                                        $relation = match ($department) {
                                                            'inventory' => 'inventories',
                                                            'tyre'      => 'tyres',
                                                            'asset'     => 'assets',
                                                            default     => null,
                                                        };

                                                        $count = null;

                                                        if ($relation) {
                                                            $count = $product->{$relation}()  // 👈 note the () = query, not collection
                                                                ->where('status', 1)
                                                                ->where('balance', '>', 0)
                                                                ->when($selectedStore, fn($q) => $q->where('store_id', $selectedStore))
                                                                ->sum('balance');
                                                        }
                                                    @endphp

                                                    @if(!is_null($count))
                                                        {{ $count }} {{ $product->unit_of_measure ?: "Unit(s)" }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('selectedProduct.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                @if(in_array($department,['asset','inventory']))
                                <div class="col-md-4">
                                    @if ($department == "inventory")
                                        <div class="form-group">
                                            <label for="horse">Items in Inventory<span class="required" style="color: red">*</span></label>
                                            <select wire:model.debounce.300ms="selectedInventory.0" class="form-control" required size = "4">
                                                <option value="" selected>Select Item</option>
                                                    @foreach ($inventories as $inventory)
                                                        <option value="{{$inventory->id}}"
                                                            @if(in_array($inventory->id, $selectedInventory ?? []) && ($selectedInventory[0] ?? null) != $inventory->id) 
                                                                disabled 
                                                            @endif
                                                            >
                                                            {{$inventory->inventory_number}} {{$inventory->serial_number ? "SN#: ".$inventory->serial_number : ""}} {{$inventory->balance ? "Bal: ".$inventory->balance: ""}} {{ $inventory->measurement ?? optional($inventory->product)->unit_of_measure ?? 'unit' }}
                                                            @if ($inventory->total)
                                                                {{$inventory->currency ? $inventory->currency->name : ""}} {{$inventory->currency ? $inventory->currency->symbol : ""}} {{ number_format((float)($inventory->balance ?? 0) * (float)($inventory->amount ?? 0),2)}}
                                                            @endif
                                                        </option>
                                                    @endforeach
                                            </select>
                                            @error('selectedInventory.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    @elseif($department == "asset")
                                         <div class="form-group">
                                            <label for="horse">Assets<span class="required" style="color: red">*</span></label>
                                            <select wire:model.debounce.300ms="selectedAsset.0" class="form-control" required size = "4">
                                                <option value="" selected>Select Item</option>
                                                    @foreach ($assets as $asset)
                                                        <option value="{{$asset->id}}"
                                                            @if(in_array($asset->id, $selectedAsset ?? []) && ($selectedAsset[0] ?? null) != $asset->id) 
                                                                disabled 
                                                            @endif
                                                            >
                                                            {{$asset->asset_number}} {{$asset->serial_number ? "SN#: ".$asset->serial_number : ""}} {{$asset->balance ? "Bal: ".$asset->balance: ""}} {{ $asset->measurement ?? optional($asset->product)->unit_of_measure ?? 'unit' }}
                                                            @if ($asset->total)
                                                                {{$asset->currency ? $asset->currency->name : ""}} {{$asset->currency ? $asset->currency->symbol : ""}}{{ number_format((float)($asset->balance ?? 0) * (float)($asset->amount ?? 0),2)}}  
                                                            @endif
                                                        </option>
                                                    @endforeach
                                            </select>
                                            @error('selectedAsset.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    @endif
                                </div>
                                @elseif($department == "tyre")
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="horse">Tyres<span class="required" style="color: red">*</span></label>
                                            <select wire:model.debounce.300ms="selectedTyre.0" class="form-control" required size = "4">
                                                    <option value="" selected>Select Item</option>
                                                    @foreach ($tyres as $tyre)
                                                        <option value="{{$tyre->id}}"
                                                            @if(in_array($tyre->id, $selectedTyre ?? []) && ($selectedTyre[0] ?? null) != $tyre->id) 
                                                                disabled 
                                                            @endif
                                                            >
                                                            {{$tyre->tyre_number}} {{$tyre->serial_number ? "SN#: ".$tyre->serial_number : ""}} {{$tyre->balance ? "Bal: ".$tyre->balance: ""}} {{ $tyre->measurement ?? optional($tyre->product)->unit_of_measure ?? 'unit' }}
                                                            @if ($tyre->total)
                                                                {{$tyre->currency ? $tyre->currency->name : ""}} {{$tyre->currency ? $tyre->currency->symbol : ""}}{{ number_format((float)($tyre->balance ?? 0) * (float)($tyre->amount ?? 0),2)}} 
                                                            @endif
                                                        </option>
                                                    @endforeach
                                            </select>
                                            @error('selectedTyre.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                @endif
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label for="weight">Qty<span class="required" style="color: red">*</span></label>
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
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="horse">Requested Items</label>
                                                <select wire:model.debounce.300ms="requestedItem.{{$value}}" class="form-control"  size="6">
                                                        <option value="" disabled>Select Item</option>
                                                        @if (!empty($ticket_requests))
                                                            @foreach ($ticket_requests as $ticket_request)
                                                                <option value="{{$ticket_request->id}}"
                                                                    @if(in_array($ticket_request->id, $requestedItem ?? []) && ($requestedItem[0] ?? null) != $ticket_request->id) 
                                                                        disabled 
                                                                    @endif
                                                                    >
                                                                    @if ($ticket_request->product)
                                                                        {{ $ticket_request->product->name }} {{ $ticket_request->product->brand ? $ticket_request->product->brand->name : "" }} {{ $ticket_request->product->identification_number ? "Part/ID#, ".$ticket_request->product->identification_number : "" }}
                                                                    @endif
                                                                    {{$ticket_request->product_name}} {{$ticket_request->qty ? "Qty: ".$ticket_request->qty." ".$ticket_request->measurement : ""}}
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                </select>
                                                @error('requestedItem.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="horse">Products<span class="required" style="color: red">*</span></label>
                                                <input type="text" wire:model.debounce.300ms="searchProduct" placeholder="Search products by name, brand name, ID/model/part#,..." class="form-control" >
                                                <select wire:model.debounce.300ms="selectedProduct.{{$value}}" class="form-control" required size="4">
                                                    <option value="" selected>Select Products</option>
                                                    @foreach ($products as $product)
                                                        <option value="{{$product->id}}"> {{$product->name}} {{$product->brand ? $product->brand->name : ""}}, {{$product->product_number ? "Code: ".$product->product_number : ""}}, {{$product->identification_number ? "Part/ID#, ".$product->identification_number : ""}} 
                                                                @php
                                                                    $relation = match ($department) {
                                                                        'inventory' => 'inventories',
                                                                        'tyre'      => 'tyres',
                                                                        'asset'     => 'assets',
                                                                        default     => null,
                                                                    };

                                                                    $count = null;

                                                                    if ($relation) {
                                                                        $count = $product->{$relation}()  // 👈 note the () = query, not collection
                                                                            ->where('status', 1)
                                                                            ->where('balance', '>', 0)
                                                                            ->when($selectedStore, fn($q) => $q->where('store_id', $selectedStore))
                                                                            ->sum('balance');
                                                                    }
                                                                @endphp

                                                    @if(!is_null($count))
                                                        {{ $count }} {{ $product->unit_of_measure ?: "Unit(s)" }}
                                                    @endif
                                                    </option>
                                                    @endforeach
                                                </select>
                                                @error('selectedProduct.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        @if(in_array($department,['asset','inventory']))
                                        <div class="col-md-4">
                                            @if ($department == "inventory")
                                                 <div class="form-group">
                                                    <label for="horse">Items in Inventory<span class="required" style="color: red">*</span></label>
                                                    <select wire:model.debounce.300ms="selectedInventory.{{$value}}" class="form-control" required size = "4">
                                                        <option value="" selected>Select Item</option>
                                                            @foreach ($inventories as $inventory)
                                                                <option value="{{$inventory->id}}"
                                                                    @if(in_array($inventory->id, $selectedInventory ?? []) && ($selectedInventory[$value] ?? null) != $inventory->id) 
                                                                        disabled 
                                                                    @endif
                                                                    >
                                                                    {{$inventory->inventory_number}} {{$inventory->serial_number ? "SN#: ".$inventory->serial_number : ""}} {{$inventory->balance ? "Bal: ".$inventory->balance: ""}} {{ $inventory->measurement ?? optional($inventory->product)->unit_of_measure ?? 'unit' }}
                                                                    @if ($inventory->total)
                                                                        {{$inventory->currency ? $inventory->currency->name : ""}} {{$inventory->currency ? $inventory->currency->symbol : ""}}{{ number_format((float)($inventory->balance ?? 0) * (float)($inventory->amount ?? 0),2)}}
                                                                    @endif
                                                                </option>
                                                            @endforeach
                                                    </select>
                                                    @error('selectedInventory.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            @elseif($department == "asset")
                                                <div class="form-group">
                                                    <label for="horse">Assets<span class="required" style="color: red">*</span></label>
                                                    <select wire:model.debounce.300ms="selectedAsset.{{$value}}" class="form-control" required size = "4">
                                                        <option value="" selected>Select Item</option>
                                                            @foreach ($assets as $asset)
                                                                <option value="{{$asset->id}}"
                                                                    @if(in_array($asset->id, $selectedAsset ?? []) && ($selectedAsset[$value] ?? null) != $asset->id) 
                                                                        disabled 
                                                                    @endif
                                                                    >
                                                                    {{$asset->asset_number}} {{$asset->serial_number ? "SN#: ".$asset->serial_number : ""}} {{$asset->balance ? "Bal: ".$asset->balance: ""}} {{ $asset->measurement ?? optional($asset->product)->unit_of_measure ?? 'unit' }}
                                                                    @if ($asset->total)
                                                                        {{$asset->currency ? $asset->currency->name : ""}} {{$asset->currency ? $asset->currency->symbol : ""}}{{ number_format((float)($asset->balance ?? 0) * (float)($asset->amount ?? 0),2)}} 
                                                                    @endif
                                                                </option>
                                                            @endforeach
                                                    </select>
                                                    @error('selectedAsset.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            @endif
                                        </div>
                                       
                                        @elseif($department == "tyre")
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="horse">Tyres<span class="required" style="color: red">*</span></label>
                                                <select wire:model.debounce.300ms="selectedTyre.{{$value}}" class="form-control" required size = "4">
                                                    <option value="" selected>Select Tyre</option>
                                                        @foreach ($tyres as $tyre)
                                                            <option value="{{$tyre->id}}"
                                                                @if(in_array($tyre->id, $selectedTyre ?? []) && ($selectedTyre[$value] ?? null) != $tyre->id) 
                                                                    disabled 
                                                                @endif
                                                                >
                                                                {{$tyre->tyre_number}} {{$tyre->serial_number ? "SN#: ".$tyre->serial_number : ""}} {{$tyre->balance ? "Bal: ".$tyre->balance: ""}} {{ $tyre->measurement ?? optional($tyre->product)->unit_of_measure ?? 'unit' }}
                                                                @if ($tyre->total)
                                                                    {{$tyre->currency ? $tyre->currency->name : ""}} {{$tyre->currency ? $tyre->currency->symbol : ""}}{{ number_format((float)($tyre->balance ?? 0) * (float)($tyre->amount ?? 0),2)}}
                                                                @endif
                                                            </option>
                                                        @endforeach
                                                </select>
                                                @error('selectedTyre.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        @endif
                                         <div class="col-md-1">
                                            <div class="form-group">
                                                <label for="weight">Qty<span class="required" style="color: red">*</span></label>
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
                        
                        @if ($expand == False)
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Product</button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                @elseif(!empty($dispatch_for) && $dispatch_for == "expenses")
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="country">Vendor(s)<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="vendor_id" class="form-control" required>
                                   <option value="">Select Vendor</option>
                                    @foreach ($vendors as $vendor)
                                        <option value="{{$vendor->id}}">{{$vendor->name}} {{$vendor->vendor_number}}</option> 
                                    @endforeach
                               </select>
                                @error('vendor_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                <small> <a href="{{ route('vendors.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Vendor</a></small> 
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="country">Currencies<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="currency_id" class="form-control" required>
                                   <option value="">Select Currency</option>
                                 @foreach ($currencies as $currency)
                                 <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option> 
                                 @endforeach
                              
                               </select>
                                @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                <small>  <a href="{{ route('currencies.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Currency</a></small> 
                            </div>
                            @if (!is_null($currency_id))
                            @if ($company)
                                @if ($currency_id != $company->currency_id)
                                <div class="form-group">
                                    <label for="customer">Conversion Rate</label>
                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate {{$selected_currency ? "From ".$selected_currency->name : ""}} {{$company->currency ? "To ".$company->currency->name : ""}}" >
                                    @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                    <small style="color: green">{{$selected_currency ? " 1 ".$selected_currency->name." is how much in" : ""}} {{$company->currency ? $company->currency->name." ?" : ""}}</small>
                                    <small>{{$exchange_amount ? "The fuel converted amount is: ".$exchange_amount : ""}}</small> 
                                </div> 
                                @endif
                            @endif
                        @endif
                            
                        </div>
                    </div>
                   
             
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="country">Items (Products & Services)<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="selectedExpenseProduct.0" class="form-control" required>
                                    <option value="">Select Item</option>
                                        @foreach ($products as $product)
                                            <option value="{{$product->id}}">
                                                <strong>{{$product->name}}</strong> {{$product->description ? "| ".$product->description : ""}}
                                            </option> 
                                        @endforeach
                                    </select>
                                    <small><a href="{{route('inventory_products.create')}}" target="_blank" ><i class="fa fa-plus-square-o"></i> New Product / Service</a></small><a href="#" wire:click.prevent="refresh('products')" class="float-end" style="float: right"><i class="fa fa-refresh"></i></a>
                                @error('selectedExpenseProduct.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                       
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="name">Description</label>
                               <textarea wire:model.debounce.300ms="item_description.0" class="form-control" cols="30" rows="1" placeholder="Enter Description"></textarea>
                                @error('item_description.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-2">
                                <div class="form-group">
                                <label for="country">Payment Methods</label>
                                <select wire:model.debounce.300ms="payment_method_id.0"  class="form-control"  >
                                    <option value="">Select Payment Method</option>
                                    @foreach ($payment_methods as $payment_method)
                                    <option value="{{ $payment_method->id }}">{{ $payment_method->name }}</option>
                                    @endforeach
                                </select>
                                @error('payment_method_id.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                <input type="number" class="form-control" wire:model.debounce.300ms="qty.0"  required />
                                @error('qty.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label for="name">Price<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount.0" required />
                                @error('amount.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="col-md-1">
                            <div class="form-group">
                                <label for="subheading">Taxes</label>
                                <select wire:model.debounce.300ms="selectedTax.0"  class="form-control">
                                    <option value="">Select Tax</option>
                                        @foreach ($tax_accounts as $tax)
                                           <option value="{{$tax->id}}">{{$tax->abbreviation}} </option> 
                                        @endforeach
                                    </select>
                                    <small><a href="{{ route('accounts.tax') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small> 
                                @error('selectedTax.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>      
                    </div>  
                    @foreach ($expenses_inputs as $key => $value)

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="country">Items (Products & Services)<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="selectedExpenseProduct.{{$value}}" class="form-control" required>
                                    <option value="">Select Item</option>
                                        @foreach ($products as $product)
                                            <option value="{{$product->id}}">
                                                <strong>{{$product->name}}</strong> {{$product->description ? "| ".$product->description : ""}}
                                            </option> 
                                        @endforeach
                                    </select>
                                    <small><a href="{{route('inventory_products.create')}}" target="_blank" ><i class="fa fa-plus-square-o"></i> New Product / Service</a></small><a href="#" wire:click.prevent="refresh('products')" class="float-end" style="float: right"><i class="fa fa-refresh"></i></a>
                                @error('selectedExpenseProduct.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                       
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="name">Description</label>
                               <textarea wire:model.debounce.300ms="item_description.{{$value}}" class="form-control" cols="30" rows="1" placeholder="Enter Description"></textarea>
                                @error('item_description.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-2">
                                <div class="form-group">
                                <label for="country">Payment Methods</label>
                                <select wire:model.debounce.300ms="payment_method_id.{{$value}}"  class="form-control"  >
                                    <option value="">Select Payment Method</option>
                                    @foreach ($payment_methods as $payment_method)
                                    <option value="{{ $payment_method->id }}">{{ $payment_method->name }}</option>
                                    @endforeach
                                </select>
                                @error('payment_method_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                <input type="number" class="form-control" wire:model.debounce.300ms="qty.{{$value}}"  required />
                                @error('qty.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label for="name">Price<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount.{{$value}}" required />
                                @error('amount.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="col-md-1">
                            <div class="form-group">
                                <label for="subheading">Taxes</label>
                                <select wire:model.debounce.300ms="selectedTax.{{$value}}"  class="form-control">
                                    <option value="">Select Tax</option>
                                        @foreach ($tax_accounts as $tax)
                                           <option value="{{$tax->id}}">{{$tax->abbreviation}} </option> 
                                        @endforeach
                                    </select>
                                    <small><a href="{{ route('accounts.tax') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small> 
                                @error('selectedTax.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>     
                         <div class="col-md-1">
                            <div class="form-group">
                                <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="removeExpense({{$key}})"> <i class="fa fa-times"></i></button>
                            </div>
                        </div> 
                    </div>
                        
                    @endforeach
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="addExpense({{$e}})"> <i class="fa fa-plus"></i> Expense</button>
                            </div>
                        </div>
                    </div>
    @endif
                
                <div class="form-group">
                    <label for="comment">Comments/Notes</label>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="dispatchEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog  mw-100 w-90" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Edit {{ucfirst($department)}} Dispatch <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                        @if ($company->valuation_method)
                           <p style="color: green"> {{$company->valuation_method == 'AVCO' ? 'Weighted Average Cost (AVCO)' : 'First In First Out(FIFO)'}} Inventory Valuation Method Selected </p> 
                        @endif
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
                    @if (in_array($department,['inventory','tyre']))
                        <div class="form-group">
                            <label for="country">Tickets<span class="required" style="color: red">*</span></label>
                            <input type="text" wire:model.debounce.300ms="searchTicket" placeholder="Search tickets by ticket#, booking#, registration#, fleet#, driver..." class="form-control">
                            <select wire:model.debounce.300ms="selectedTicket" class="form-control" required size="4">
                                <option value="">Select Ticket</option>
                                @foreach ($tickets as $ticket)
                                    <option value="{{$ticket->id}}">
                                            @if ($ticket->booking)
                                                 {{$ticket->booking->booking_number ? "Booking#: ".$ticket->booking->booking_number : ""}}
                                            @endif
                                            {{$ticket->ticket_number ? "Ticket#: ".$ticket->ticket_number : ""}}
                                            {{$ticket->in_date ? "Date: ".$ticket->in_date : ""}}
                                            @if ($ticket->booking->service_type)
                                                , Service Type:  {{$ticket->booking->service_type ? $ticket->booking->service_type->name : ""}}
                                            @endif
                                            @if ($ticket->horse)
                                                , Horse: {{$ticket->horse->registration_number}} {{$ticket->horse->fleet_number ? "(".$ticket->horse->fleet_number.")" : ""}} {{$ticket->horse->horse_make ? $ticket->horse->horse_make->name : ""}} {{$ticket->horse->horse_model ? $ticket->horse->horse_model->name : ""}}
                                            @endif
                                            @if ($ticket->trailer)
                                                , Trailer: {{$ticket->trailer->registration_number}} {{$ticket->trailer->fleet_number ? "(".$ticket->trailer->fleet_number.")" : ""}} {{$ticket->trailer->make}} {{$ticket->trailer->model}}
                                            @endif
                                            @if ($ticket->vehicle)
                                                , Vehicle: {{$ticket->vehicle->registration_number}} {{$ticket->vehicle->fleet_number ? "(".$ticket->vehicle->fleet_number.")" : ""}} {{$ticket->vehicle->vehicle_make ? $ticket->vehicle->vehicle_make->name : ""}} {{$ticket->vehicle->vehicle_model ? $ticket->vehicle->vehicle_model->name : ""}}
                                            @endif
                                        </option>
                                @endforeach
                            </select>
                            @if (!is_null($selectedTicket))
                              <small style="color: green"><a href="{{route('tickets.show',$selectedTicket)}}" target="_blank"> Click to view ticket <strong>{{$ticket->ticket_number}}</strong></a></small>  
                            @endif
                            @error('selectedTicket') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                         
                    @endif
                    @if($department == "asset")
                        <h5 class="underline mt-30">Dispatch destination</h5>
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
                                    <select class="form-control" wire:model.debounce.300ms="asset_department_id">
                                        <option value="">Select Department</option>
                                        @foreach ($all_departments as $department)
                                            <option value="{{$department->id}}">{{$department->name}}</option>
                                        @endforeach
                                    </select>
                                    @error('asset_department_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

                    

                     {{-- <h5 class="underline mt-30">Select store to dispatch from</h5> --}}
                    <div class="form-group">
                        <label for="purchase_date">Select store to dispatch from</label>
                            <select class="form-control" wire:model.debounce.300ms="selectedStore" >
                                <option value="">Select Store </option>
                                @foreach ($stores as $store)
                                    <option value="{{$store->id}}">{{$store->name}} </option>
                                @endforeach
                            </select>
                        @error('selectedStore') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>

                    

                    
                    <div class="mb-10">
                        <input type="checkbox" wire:model.debounce.300ms="expand"   class="line-style"  @if(count($inputs) > 0) disabled @endif />
                        <label for="one" class="radio-label">Select specific items to dispatch </label>
                        @error('expand') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                   
                    @if (!is_null($expand) && $expand == False)
                        <div style="background-color: lightgrey; padding:5px; border: 1px solid #333; border-radius: 5px;">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="horse">Requested Items</label>
                                        <select wire:model.debounce.300ms="requestedItem.0" class="form-control"  size="6">
                                                <option value="" disabled>Select Item</option>
                                                @if (!empty($ticket_requests))
                                                    @foreach ($ticket_requests as $ticket_request)
                                                        <option value="{{$ticket_request->id}}"
                                                            @if(in_array($ticket_request->id, $requestedItem ?? []) && ($requestedItem[0] ?? null) != $ticket_request->id) 
                                                                disabled 
                                                            @endif
                                                            >
                                                            @if ($ticket_request->product)
                                                                {{ $ticket_request->product->name }} {{ $ticket_request->product->brand ? $ticket_request->product->brand->name : "" }} {{ $ticket_request->product->identification_number ? "Part/ID#, ".$ticket_request->product->identification_number : "" }}
                                                            @endif
                                                              {{$ticket_request->product_name}} {{$ticket_request->qty ? "Qty: ".$ticket_request->qty." ".$ticket_request->measurement : ""}}
                                                        </option>
                                                    @endforeach
                                                @endif
                                        </select>
                                        @error('requestedItem.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    <div class="form-group">
                                        <label for="horse">Products<span class="required" style="color: red">*</span></label>
                                        <input type="text" wire:model.debounce.300ms="searchProduct" placeholder="Search products by name, brand name, ID/Model/Part#,..." class="form-control" >
                                        <select wire:model.debounce.300ms="selectedProduct.0" class="form-control" required size="4">
                                                <option value="" disabled>Select Products</option>
                                                @foreach ($products as $product)
                                                    <option value="{{$product->id}}"
                                                        @if(in_array($product->id, $selectedProduct ?? []) && ($selectedProduct[0] ?? null) != $product->id) 
                                                            disabled 
                                                        @endif
                                                        >  {{$product->name}} {{$product->brand ? $product->brand->name : ""}}, {{$product->product_number ? "Inventory#:  ".$product->product_number : ""}}, {{$product->identification_number ? "ID#: ".$product->identification_number : ""}} 
                                                      
                                                    @php
                                                        $relation = match ($department) {
                                                            'inventory' => 'inventories',
                                                            'tyre'      => 'tyres',
                                                            'asset'     => 'assets',
                                                            default     => null,
                                                        };

                                                        $count = null;

                                                        if ($relation) {
                                                            $count = $product->{$relation}()  // 👈 note the () = query, not collection
                                                                ->where('status', 1)
                                                                ->where('balance', '>', 0)
                                                                ->when($selectedStore, fn($q) => $q->where('store_id', $selectedStore))
                                                                ->sum('balance');
                                                        }
                                                    @endphp

                                                    @if(!is_null($count))
                                                        {{ $count }} {{ $product->unit_of_measure ?: "Unit(s)" }}
                                                    @endif
                                                     
                                                    </option>
                                                @endforeach
                                        </select>
                                        @error('selectedProduct.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label for="purchase_date">Qty<span class="required" style="color: red">*</span></label>
                                        @if ($max)
                                            <input type="number" step="any" min="0"  max="{{ $max[0] ?? '' }}"  class="form-control" wire:model.debounce.300ms="qty.0" placeholder="Qty" required>
                                        @else
                                            <input type="number" step="any" min="0"  class="form-control" wire:model.debounce.300ms="qty.0" placeholder="Qty" required>
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
                                    <div class="col-md-4">
                                    <div class="form-group">
                                            <label for="horse">Requested Items</label>
                                            <select wire:model.debounce.300ms="requestedItem.{{$value}}" class="form-control"  size="6">
                                                    <option value="" disabled>Select Item</option>
                                                    @if (!empty($ticket_requests))
                                                        @foreach ($ticket_requests as $ticket_request)
                                                            <option value="{{$ticket_request->id}}"
                                                                @if(in_array($ticket_request->id, $requestedItem ?? []) && ($requestedItem[0] ?? null) != $ticket_request->id) 
                                                                    disabled 
                                                                @endif
                                                                >
                                                                @if ($ticket_request->product)
                                                                    {{ $ticket_request->product->name }} {{ $ticket_request->product->brand ? $ticket_request->product->brand->name : "" }} {{ $ticket_request->product->identification_number ? "Part/ID#, ".$ticket_request->product->identification_number : "" }}
                                                                @endif
                                                                {{$ticket_request->product_name}} {{$ticket_request->qty ? "Qty: ".$ticket_request->qty." ".$ticket_request->measurement : ""}}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                            </select>
                                            @error('requestedItem.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="horse">Products<span class="required" style="color: red">*</span></label>
                                            <input type="text" wire:model.debounce.300ms="searchProduct" placeholder="Search products by name, brand name, ID/model/part#,..." class="form-control" >
                                            <select wire:model.debounce.300ms="selectedProduct.{{$value}}" class="form-control" required size="4">
                                                <option value="" selected>Select Products</option>
                                                @foreach ($products as $product)
                                                <option value="{{$product->id}}"
                                                        @if(in_array($product->id, $selectedProduct ?? []) && ($selectedProduct[$value] ?? null) != $product->id) 
                                                            disabled 
                                                        @endif
                                                    > 
                                                    {{$product->name}} {{$product->brand ? $product->brand->name : ""}}, {{$product->product_number ? "Code: ".$product->product_number : ""}}, {{$product->identification_number ? "Part/ID#, ".$product->identification_number : ""}} 
                                                           @php
                                                        $relation = match ($department) {
                                                            'inventory' => 'inventories',
                                                            'tyre'      => 'tyres',
                                                            'asset'     => 'assets',
                                                            default     => null,
                                                        };

                                                        $count = null;

                                                        if ($relation) {
                                                            $count = $product->{$relation}()  // 👈 note the () = query, not collection
                                                                ->where('status', 1)
                                                                ->where('balance', '>', 0)
                                                                ->when($selectedStore, fn($q) => $q->where('store_id', $selectedStore))
                                                                ->sum('balance');
                                                        }
                                                    @endphp

                                                    @if(!is_null($count))
                                                        {{ $count }} {{ $product->unit_of_measure ?: "Unit(s)" }}
                                                    @endif
                                                 </option>
                                                @endforeach                                                       
                                            </select>
                                            @error('selectedProduct.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label for="purchase_date">Qty<span class="required" style="color: red">*</span></label>
                                            @if ($max)
                                                <input type="number" step="any" min="0"  max="{{ $max[$value] ?? '' }}"  class="form-control" wire:model.debounce.300ms="qty.{{$value}}" placeholder="Qty" required>
                                            @else
                                                <input type="number" step="any" min="0"  class="form-control" wire:model.debounce.300ms="qty.{{$value}}" placeholder="Qty" required>
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
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="horse">Requested Items</label>
                                        <select wire:model.debounce.300ms="requestedItem.0" class="form-control"  size="6">
                                                <option value="" disabled>Select Item</option>
                                                @if (!empty($ticket_requests))
                                                    @foreach ($ticket_requests as $ticket_request)
                                                        <option value="{{$ticket_request->id}}"
                                                            @if(in_array($ticket_request->id, $requestedItem ?? []) && ($requestedItem[0] ?? null) != $ticket_request->id) 
                                                                disabled 
                                                            @endif
                                                            >
                                                            @if ($ticket_request->product)
                                                                {{ $ticket_request->product->name }} {{ $ticket_request->product->brand ? $ticket_request->product->brand->name : "" }} {{ $ticket_request->product->identification_number ? "Part/ID#, ".$ticket_request->product->identification_number : "" }}
                                                            @endif
                                                              {{$ticket_request->product_name}} {{$ticket_request->qty ? "Qty: ".$ticket_request->qty." ".$ticket_request->measurement : ""}}
                                                        </option>
                                                    @endforeach
                                                @endif
                                        </select>
                                        @error('requestedItem.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="horse">Products<span class="required" style="color: red">*</span></label>
                                        <input type="text" wire:model.debounce.300ms="searchProduct" placeholder="Search products by name, brand name, ID/model/part#,..." class="form-control" >
                                        <select wire:model.debounce.300ms="selectedProduct.0" class="form-control" required size="4">
                                            <option value="" selected>Select Products</option>
                                            @foreach ($products as $product)
                                                <option value="{{$product->id}}"> {{$product->name}} {{$product->brand ? $product->brand->name : ""}}, {{$product->product_number ? "Code: ".$product->product_number : ""}}, {{$product->identification_number ? "Part/ID#, ".$product->identification_number : ""}} 
                                                        @php
                                                        $relation = match ($department) {
                                                            'inventory' => 'inventories',
                                                            'tyre'      => 'tyres',
                                                            'asset'     => 'assets',
                                                            default     => null,
                                                        };

                                                        $count = null;

                                                        if ($relation) {
                                                            $count = $product->{$relation}()  // 👈 note the () = query, not collection
                                                                ->where('status', 1)
                                                                ->where('balance', '>', 0)
                                                                ->when($selectedStore, fn($q) => $q->where('store_id', $selectedStore))
                                                                ->sum('balance');
                                                        }
                                                    @endphp

                                                    @if(!is_null($count))
                                                        {{ $count }} {{ $product->unit_of_measure ?: "Unit(s)" }}
                                                    @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('selectedProduct.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                @if(in_array($department,['asset','inventory']))
                                <div class="col-md-4">
                                    @if ($department == "inventory")
                                        <div class="form-group">
                                            <label for="horse">Items in Inventory<span class="required" style="color: red">*</span></label>
                                            <select wire:model.debounce.300ms="selectedInventory.0" class="form-control" required size = "4">
                                                <option value="" selected>Select Item</option>
                                                    @foreach ($inventories as $inventory)
                                                        <option value="{{$inventory->id}}"
                                                            @if(in_array($inventory->id, $selectedInventory ?? []) && ($selectedInventory[0] ?? null) != $inventory->id) 
                                                                disabled 
                                                            @endif
                                                            >
                                                            {{$inventory->inventory_number}} {{$inventory->serial_number ? "SN#: ".$inventory->serial_number : ""}} {{$inventory->balance ? "Bal: ".$inventory->balance: ""}} {{ $inventory->measurement ?? optional($inventory->product)->unit_of_measure ?? 'unit' }}
                                                            @if ($inventory->total)
                                                                {{$inventory->currency ? $inventory->currency->name : ""}} {{$inventory->currency ? $inventory->currency->symbol : ""}}{{number_format($inventory->total,2)}}  
                                                            @endif
                                                        </option>
                                                    @endforeach
                                            </select>
                                            @error('selectedInventory.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    @elseif($department == "asset")
                                         <div class="form-group">
                                            <label for="horse">Assets<span class="required" style="color: red">*</span></label>
                                            <select wire:model.debounce.300ms="selectedAsset.0" class="form-control" required size = "4">
                                                <option value="" selected>Select Item</option>
                                                    @foreach ($assets as $asset)
                                                        <option value="{{$asset->id}}"
                                                            @if(in_array($asset->id, $selectedAsset ?? []) && ($selectedAsset[0] ?? null) != $asset->id) 
                                                                disabled 
                                                            @endif
                                                            >
                                                            {{$asset->asset_number}} {{$asset->serial_number ? "SN#: ".$asset->serial_number : ""}} {{$asset->balance ? "Bal: ".$asset->balance: ""}} {{ $asset->measurement ?? optional($asset->product)->unit_of_measure ?? 'unit' }}
                                                            @if ($asset->total)
                                                                {{$asset->currency ? $asset->currency->name : ""}} {{$asset->currency ? $asset->currency->symbol : ""}}{{number_format($asset->total,2)}}  
                                                            @endif
                                                        </option>
                                                    @endforeach
                                            </select>
                                            @error('selectedAsset.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    @endif
                                </div>
                                @elseif($department == "tyre")
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="horse">Tyres<span class="required" style="color: red">*</span></label>
                                            <select wire:model.debounce.300ms="selectedTyre.0" class="form-control" required size = "4">
                                                    <option value="" selected>Select Item</option>
                                                    @foreach ($tyres as $tyre)
                                                        <option value="{{$tyre->id}}"
                                                            @if(in_array($tyre->id, $selectedTyre ?? []) && ($selectedTyre[0] ?? null) != $tyre->id) 
                                                                disabled 
                                                            @endif
                                                            >
                                                            {{$tyre->tyre_number}} {{$tyre->serial_number ? "SN#: ".$tyre->serial_number : ""}} {{$tyre->balance ? "Bal: ".$tyre->balance: ""}} {{ $tyre->measurement ?? optional($tyre->product)->unit_of_measure ?? 'unit' }}
                                                            @if ($tyre->total)
                                                                {{$tyre->currency ? $tyre->currency->name : ""}} {{$tyre->currency ? $tyre->currency->symbol : ""}}{{number_format($tyre->total,2)}}  
                                                            @endif
                                                        </option>
                                                    @endforeach
                                            </select>
                                            @error('selectedTyre.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                @endif
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label for="weight">Qty<span class="required" style="color: red">*</span></label>
                                        @if ($max_weight)
                                            <input type="number" step="any" min="0"  max="{{ $max_weight[0] ?? '' }}"  class="form-control" wire:model.debounce.300ms="qty.0" placeholder="Enter Qty" required>
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
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="horse">Requested Items</label>
                                                <select wire:model.debounce.300ms="requestedItem.{{$value}}" class="form-control"  size="6">
                                                        <option value="" disabled>Select Item</option>
                                                        @if (!empty($ticket_requests))
                                                            @foreach ($ticket_requests as $ticket_request)
                                                                <option value="{{$ticket_request->id}}"
                                                                    @if(in_array($ticket_request->id, $requestedItem ?? []) && ($requestedItem[0] ?? null) != $ticket_request->id) 
                                                                        disabled 
                                                                    @endif
                                                                    >
                                                                    @if ($ticket_request->product)
                                                                        {{ $ticket_request->product->name }} {{ $ticket_request->product->brand ? $ticket_request->product->brand->name : "" }} {{ $ticket_request->product->identification_number ? "Part/ID#, ".$ticket_request->product->identification_number : "" }}
                                                                    @endif
                                                                    {{$ticket_request->product_name}} {{$ticket_request->qty ? "Qty: ".$ticket_request->qty." ".$ticket_request->measurement : ""}}
                                                                </option>
                                                            @endforeach
                                                        @endif
                                                </select>
                                                @error('requestedItem.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="horse">Products<span class="required" style="color: red">*</span></label>
                                                <input type="text" wire:model.debounce.300ms="searchProduct" placeholder="Search products by name, brand name, ID/model/part#,..." class="form-control" >
                                                <select wire:model.debounce.300ms="selectedProduct.{{$value}}" class="form-control" required size="4">
                                                    <option value="" selected>Select Products</option>
                                                    @foreach ($products as $product)
                                                        <option value="{{$product->id}}"> {{$product->name}} {{$product->brand ? $product->brand->name : ""}}, {{$product->product_number ? "Code: ".$product->product_number : ""}}, {{$product->identification_number ? "Part/ID#, ".$product->identification_number : ""}} 
                                                                @php
                                                                    $relation = match ($department) {
                                                                        'inventory' => 'inventories',
                                                                        'tyre'      => 'tyres',
                                                                        'asset'     => 'assets',
                                                                        default     => null,
                                                                    };

                                                                    $count = null;

                                                                    if ($relation) {
                                                                        $count = $product->{$relation}()  // 👈 note the () = query, not collection
                                                                            ->where('status', 1)
                                                                            ->where('balance', '>', 0)
                                                                            ->when($selectedStore, fn($q) => $q->where('store_id', $selectedStore))
                                                                            ->sum('balance');
                                                                    }
                                                                @endphp

                                                    @if(!is_null($count))
                                                        {{ $count }} {{ $product->unit_of_measure ?: "Unit(s)" }}
                                                    @endif
                                                    </option>
                                                    @endforeach
                                                </select>
                                                @error('selectedProduct.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        @if(in_array($department,['asset','inventory']))
                                        <div class="col-md-4">
                                            @if ($department == "inventory")
                                                 <div class="form-group">
                                                    <label for="horse">Items in Inventory<span class="required" style="color: red">*</span></label>
                                                    <select wire:model.debounce.300ms="selectedInventory.{{$value}}" class="form-control" required size = "4">
                                                        <option value="" selected>Select Item</option>
                                                            @foreach ($inventories as $inventory)
                                                                <option value="{{$inventory->id}}"
                                                                    @if(in_array($inventory->id, $selectedInventory ?? []) && ($selectedInventory[$value] ?? null) != $inventory->id) 
                                                                        disabled 
                                                                    @endif
                                                                    >
                                                                    {{$inventory->inventory_number}} {{$inventory->serial_number ? "SN#: ".$inventory->serial_number : ""}} {{$inventory->balance ? "Bal: ".$inventory->balance: ""}} {{ $inventory->measurement ?? optional($inventory->product)->unit_of_measure ?? 'unit' }}
                                                                    @if ($inventory->total)
                                                                        {{$inventory->currency ? $inventory->currency->name : ""}} {{$inventory->currency ? $inventory->currency->symbol : ""}}{{number_format($inventory->total,2)}}  
                                                                    @endif
                                                                </option>
                                                            @endforeach
                                                    </select>
                                                    @error('selectedInventory.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            @elseif($department == "asset")
                                                <div class="form-group">
                                                    <label for="horse">Assets<span class="required" style="color: red">*</span></label>
                                                    <select wire:model.debounce.300ms="selectedAsset.{{$value}}" class="form-control" required size = "4">
                                                        <option value="" selected>Select Item</option>
                                                            @foreach ($assets as $asset)
                                                                <option value="{{$asset->id}}"
                                                                    @if(in_array($asset->id, $selectedAsset ?? []) && ($selectedAsset[$value] ?? null) != $asset->id) 
                                                                        disabled 
                                                                    @endif
                                                                    >
                                                                    {{$asset->asset_number}} {{$asset->serial_number ? "SN#: ".$asset->serial_number : ""}} {{$asset->balance ? "Bal: ".$asset->balance: ""}} {{ $asset->measurement ?? optional($asset->product)->unit_of_measure ?? 'unit' }}
                                                                    @if ($asset->total)
                                                                        {{$asset->currency ? $asset->currency->name : ""}} {{$asset->currency ? $asset->currency->symbol : ""}}{{number_format($asset->total,2)}}  
                                                                    @endif
                                                                </option>
                                                            @endforeach
                                                    </select>
                                                    @error('selectedAsset.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            @endif
                                        </div>
                                       
                                        @elseif($department == "tyre")
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="horse">Tyres<span class="required" style="color: red">*</span></label>
                                                <select wire:model.debounce.300ms="selectedTyre.{{$value}}" class="form-control" required size = "4">
                                                    <option value="" selected>Select Tyre</option>
                                                        @foreach ($tyres as $tyre)
                                                            <option value="{{$tyre->id}}"
                                                                @if(in_array($tyre->id, $selectedTyre ?? []) && ($selectedTyre[$value] ?? null) != $tyre->id) 
                                                                    disabled 
                                                                @endif
                                                                >
                                                                {{$tyre->tyre_number}} {{$tyre->serial_number ? "SN#: ".$tyre->serial_number : ""}} {{$tyre->balance ? "Bal: ".$tyre->balance: ""}} {{ $tyre->measurement ?? optional($tyre->product)->unit_of_measure ?? 'unit' }}
                                                                @if ($tyre->total)
                                                                    {{$tyre->currency ? $tyre->currency->name : ""}} {{$tyre->currency ? $tyre->currency->symbol : ""}}{{number_format($tyre->total,2)}}  
                                                                @endif
                                                            </option>
                                                        @endforeach
                                                </select>
                                                @error('selectedTyre.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        @endif
                                         <div class="col-md-1">
                                            <div class="form-group">
                                                <label for="weight">Qty<span class="required" style="color: red">*</span></label>
                                                @if ($max_weight)
                                                    <input type="number" step="any" min="0"  max="{{ $max_weight[$value] ?? '' }}"  class="form-control" wire:model.debounce.300ms="weight.{{$value}}" placeholder="Enter Qty" required>
                                                @else
                                                    <input type="number" step="any" min="0"  class="form-control" wire:model.debounce.300ms="weight.{{$value}}" placeholder="Enter Qty" required>
                                                @endif
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
                        
                        @if ($expand == False)
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Product</button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                
                <div class="form-group">
                    <label for="comment">Comments/Notes</label>
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


      <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="ticket_expenseModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-90" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Expenses / Services <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="country">Vendor(s)<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="vendor_id" class="form-control" required>
                                   <option value="">Select Vendor</option>
                                    @foreach ($vendors as $vendor)
                                        <option value="{{$vendor->id}}">{{$vendor->name}} {{$vendor->vendor_number}}</option> 
                                    @endforeach
                               </select>
                                @error('vendor_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                <small> <a href="{{ route('vendors.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Vendor</a></small> 
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="country">Currencies<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="currency_id" class="form-control" required>
                                   <option value="">Select Currency</option>
                                 @foreach ($currencies as $currency)
                                 <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option> 
                                 @endforeach
                              
                               </select>
                                @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                <small>  <a href="{{ route('currencies.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Currency</a></small> 
                            </div>
                            @if (!is_null($currency_id))
                            @if ($company)
                                @if ($currency_id != $company->currency_id)
                                <div class="form-group">
                                    <label for="customer">Conversion Rate</label>
                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate {{$selected_currency ? "From ".$selected_currency->name : ""}} {{$company->currency ? "To ".$company->currency->name : ""}}" >
                                    @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                    <small style="color: green">{{$selected_currency ? " 1 ".$selected_currency->name." is how much in" : ""}} {{$company->currency ? $company->currency->name." ?" : ""}}</small>
                                    <small>{{$exchange_amount ? "The fuel converted amount is: ".$exchange_amount : ""}}</small> 
                                </div> 
                                @endif
                            @endif
                        @endif
                            
                        </div>
                     
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="name">Bill Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:change="billDate()" wire:model.debounce.300ms="bill_date" placeholder="Enter Bill Date" required >
                                @error('bill_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="name">Due Date</label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="due_date" placeholder="Enter Due Date"  >
                                @error('due_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="subheading">Notes</label>
                                <textarea class="form-control" wire:model.debounce.300ms="notes" cols="30" rows="3"></textarea>
                                @error('notes') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                            </div>

                    </div>
                   
             
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="country">Items<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="selectedProduct" class="form-control" required>
                                    <option value="">Select Item</option>
                                        @foreach ($products as $product)
                                            <option value="{{$product->id}}">
                                                <strong>{{$product->name}}</strong> {{$product->description ? "| ".$product->description : ""}}
                                            </option> 
                                        @endforeach
                                    </select>
                                    <small>  <a href="#" data-toggle="modal" data-target="#product_serviceModal"><i class="fa fa-plus-square-o"></i> New Product / Service</a></small> 
                                @error('selectedProduct') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="subheading">Expense Accounts<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="selectedAccount" class="form-control" required>
                                    <option value="">Select Expense Account</option>
                                @foreach ($accounts as $account)
                                    <option value="{{$account->id}}">{{$account->name}}</option> 
                                @endforeach
                            
                                </select>
                                @error('selectedAccount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="name">Description</label>
                               <textarea wire:model.debounce.300ms="description" class="form-control" cols="30" rows="1" placeholder="Enter Description"></textarea>
                                @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-2">
                                <div class="form-group">
                                <label for="country">Payment Methods</label>
                                <select wire:model.debounce.300ms="payment_method_id"  class="form-control"  >
                                    <option value="">Select Payment Method</option>
                                    @foreach ($payment_methods as $payment_method)
                                    <option value="{{ $payment_method->id }}">{{ $payment_method->name }}</option>
                                    @endforeach
                                </select>
                                @error('payment_method_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                <input type="number" class="form-control" wire:model.debounce.300ms="qty"  required />
                                @error('qty') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label for="name">Price<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount" required />
                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="col-md-1">
                            <div class="form-group">
                                <label for="subheading">Taxes</label>
                                <select wire:model.debounce.300ms="selectedTax"  class="form-control">
                                    <option value="">Select Tax</option>
                                        @foreach ($tax_accounts as $tax)
                                           <option value="{{$tax->id}}">{{$tax->abbreviation}} </option> 
                                        @endforeach
                                    </select>
                                    <small><a href="{{ route('taxes.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small> 
                                @error('selectedTax') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="ticket_expenseEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-90" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Expenses / Services <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="country">Vendor(s)<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="vendor_id" class="form-control" required>
                                   <option value="">Select Vendor</option>
                                    @foreach ($vendors as $vendor)
                                        <option value="{{$vendor->id}}">{{$vendor->name}} {{$vendor->vendor_number}}</option> 
                                    @endforeach
                               </select>
                                @error('vendor_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                <small> <a href="{{ route('vendors.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Vendor</a></small> 
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="country">Currencies<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="currency_id" class="form-control" required>
                                   <option value="">Select Currency</option>
                                 @foreach ($currencies as $currency)
                                 <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option> 
                                 @endforeach
                              
                               </select>
                                @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                <small>  <a href="{{ route('currencies.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Currency</a></small> 
                            </div>
               
                            @if (!is_null($currency_id))
                            @if (Auth::user()->employee->company)
                                @if ($currency_id != Auth::user()->employee->company->currency_id)
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="customer">Conversion Rate<span class="required" style="color: red">*</span></label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate" wire:change="exchangeRate()" placeholder="Exchange Rate" required>
                                            @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div> 
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="customer">Bill in {{ Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->name : "" }}<span class="required" style="color: red">*</span></label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_amount" placeholder="Converted Bill" required>
                                            @error('exchange_amount') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div> 
                                    </div>
                                </div>
                                @endif
                            @endif
                        @endif 
                        </div>
                     
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="name">Bill Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:change="billDate()" wire:model.debounce.300ms="bill_date" placeholder="Enter Bill Date" required >
                                @error('bill_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="name">Due Date</label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="due_date" placeholder="Enter Due Date"  >
                                @error('due_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="subheading">Notes</label>
                                <textarea class="form-control" wire:model.debounce.300ms="notes" cols="30" rows="3"></textarea>
                                @error('notes') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                            </div>

                    </div>
                   
             
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="country">Items<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="selectedProduct" class="form-control" required>
                                    <option value="">Select Item</option>
                                        @foreach ($products as $product)
                                            <option value="{{$product->id}}">
                                                <strong>{{$product->name}}</strong> {{$product->description ? "| ".$product->description : ""}}
                                            </option> 
                                        @endforeach
                                    </select>
                                    <small>  <a href="#" data-toggle="modal" data-target="#product_serviceModal"><i class="fa fa-plus-square-o"></i> New Product / Service</a></small> 
                                @error('selectedProduct') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="subheading">Expense Accounts<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="selectedAccount" class="form-control" required>
                                    <option value="">Select Expense Account</option>
                                @foreach ($accounts as $account)
                                    <option value="{{$account->id}}">{{$account->name}}</option> 
                                @endforeach
                            
                                </select>
                                @error('selectedAccount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="name">Description</label>
                               <textarea wire:model.debounce.300ms="description" class="form-control" cols="30" rows="1" placeholder="Enter Description"></textarea>
                                @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-2">
                                <div class="form-group">
                                <label for="country">Payment Methods</label>
                                <select wire:model.debounce.300ms="payment_method_id"  class="form-control"  >
                                    <option value="">Select Payment Method</option>
                                    @foreach ($payment_methods as $payment_method)
                                    <option value="{{ $payment_method->id }}">{{ $payment_method->name }}</option>
                                    @endforeach
                                </select>
                                @error('payment_method_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                <input type="number" class="form-control" wire:model.debounce.300ms="qty"  required />
                                @error('qty') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label for="name">Price<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount" required />
                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="col-md-1">
                            <div class="form-group">
                                <label for="subheading">Taxes</label>
                                <select wire:model.debounce.300ms="selectedTax"  class="form-control">
                                    <option value="">Select Tax</option>
                                        @foreach ($tax_accounts as $tax)
                                           <option value="{{$tax->id}}">{{$tax->abbreviation}}</option> 
                                        @endforeach
                                    </select>
                                    <small><a href="{{ route('taxes.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small> 
                                @error('selectedTax') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="product_serviceModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> New Product / Service<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="storeItem()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="comment">Item Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="item_name" placeholder="Enter Item Name" required>
                                @error('item_name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="comment">Description</label>
                            <textarea class="form-control" wire:model.debounce.300ms="item_description" cols="30" rows="4"></textarea>
                                @error('item_description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-10">
                                <label for=""></label>
                                <input type="checkbox" wire:model.debounce.300ms="sell"   class="line-style" />
                                <label for="one" class="radio-label">Sell this?</label>
                                @error('sell') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-10">
                                <input type="checkbox" wire:model.debounce.300ms="buy"   class="line-style" disabled />
                                <label for="one" class="radio-label">Buy this?</label>
                                @error('buy') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    @if ($buy == True)
                    <div class="form-group">
                        <label for="subheading">Select Expense Account</label>
                        <select wire:model.debounce.300ms="expense_account_id" class="form-control">
                            <option value="">Select Tax</option>
                                @foreach ($accounts as $account)
                                <option value="{{$account->id}}">{{$account->name}}</option> 
                                @endforeach
                            </select>
                            {{-- <small><a href="{{ route('accounts.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Account</a></small>  --}}
                        @error('expense_account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    @endif
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="comment">Price</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="item_price" placeholder="Enter Price" >
                                @error('item_price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="subheading">Sales Tax</label>
                                <select wire:model.debounce.300ms="tax_id" class="form-control">
                                    <option value="">Select Tax</option>
                                        @foreach ($tax_accounts as $tax)
                                        <option value="{{$tax->id}}">{{$tax->abbreviation}} {{$tax->rate ? $tax->rate."%" : ""}}</option> 
                                        @endforeach
                                    </select>
                                    <small><a href="{{ route('accounts.tax') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small> 
                                @error('tax_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

 
   
</div>

