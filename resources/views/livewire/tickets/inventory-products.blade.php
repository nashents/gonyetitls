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
        @if (isset($wsdepartment_head) ||  (in_array('Admin', $role_names) && in_array('Stores', $department_names)))
        <a href="" data-toggle="modal" data-target="#ticket_inventoryModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Inventory Products</a>
        @endif
    <br>
                    <table id="partsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                        <thead >
                         <tr>
                            <th class="th-sm">Ticket#s
                            </th>
                            <th class="th-sm">Product
                            </th>
                            <th class="th-sm">Part#
                            </th>
                            <th class="th-sm">Qty
                            </th>
                            <th class="th-sm">Subtotal
                            </th>
                            <th class="th-sm">Usage
                            </th>
                            <th class="th-sm">Actions
                            </th>
                          </tr>
                        </thead>
                        @if ($ticket_inventories->count()>0)
                        <tbody>
                           @foreach ($ticket_inventories as  $ticket_inventory)
                            <tr>
                                <td>{{$ticket->ticket_number}}</td>
                                <td>
                                    @if ($ticket_inventory->inventory)
                                    {{$ticket_inventory->inventory->product ? $ticket_inventory->inventory->product->name : " product"}}
                                    @endif
                                  </td>
                                <td>{{$ticket_inventory->inventory ? $ticket_inventory->inventory->part_number : " part#"}}</td>
                                <td>{{$ticket_inventory->qty}}</td>
                                <td>{{$ticket_inventory->subtotal}}</td>
                                <td>{{$ticket_inventory->usage}}</td>
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
                                    {{-- @include('ticket_inventories.delete') --}}
                            </td>
                            </tr>
                            @endforeach
                        </tbody>
                        @else
                        <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                     @endif
                      </table>

                      <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="ticket_inventoryModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Repairs/Spares <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                                </div>
                                <form wire:submit.prevent="addProducts()" >
                                <div class="modal-body">

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="horse">Inventories</label>
                                               <select wire:model.debounce.300ms="selectedInventory.0" class="form-control" required>
                                                   <option value="" selected>Select Inventory</option>
                                                    @foreach ($inventories as $inventory)
                                                        <option value="{{$inventory->id}}">{{$inventory->product ? $inventory->product->name: ""}} {{$inventory->inventory_number}}  ({{$inventory->qty}} <small> Items Left</small> )</option>
                                                    @endforeach
                                               </select>
                                                @error('selectedInventory.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        @if (isset($selectedInventory))
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="date">Quantity</label>
                                             <input type="number" min="1"  class="form-control" wire:model.debounce.300ms="qty.0"   placeholder="Enter Quantity" required>
                                                @error('qty.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        @else
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="date">Quantity</label>
                                             <input type="number" min="1"  class="form-control" wire:model.debounce.300ms="qty"   placeholder="Enter Quantity" disabled required>
                                                @error('qty') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        @endif



                                    </div>
                                    @foreach ($inputs as $key => $value)
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="horse">Inventories</label>
                                               <select wire:model.debounce.300ms="selectedInventory.{{$value}}" class="form-control" required>
                                                   <option value="" selected>Select Inventory</option>
                                                    @foreach ($inventories as $inventory)
                                                        <option value="{{$inventory->id}}">{{$inventory->product ? $inventory->product->name: ""}} {{$inventory->inventory_number}}  ({{$inventory->qty}} <small> Items Left</small> )</option>
                                                    @endforeach
                                               </select>
                                                @error('selectedInventory.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        @if (isset($selectedInventory))
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="date">Quantity</label>
                                             <input type="number" min="1"  class="form-control" wire:model.debounce.300ms="qty.{{$value}}"   placeholder="Enter Quantity" required>
                                                @error('qty.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        @else
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="date">Quantity</label>
                                             <input type="number" min="1"  class="form-control" wire:model.debounce.300ms="qty.{{$value}}"   placeholder="Enter Quantity" disabled required>
                                                @error('qty') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        @endif
                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Product</button>
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
