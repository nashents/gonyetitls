<div>
                    @php
                        $departments = Auth::user()->employee->departments;
                        foreach($departments as $department){
                            $department_names[] = $department->name;
                        }
                        $roles = Auth::user()->roles;
                        foreach($roles as $role){
                            $role_names[] = $role->name;
                        }
                        $wsdepartment = App\Models\Department::where('name','Workshop')->first();
                        if (isset($wsdepartment)) {
                            $wsdepartment_head = App\Models\DepartmentHead::where('department_id',$wsdepartment->id)->where('employee_id',Auth::user()->employee->id)->first();
                        }
                        $stdepartment = App\Models\Department::where('name','Workshop')->first();
                        if (isset($stdepartment)) {
                            $stdepartment_head = App\Models\DepartmentHead::where('department_id',$stdepartment->id)->where('employee_id',Auth::user()->employee->id)->first();
                        }
                        $fndepartment = App\Models\Department::where('name','Finance')->first();
                        if (isset($fndepartment)) {
                            $fndepartment_head = App\Models\DepartmentHead::where('department_id',$fndepartment->id)->where('employee_id',Auth::user()->employee->id)->first();
                        }
                    @endphp
                    @if (isset($wsdepartment_head) ||  (in_array('Admin', $role_names) && in_array('Stores', $department_names))|| in_array('Super Admin', $role_names))
                          <a href="" data-toggle="modal" data-target="#ticket_inventoryModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Item(s)</a>
                    @endif
                        <br>
                        <br>
                        <br>
                    <table id="partsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                        <thead >
                         <tr>
                            <th class="th-sm">Item
                            </th>
                            <th class="th-sm">Quantities
                            </th>
                            <th class="th-sm">Currency
                            </th>
                            <th class="th-sm">Amount
                            </th>
                            <th class="th-sm">Actions
                            </th>
                          </tr>
                        </thead>
                        @if ($ticket_inventories->count()>0)
                        <tbody>
                           @foreach ($ticket_inventories as  $ticket_inventory)
                            <tr>
                                <td>
                                    @if ($ticket_inventory->inventory)
                                    <a href="{{route('inventories.show',$ticket_inventory->inventory->id)}}" target="_blank" style="color: blue">
                                        {{$ticket_inventory->inventory ? $ticket_inventory->inventory->inventory_number : ""}}
                                        {{$ticket_inventory->inventory->product ? $ticket_inventory->inventory->product->name : ""}}
                                        {{$ticket_inventory->inventory->serial_number ? "SN#: ".$ticket_inventory->inventory->serial_number : ""}} {{$ticket_inventory->inventory->part_number ? "PN#: ".$ticket_inventory->inventory->part_number : ""}}
                                    </a>
                                    @elseif ($ticket_inventory->tyre)
                                    @php
                                            $tyre = $ticket_inventory->tyre;
                                    @endphp
                                    <a href="{{route('tyres.show',$ticket_inventory->tyre->id)}}" target="_blank" style="color: blue">
                                        {{$tyre->tyre_number}} {{$tyre->product->brand ? $tyre->product->brand->name : ""}} {{$tyre->product ? $tyre->product->name : ""}} SN#: {{$tyre->serial_number}} |  {{$tyre->width}} / {{$tyre->aspect_ratio}} R {{$tyre->diameter}}
                                        @if ($tyre->subtotal_incl)
                                        {{$tyre->currency ? $tyre->currency->name : ""}} {{$tyre->currency ? $tyre->currency->symbol : ""}}{{number_format($tyre->subtotal_incl,2)}}  
                                        @endif
                                    </a>
                                    @endif
                                 
                                </td>  
                                <td>
                                    @if ($ticket_inventory->inventory)
                                    {{$ticket_inventory->weight}} {{$ticket_inventory->measurement}}
                                    @elseif ($ticket_inventory->tyre)
                                    1
                                    @endif
                                </td>
                                <td>{{$ticket_inventory->currency ? $ticket_inventory->currency->name : ""}}</td>
                                <td>
                                    @if (isset($ticket_inventory->amount) && is_numeric($ticket_inventory->amount))
                                        {{ $ticket_inventory->currency ? $ticket_inventory->currency->symbol : "" }}{{number_format($ticket_inventory->amount,2)}}        
                                    @endif
                                </td>   
                                <td class="w-10 line-height-35 table-dropdown">
                                    <div class="dropdown">
                                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fa fa-bars"></i>
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a href="#"  wire:click="edit({{$ticket_inventory->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                            <li><a href="#" data-toggle="modal" data-target="#ticket_inventoryDeleteModal{{ $ticket_inventory->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                        </ul>
                                    </div>
                                    @include('ticket_inventories.delete')
                            </td> 
                            </tr>
                            @endforeach
                        </tbody>
                        @else
                        <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                     @endif
                      </table>

                      <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="ticket_inventoryModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
                        <div class="modal-dialog mw-100 w-90" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Item(s) Required / Used <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                                </div>
                                <form wire:submit.prevent="addProducts()" >
                                <div class="modal-body">
                                    
                                    <div class="form-group" >
                                        <label for="name">Inventory Type</label>
                                        <label class="radio-inline">
                                            <input type="radio" wire:model.debounce.300ms="inventory_type" value="spares" name="optradio" >Spares
                                          </label>
                                          <label class="radio-inline">
                                            <input type="radio" wire:model.debounce.300ms="inventory_type" value="tyres" name="optradio">Tyres
                                          </label>
                                    </div>
                                    @if (isset($inventory_type) && $inventory_type == "spares")
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label for="horse">Products / Services<span class="required" style="color: red">*</span></label>
                                                <input type="text" wire:model.debounce.300ms="search_inventory" placeholder="Search products..." class="form-control" >
                                               <select wire:model.debounce.300ms="selectedProduct" class="form-control" required size="4">
                                                   <option value="" selected>Select Products / Services</option>
                                                    @foreach ($inventory_products as $product)
                                                        <option value="{{$product->id}}">{{$product->product_number}} {{$product->brand ? $product->brand->name : ""}} {{$product->name}} <strong>{{$product->inventories->where('status',1)->where('balance','>',0)->count()}} Item(s) in store</strong> </option>
                                                    @endforeach
                                               </select>
                                                @error('selectedProduct') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label for="horse">Items in Inventory<span class="required" style="color: red">*</span></label>
                                               <select wire:model.debounce.300ms="selectedInventory" class="form-control" required size="6">
                                                   <option value="" selected>Select Item</option>
                                                    @foreach ($inventories as $inventory)
                                                        <option value="{{$inventory->id}}">{{$inventory->inventory_number}} {{$inventory->serial_number ? "SN#: ".$inventory->serial_number : ""}} {{$inventory->part_number ? "PN#: ".$inventory->part_number : ""}} {{$inventory->weight}} {{$inventory->measurement}} {{$inventory->balance ? "Bal: ".$inventory->balance." ".$inventory->measurement : ""}}
                                                            @if ($inventory->subtotal_incl)
                                                            {{$inventory->currency ? $inventory->currency->name : ""}} {{$inventory->currency ? $inventory->currency->symbol : ""}}{{number_format($inventory->subtotal_incl,2)}}  
                                                            @endif
                                                        </option>
                                                    @endforeach
                                               </select>
                                                @error('selectedInventory') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="purchase_date">Item Contents<span class="required" style="color: red">*</span></label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="weight" placeholder="Enter Required/Used Amounts" required>
                                                @error('weight') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                            </div>
                                    </div>
                                    @else
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="horse">Products / Services<span class="required" style="color: red">*</span></label>
                                                <input type="text" wire:model.debounce.300ms="search_tyres" placeholder="Search products..." class="form-control" >
                                               <select wire:model.debounce.300ms="selectedProduct" class="form-control" required size="4">
                                                   <option value="" selected>Select Products / Services</option>
                                                    @foreach ($tyre_products as $product)
                                                        <option value="{{$product->id}}">{{$product->product_number}} {{$product->brand ? $product->brand->name : ""}} {{$product->name}} <strong>{{$product->inventories->where('status',1)->where('balance','>',0)->count()}} Item(s) in store</strong> </option>
                                                    @endforeach
                                               </select>
                                                @error('selectedProduct') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="horse">Tyres in Inventory<span class="required" style="color: red">*</span></label>
                                               <select wire:model.debounce.300ms="selectedTyre" class="form-control" required size="6">
                                                   <option value="" selected>Select Tyre</option>
                                                    @foreach ($tyres as $tyre)
                                                            <option value="{{$tyre->id}}">{{$tyre->tyre_number}} {{$tyre->product->brand ? $tyre->product->brand->name : ""}} {{$tyre->product ? $tyre->product->name : ""}} SN#: {{$tyre->serial_number}} |  {{$tyre->width}} / {{$tyre->aspect_ratio}} R {{$tyre->diameter}} </option>
                                                            @if ($tyre->subtotal_incl)
                                                            {{$tyre->currency ? $tyre->currency->name : ""}} {{$tyre->currency ? $tyre->currency->symbol : ""}}{{number_format($tyre->subtotal_incl,2)}}  
                                                            @endif
                                                        </option>
                                                    @endforeach
                                               </select>
                                                @error('selectedTyre') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    
                                    </div>
                                    @endif
                                   
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
                      <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="ticket_inventoryEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
                        <div class="modal-dialog mw-100 w-90" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Edit Item Required / Used <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                                </div>
                                <form wire:submit.prevent="update()" >
                                <div class="modal-body">
                                    <div class="form-group" >
                                        <label for="name">Inventory Type</label>
                                        <label class="radio-inline">
                                            <input type="radio" wire:model.debounce.300ms="inventory_type" value="spares" name="optradio" >Spares
                                          </label>
                                          <label class="radio-inline">
                                            <input type="radio" wire:model.debounce.300ms="inventory_type" value="tyres" name="optradio">Tyres
                                          </label>
                                    </div>
                                    @if (isset($inventory_type) && $inventory_type == "spares")
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label for="horse">Products / Services<span class="required" style="color: red">*</span></label>
                                                <input type="text" wire:model.debounce.300ms="search_inventory" placeholder="Search products..." class="form-control" >
                                               <select wire:model.debounce.300ms="selectedProduct" class="form-control" required size="4">
                                                   <option value="" selected>Select Products / Services</option>
                                                    @foreach ($inventory_products as $product)
                                                        <option value="{{$product->id}}">{{$product->product_number}} {{$product->brand ? $product->brand->name : ""}} {{$product->name}} <strong>{{$product->inventories->where('status',1)->where('balance','>',0)->count()}} Item(s) in store</strong> </option>
                                                    @endforeach
                                               </select>
                                                @error('selectedProduct') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label for="horse">Items in Inventory<span class="required" style="color: red">*</span></label>
                                               <select wire:model.debounce.300ms="selectedInventory" class="form-control" required size="6">
                                                   <option value="" selected>Select Item</option>
                                                    @foreach ($inventories as $inventory)
                                                        <option value="{{$inventory->id}}">{{$inventory->inventory_number}} {{$inventory->serial_number ? "SN#: ".$inventory->serial_number : ""}} {{$inventory->part_number ? "PN#: ".$inventory->part_number : ""}} {{$inventory->weight}} {{$inventory->measurement}} {{$inventory->balance ? "Bal: ".$inventory->balance." ".$inventory->measurement : ""}}
                                                            @if ($inventory->subtotal_incl)
                                                            {{$inventory->currency ? $inventory->currency->name : ""}} {{$inventory->currency ? $inventory->currency->symbol : ""}}{{number_format($inventory->subtotal_incl,2)}}  
                                                            @endif
                                                        </option>
                                                    @endforeach
                                               </select>
                                                @error('selectedInventory') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="purchase_date">Item Contents<span class="required" style="color: red">*</span></label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="weight" placeholder="Enter Required/Used Amounts" required>
                                                @error('weight') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                            </div>
                                    </div>
                                    @else
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="horse">Products / Services<span class="required" style="color: red">*</span></label>
                                                <input type="text" wire:model.debounce.300ms="search_tyres" placeholder="Search products..." class="form-control" >
                                               <select wire:model.debounce.300ms="selectedProduct" class="form-control" required size="4">
                                                   <option value="" selected>Select Products / Services</option>
                                                    @foreach ($tyre_products as $product)
                                                        <option value="{{$product->id}}">{{$product->product_number}} {{$product->brand ? $product->brand->name : ""}} {{$product->name}} <strong>{{$product->inventories->where('status',1)->where('balance','>',0)->count()}} Item(s) in store</strong> </option>
                                                    @endforeach
                                               </select>
                                                @error('selectedProduct') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="horse">Tyres in Inventory<span class="required" style="color: red">*</span></label>
                                               <select wire:model.debounce.300ms="selectedTyre" class="form-control" required size="6">
                                                   <option value="" selected>Select Tyre</option>
                                                    @foreach ($tyres as $tyre)
                                                            <option value="{{$tyre->id}}">{{$tyre->tyre_number}} {{$tyre->product->brand ? $tyre->product->brand->name : ""}} {{$tyre->product ? $tyre->product->name : ""}} SN#: {{$tyre->serial_number}} |  {{$tyre->width}} / {{$tyre->aspect_ratio}} R {{$tyre->diameter}} </option>
                                                            @if ($tyre->subtotal_incl)
                                                            {{$tyre->currency ? $tyre->currency->name : ""}} {{$tyre->currency ? $tyre->currency->symbol : ""}}{{number_format($tyre->subtotal_incl,2)}}  
                                                            @endif
                                                        </option>
                                                    @endforeach
                                               </select>
                                                @error('selectedTyre') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    
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
                            </div>
                        </div>
                    </div>
</div>
