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
                            {{-- <div class="panel-title" style="float: right">
                                <a href="{{route('assignments.export.excel')}}"  class="btn btn-default"><i class="fa fa-download"></i>Excel</a>
                                <a href="{{route('assignments.export.csv')}}"  class="btn btn-default"><i class="fa fa-download"></i>CSV</a>
                                <a href="{{route('assignments.export.pdf')}}"  class="btn btn-default"><i class="fa fa-download"></i>PDF</a>
                                {{-- <a href="" data-toggle="modal" data-target="#assignmentsImportModal" class="btn btn-default"><i class="fa fa-upload"></i>Import</a> --}}
                            {{-- </div>  --}}
                            <div class="panel-title">
                                <a href="" data-toggle="modal" data-target="#inventory_assignmentModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Assignment</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="inventory_assignmentsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Inventory#
                                    </th>
                                    <th class="th-sm">Item
                                    </th>
                                    <th class="th-sm">Qty
                                    </th>
                                    <th class="th-sm">RequestBy
                                    </th>
                                    <th class="th-sm">Horse
                                    </th>
                                    <th class="th-sm">Vehicle
                                    </th>
                                    <th class="th-sm">Trailer
                                    </th>
                                    <th class="th-sm">Date
                                    </th>
                                    <th class="th-sm">Specification
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($inventory_assignments->count()>0)
                                <tbody>
                                    @foreach ($inventory_assignments as $assignment)
                                  <tr>
                                    <td>{{$assignment->inventory ? $assignment->inventory->inventory_number : ""}}</td>
                                    <td>
                                        @if ($assignment->inventory)
                                        {{$assignment->inventory->product ? $assignment->inventory->product->name : ""}} ({{$assignment->inventory ? $assignment->inventory->part_number : ""}})
                                        @endif
                                    </td>
                                    <td>{{$assignment->qty}}</td>
                                    <td>{{ucfirst($assignment->employee ? $assignment->employee->name : "")}} {{ucfirst($assignment->employee ? $assignment->employee->surname : "")}}</td>
                                    <td>
                                        @if ($assignment->horse)
                                        {{$assignment->horse->horse_make ? $assignment->horse->horse_make->name : ""}} {{$assignment->horse->horse_model ? $assignment->horse->horse_model->name : ""}}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($assignment->vehicle)
                                        {{$assignment->vehicle->vehicle_make ? $assignment->vehicle->vehicle_make->name : ""}} {{$assignment->vehicle->vehicle_model ? $assignment->vehicle->vehicle_model->name : ""}}
                                        @endif
                                    </td>
                                    <td>
                                        {{$assignment->trailer ? $assignment->trailer->make : ""}}  {{$assignment->trailer ? $assignment->trailer->model : ""}}
                                    </td>
                                    <td>{{$assignment->date}}</td>
                                    <td>{{$assignment->specifications}}</td>
                                    <td><span class="label label-{{$assignment->status == 1 ? "success" : "danger"}} label-rounded">{{$assignment->status == 1 ? "Assigned" : "Unassigned"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#"  wire:click="edit({{$assignment->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                {{-- @if ($assignment->status == 1)
                                                <li><a href="#"  wire:click="unAssignment({{$assignment->id}})" ><i class="fa fa-undo color-success"></i> Unassign</a></li>
                                                @endif --}}
                                                {{-- <li><a href="#" data-toggle="modal" data-target="#assignmentDeleteModal{{ $assignment->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li> --}}
                                            </ul>
                                        </div>
                                        @include('assignments.delete')
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="inventory_assignmentModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Inventory Assignment <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="horse">Items<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="selectedProduct.0" class="form-control" required>
                                   <option value="" selected>Select Item</option>
                                    @foreach ($products as $product)
                                            @if ($product->inventories->count() > 0)
                                            <option value="{{$product->id}}">{{$product->product_number}} {{$product->brand ? $product->brand->name : ""}} {{$product->name}} | {{$product->inventories->count()}} <small> Items Left</small> </option>
                                            @endif
                                    @endforeach
                               </select>
                                @error('selectedProduct.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @if (isset($selectedProduct))
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date">Quantity<span class="required" style="color: red">*</span></label>
                             <input type="number" min="1"  class="form-control" wire:model.debounce.300ms="qty.0"   placeholder="Enter Quantity" required>
                                @error('qty.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @else
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date">Quantity<span class="required" style="color: red">*</span></label>
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
                                <label for="horse">Items<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="selectedProduct.{{$value}}" class="form-control" required>
                                   <option value="" selected>Select Item</option>
                                   @foreach ($products as $product)
                                    @if ($product->inventories->count() > 0)
                                    <option value="{{$product->id}}">{{$product->product_number}} {{$product->brand ? $product->brand->name : ""}} {{$product->name}} | {{$product->inventories->count()}} <small> Items Left</small> </option>
                                    @endif
                                    @endforeach
                               </select>
                                @error('selectedProduct.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @if (isset($selectedProduct))
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="date">Quantity<span class="required" style="color: red">*</span></label>
                             <input type="number" min="1"  class="form-control" wire:model.debounce.300ms="qty.{{$value}}"   placeholder="Enter Quantity" required>
                                @error('qty.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @else
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="date">Quantity<span class="required" style="color: red">*</span></label>
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
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date">Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="date"  placeholder="Assignment Date" required>
                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="horse">RequestedBy<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="employee_id" class="form-control" required>
                                   <option value="" selected>Select Employee</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{$employee->id}}">{{$employee->name}} {{$employee->surname}}</option>
                                    @endforeach
                               </select>
                                @error('employee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="horse">Horses</label>
                               <select wire:model.debounce.300ms="horse_id" class="form-control" >
                                   <option value="" selected>Select Horse</option>
                                    @foreach ($horses as $horse)
                                        <option value="{{$horse->id}}">{{$horse->registration_number}} {{$horse->horse_make ? $horse->horse_make->name : ""}} {{$horse->horse_model ? $horse->horse_model->name : ""}}</option>
                                    @endforeach
                               </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="horse">Vehicles</label>
                               <select wire:model.debounce.300ms="vehicle_id" class="form-control" >
                                   <option value="" selected>Select Vehicle</option>
                                    @foreach ($vehicles as $vehicle)
                                        <option value="{{$vehicle->id}}">{{$vehicle->registration_number}} {{$vehicle->vehicle_make ? $vehicle->vehicle_make->name : ""}} {{$vehicle->vehicle_model ? $vehicle->vehicle_model->name : ""}}</option>
                                    @endforeach
                               </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="horse">Trailers</label>
                               <select wire:model.debounce.300ms="trailer_id" class="form-control" >
                                   <option value="" selected>Select Trailer</option>
                                    @foreach ($trailers as $trailer)
                                        <option value="{{$trailer->id}}">{{$trailer->registration_number}} {{$trailer->make}} {{$trailer->model}}</option>
                                    @endforeach
                               </select>
                            </div>
                        </div>
                    </div>
                   
                    <div class="form-group">
                        <label for="specifications">Description</label>
                        <textarea wire:model.debounce.300ms="specifications" class="form-control" cols="30" rows="5"></textarea>
                        @error('specifications') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Assign</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="inventory_assignmentEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Edit inventory Assignment <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="horse">Inventories</label>
                                   <select wire:model.debounce.300ms="selectedProduct" class="form-control" required>
                                       <option value="" selected>Select Inventory</option>
                                        @foreach ($inventories as $inventory)
                                            <option value="{{$inventory->id}}">{{$inventory->product ? $inventory->product->name: "undefined"}} {{$inventory->inventory_number}} </option>
                                        @endforeach
                                   </select>
                                    @error('selectedProduct') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            @if (isset($selectedProduct))
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="date">Quantity</label>
                                 <input type="number" min="1"  class="form-control" wire:model.debounce.300ms="qty"   placeholder="Enter Quantity" required>
                                    @error('qty') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                        <div class="form-group">
                            <label for="horse">RequestedBy</label>
                           <select wire:model.debounce.300ms="employee_id" class="form-control" required>
                               <option value="" selected>Select Employee</option>
                                @foreach ($employees as $employee)
                                    <option value="{{$employee->id}}">{{$employee->name}} {{$employee->surname}}</option>
                                @endforeach
                           </select>
                            @error('employee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="horse">Horses</label>
                           <select wire:model.debounce.300ms="horse_id" class="form-control" >
                               <option value="" selected>Select Horse</option>
                                @foreach ($horses as $horse)
                                    <option value="{{$horse->id}}">{{$horse->registration_number}} {{$horse->horse_make ? $horse->horse_make : ""}} {{$horse->horse_model ? $horse->horse_model : ""}}</option>
                                @endforeach
                           </select>
                        </div>
                        <div class="form-group">
                            <label for="horse">Vehicles</label>
                           <select wire:model.debounce.300ms="vehicle_id" class="form-control" >
                               <option value="" selected>Select Vehicle</option>
                                @foreach ($vehicles as $vehicle)
                                    <option value="{{$vehicle->id}}">{{$vehicle->registration_number}} {{$vehicle->vehicle_make ? $vehicle->vehicle_make : ""}} {{$vehicle->vehicle_model ? $vehicle->vehicle_model : ""}}</option>
                                @endforeach
                           </select>
                        </div>
                        <div class="form-group">
                            <label for="horse">Trailers</label>
                           <select wire:model.debounce.300ms="trailer_id" class="form-control" >
                               <option value="" selected>Select Trailer</option>
                                @foreach ($trailers as $trailer)
                                    <option value="{{$trailer->id}}">{{$trailer->registration_number}} {{$trailer->make}} {{$trailer->model}}</option>
                                @endforeach
                           </select>
                        </div>
                        <div class="form-group">
                            <label for="date">Assignemt Date</label>
                         <input type="date" class="form-control" wire:model.debounce.300ms="date" required placeholder="Assignment Date" required>
                            @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label for="specifications">Specification</label>
                        <textarea wire:model.debounce.300ms="specifications" class="form-control" cols="30" rows="5"></textarea>
                            @error('specifications') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>

                    </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Assign</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>





</div>

