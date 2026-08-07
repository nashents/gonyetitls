<div>         @if (isset($employee_ids) || $ticket->booking->vendor)
                @if ((in_array($employee->id, $employee_ids)) || ($user->id == $ticket->booking->user_id && $ticket->booking->vendor) )
                    <a href="" data-toggle="modal" data-target="#ticket_requestModal" class="btn btn-default mb-10"><i class="fa fa-plus-square-o"></i>Request Items</a>
                  @endif
             @endif
                    <br>
                    <table class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                        <thead >
                         <tr>
                            <th class="th-sm">RequestedBy
                            </th>
                            <th class="th-sm">Item
                            </th>
                            <th class="th-sm">Quantity
                            </th>
                            <th class="th-sm">Measurement
                            </th>
                            @if (isset($employee_ids) || $ticket->booking->vendor)
                            @if ((in_array($employee->id, $employee_ids)) || ($user->id == $ticket->booking->user_id && $ticket->booking->vendor) )
                            <th class="th-sm">Actions
                            </th>
                            @endif
                            @endif
                          </tr>
                        </thead>
                        @if (isset($ticket_requests))
                        <tbody>
                           @forelse ($ticket_requests as  $ticket_request)
                            <tr>
                                <td>
                                    @if ($ticket_request->user)
                                         {{$ticket_request->user ? $ticket_request->user->name  : "" }} {{$ticket_request->user ? $ticket_request->user->surname  : "" }} <small>{{$ticket_request->user->employee->job_title ? $ticket_request->user->employee->job_title->name  : "" }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if ($ticket_request->product)
                                        {{$ticket_request->product ? $ticket_request->product->name : ""}} {{$ticket_request->product->brand ? $ticket_request->product->brand->name : ""}} {{$ticket_request->product->identification_number ? $ticket_request->product->identification_number : ""}}
                                    @endif
                                    @if ($ticket_request->product_name)
                                        {{$ticket_request->product_name}}
                                    @endif
                                </td>  
                                <td>
                                    {{$ticket_request->qty}} 
                                </td>
                                <td>{{$ticket_request->measurement}}</td>
                                @if (isset($employee_ids) || $ticket->booking->vendor)
                                @if ((in_array($employee->id, $employee_ids)) || ($user->id == $ticket->booking->user_id && $ticket->booking->vendor) )
                                <td class="w-10 line-height-35 table-dropdown">
                                    <div class="dropdown">
                                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fa fa-bars"></i>
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a href="#"  wire:click="edit({{$ticket_request->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                            <li><a href="#" wire:click="delete({{$ticket_request->id}})" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                        </ul>
                                    </div>
                                </td> 
                                @endif
                                @endif
                            </tr>
                            @empty
                            <tr>
                            <td colspan="5">
                                <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                    No Requested Items Found ....
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
                            @if (isset($ticket_requests))
                                @if ($ticket_requests->count()>0)
                                    {{ $ticket_requests->links() }} 
                                @endif
                            @endif 
                        </ul>
                    </nav>

                    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="ticket_requestModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
                        <div class="modal-dialog mw-100 w-90" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Request Item(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                                </div>
                                <form wire:submit.prevent="addProducts()" >
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label for="horse">Products</label>
                                                <input type="text" wire:model.debounce.300ms="search_products" placeholder="Search products..." class="form-control" >
                                               <select wire:model.debounce.300ms="selectedProduct" class="form-control"  size="4">
                                                   <option value="" selected>Select Product</option>
                                                    @foreach ($products as $product)
                                                        <option value="{{$product->id}}">{{$product->name}} {{$product->brand ? $product->brand->name : ""}} {{$product->identification_number ? "Part/Model#: ".$product->identification_number : ""}}</option>
                                                    @endforeach
                                               </select>
                                                @error('selectedProduct') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="purchase_date">New product name / description</label>
                                                <textarea class="form-control" wire:model.debounce.300ms="product_name" placeholder="New product name / description if you can`t find what you want in the product list" cols="30" rows="2"></textarea>
                                                @error('product_name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="purchase_date">Qty<span class="required" style="color: red">*</span></label>
                                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="qty" placeholder="Enter Qty Requested" required>
                                                @error('qty') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="horse">Unit of measure (UOM)<span class="required" style="color: red">*</span></label>
                                                <select wire:model.debounce.300ms="measurement" class="form-control" required >
                                                   <option value="" selected>Select Unit of Measure</option>
                                                   @foreach ($units_of_measures as $uom)
                                                       <option value="{{ $uom->name }}">{{ $uom->name }} {{ $uom->abbreviation ? "(".$uom->abbreviation.")" : "" }}</option>
                                                   @endforeach
                                                </select>
                                                @error('measurement') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                      <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="ticket_requestEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
                        <div class="modal-dialog mw-100 w-90" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Edit Requested Item <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                                </div>
                                <form wire:submit.prevent="update()" >
                                    <div class="modal-body">
                                        <div class="row">
                                             <div class="col-md-5">
                                            <div class="form-group">
                                                <label for="products">Products<span class="required" style="color: red">*</span></label>
                                                <input type="text" wire:model.debounce.300ms="search_products" placeholder="Search products..." class="form-control" >
                                                <select wire:model.debounce.300ms="selectedProduct" class="form-control" required size="4">
                                                    <option value="" selected>Select Product</option>
                                                        @foreach ($products as $product)
                                                            <option value="{{$product->id}}">{{$product->name}} {{$product->brand ? $product->brand->name : ""}} {{$product->identification_number ? "Part/Model#: ".$product->identification_number : ""}}</option>
                                                        @endforeach
                                                </select>
                                                @error('selectedProduct') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                          <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="purchase_date">New product name / description</label>
                                                <textarea class="form-control" wire:model.debounce.300ms="product_name" placeholder="New product name / description if you can`t find what you want in the product list" cols="30" rows="2"></textarea>
                                                @error('product_name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="qty">Qty<span class="required" style="color: red">*</span></label>
                                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="qty" placeholder="Enter Qty Requested" required>
                                                @error('qty') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="horse">Unit of measure (UOM)<span class="required" style="color: red">*</span></label>
                                                <select wire:model.debounce.300ms="measurement" class="form-control" required >
                                                   <option value="" selected>Select Unit of Measure</option>
                                                   @foreach ($units_of_measures as $uom)
                                                       <option value="{{ $uom->name }}">{{ $uom->name }} {{ $uom->abbreviation ? "(".$uom->abbreviation.")" : "" }}</option>
                                                   @endforeach
                                                </select>
                                                @error('measurement') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                    <div data-backdrop="static" data-keyboard="false" class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content bg-danger">
                                <div class="modal-body">
                                <center> <strong>Are you sure you want to delete this Requested Item?</strong> </center>
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

</div>
