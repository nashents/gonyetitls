<div>
                    <a href="" data-toggle="modal" data-target="#ticket_requestModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Item</a>
                    <br>
                    <br>
                    <br>
                    <table id="requestsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                        <thead >
                         <tr>
                            <th class="th-sm">Item
                            </th>
                            <th class="th-sm">Qty
                            </th>
                            <th class="th-sm">Measurement
                            </th>
                            <th class="th-sm">Actions
                            </th>
                          </tr>
                        </thead>
                        @if ($ticket_requests->count()>0)
                        <tbody>
                           @foreach ($ticket_requests as  $ticket_request)
                            <tr>
                                <td>
                                    @if ($ticket_request->product)
                                        {{$ticket_request->product ? $ticket_request->product->name : ""}} {{$ticket_request->product->brand ? $ticket_request->product->brand->name : ""}} {{$ticket_request->product->identification_number ? $ticket_request->product->identification_number : ""}}
                                    @endif
                                </td>  
                                <td>
                                    {{$ticket_request->qty}} 
                                </td>
                                <td>
                                    @php
                                        $uom = App\Models\Measurement::find($ticket_request->measurement_id)
                                    @endphp
                                    {{$uom ? $uom->name : ""}}</td>
                                <td class="w-10 line-height-35 table-dropdown">
                                    <div class="dropdown">
                                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fa fa-bars"></i>
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a href="#"  wire:click="edit({{$ticket_request->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                            <li><a href="#" data-toggle="modal" data-target="#ticket_requestDeleteModal{{ $ticket_request->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                        </ul>
                                    </div>
                                </td> 
                            </tr>
                            @endforeach
                        </tbody>
                        @else
                        <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                     @endif
                      </table>

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
                                                <label for="horse">Products<span class="required" style="color: red">*</span></label>
                                                <input type="text" wire:model.debounce.300ms="search_products" placeholder="Search products..." class="form-control" >
                                               <select wire:model.debounce.300ms="selectedProduct" class="form-control" required size="4">
                                                   <option value="" selected>Select Product</option>
                                                    @foreach ($products as $product)
                                                        <option value="{{$product->id}}">{{$product->product_number}} {{$product->brand ? $product->brand->name : ""}} {{$product->name}}</option>
                                                    @endforeach
                                               </select>
                                                @error('selectedProduct') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="purchase_date">Quantity<span class="required" style="color: red">*</span></label>
                                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="qty" placeholder="Enter Quantity Requested" required>
                                                @error('qty') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label for="horse">Unit of measure<span class="required" style="color: red">*</span></label>
                                                <select wire:model.debounce.300ms="measurement_id" class="form-control" required >
                                                   <option value="" selected>Select Unit of Measure</option>
                                                    @foreach ($measurements as $measurement)
                                                        <option value="{{$measurement->id}}">{{$measurement->name}}</option>
                                                    @endforeach
                                                </select>
                                                @error('measurement_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label for="products">Products<span class="required" style="color: red">*</span></label>
                                                <input type="text" wire:model.debounce.300ms="search_products" placeholder="Search products..." class="form-control" >
                                                <select wire:model.debounce.300ms="selectedProduct" class="form-control" required size="4">
                                                    <option value="" selected>Select Product</option>
                                                        @foreach ($products as $product)
                                                            <option value="{{$product->id}}">{{$product->product_number}} {{$product->brand ? $product->brand->name : ""}} {{$product->name}}</option>
                                                        @endforeach
                                                </select>
                                                @error('selectedProduct') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="qty">Quantity<span class="required" style="color: red">*</span></label>
                                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="qty" placeholder="Enter Quantity Requested" required>
                                                @error('qty') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label for="measurements">Unit of measure<span class="required" style="color: red">*</span></label>
                                                <select wire:model.debounce.300ms="measurement_id" class="form-control" required >
                                                <option value="" selected>Select Unit of Measure</option>
                                                    @foreach ($measurements as $measurement)
                                                        <option value="{{$measurement->id}}">{{$measurement->name}}</option>
                                                    @endforeach
                                                </select>
                                                @error('measurement_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
