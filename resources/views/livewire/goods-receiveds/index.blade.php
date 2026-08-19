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
                                  <select wire:model.debounce.300ms="goods_received_filter" class="form-control" aria-label="..." >
                                    <option value="created_at">Created At</option>
                                    <option value="date">Received At</option>
                                    <option value="delivery_date">Delivered At</option>
                                </select>
                                    </div>

                                    <!-- /input-group -->
                                </div>
                            
                                <div class="col-lg-2" style="margin-right: 7px; margin-left:-15px;">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                From
                                </span>
                                <input type="date" wire:model.debounce.300ms="from"  class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                <div class="col-lg-2" style="margin-left: 7px">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                To
                                </span>
                                <input type="date" wire:model.debounce.300ms="to"  class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                          
                               
                               
                                <!-- /input-group -->
                            </div>
                                <a href="#" data-toggle="modal" data-target="#goods_receivedModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Goods Received Voucher</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search GRVs...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">GRV#
                                    </th>
                                    <th class="th-sm">ReceivedBy
                                    </th>
                                    <th class="th-sm">Vendor
                                    </th>
                                    <th class="th-sm">Condition
                                    </th>
                                    <th class="th-sm">Date
                                    </th>
                                    <th class="th-sm">Delivery Details
                                    </th>
                                    <th class="th-sm">Item(s)
                                    </th>
                                    <th class="th-sm">Total
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($goods_receiveds))
                                <tbody>
                                    @forelse ($goods_receiveds as $goods_received)
                                  <tr>
                                    <td>
                                        {{ucfirst($goods_received->goods_received_number)}}
                                        @php
                                            $po = $goods_received->purchase;
                                        @endphp
                                        @if ($po)
                                            <br>
                                            <small class="text-muted">
                                               <strong>PurchaseOrder#:</strong> {{$po->purchase_number}}
                                            </small>
                                        @endif
                                        @if ($this->sageEnabled)
                                            @php $sm = $goods_received->sageMapping; $ss = optional($sm)->sync_status; $approved = $goods_received->authorization === 'approved'; @endphp
                                            <br>
                                            <small class="badge bg-{{ $sm ? ($ss === 'synced' ? 'success' : ($ss === 'failed' ? 'danger' : ($ss === 'requires_attention' ? 'warning' : 'secondary'))) : 'secondary' }}"
                                                   title="{{ optional($sm)->last_error ?? '' }}">Sage Receipt: {{ $sm ? ucwords(str_replace('_',' ', $ss)) : 'Not synced' }}</small>
                                            @if ($approved)
                                                @if ($ss === 'synced')
                                                    <a href="#" wire:click.prevent="syncGoodsReceivedToSage({{ $goods_received->id }})" wire:loading.attr="disabled" title="Re-sync to Sage"><i class="fa fa-refresh"></i></a>
                                                @elseif (in_array($ss, ['failed','requires_attention']))
                                                    <a href="#" wire:click.prevent="syncGoodsReceivedToSage({{ $goods_received->id }})" wire:loading.attr="disabled" style="color:#d9534f" title="Retry Sage sync"><i class="fa fa-refresh"></i> Retry</a>
                                                @else
                                                    <a href="#" wire:click.prevent="syncGoodsReceivedToSage({{ $goods_received->id }})" wire:loading.attr="disabled" title="Sync to Sage"><i class="fa fa-cloud-upload"></i> Sync</a>
                                                @endif
                                            @endif
                                        @endif
                                    </td>
                                    
                                    <td>{{$goods_received->employee ? $goods_received->employee->name : ""}} {{$goods_received->employee ? $goods_received->employee->surname : ""}}</td>
                                    <td>{{$goods_received->vendor ? $goods_received->vendor->name : ""}}</td>
                                    <td>{{$goods_received->condition}}</td>
                                    <td>{{$goods_received->date}}</td>
                                    <td>
                                        <small class="text-muted">
                                            @if ($goods_received->delivery_number)
                                                <strong>Delivery#:</strong> {{$goods_received->delivery_number}}
                                            @endif
                                            @if ($goods_received->delivery_date) <br>
                                                <strong>Date:</strong> {{$goods_received->delivery_date}}
                                            @endif
                                            @if ($goods_received->driver_name) <br>
                                                <strong>Driver:</strong> {{$goods_received->driver_name}}
                                            @endif
                                        </small>
                                    </td>
                                    <td>
                                        @if ($department == "inventory")
                                            {{$goods_received->inventories->count()}}
                                        @elseif($department == "asset")
                                           {{$goods_received->assets->count()}}     
                                        @elseif($department == "tyre")
                                            {{$goods_received->tyres->count()}} 
                                        @endif 
                                    </td>
                                    <td></td>
                                   <td>
                                        <span class="badge bg-{{$goods_received->status == 1 ? "warning" : "success"}}">{{$goods_received->status == 1 ? "Open" : "Closed"}}</span>
                                        <br>
                                        <span class="badge bg-{{$goods_received->authorization === 'approved' ? 'success' : ($goods_received->authorization === 'rejected' ? 'danger' : 'warning')}}">{{ucfirst($goods_received->authorization)}}</span>
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('goods_receiveds.show', $goods_received->id) }}" ><i class="fa fa-eye color-default"></i> View</a></li>
                                                <li><a href="{{ route('goods_receiveds.preview', $goods_received->id) }}" ><i class="fa fa-file color-warning"></i> Preview</a></li>
                                                <li><a href="#"  wire:click="edit({{$goods_received->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" wire:click="showClose({{$goods_received->id}})"  ><i class="fas fa-check color-success"></i> Mark as closed</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#goods_receivedDeleteModal{{ $goods_received->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('goods_receiveds.delete')
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="10">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Goods Received Found ....
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
                                    @if (isset($goods_receiveds))
                                        {{ $goods_receiveds->links() }} 
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

          <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="grvCloseModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fas fa-check"></i> Mark GRV as closed<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="closeGRV()" >
                    <div class="modal-body">
                       <p>Are you sure you want to mark this GRV
                        @if ($goods_received_number)
                            {{$goods_received_number}}
                        @endif
                        as closed?
                       </p>
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


    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="goods_receivedModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Goods Received Voucher <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                      <div class="mb-20 mt-20">
                        <input type="checkbox" wire:model.debounce.300ms="attach"   class="line-style"  />
                        <label for="one" class="radio-label">Attach a purchase order to this GRVoucher. </label>
                        @error('attach') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                    @if(isset($attach) && $attach == True)
                        <div class="form-group">
                            <label for="name">Purchase Orders</label>
                            <input type="text" wire:model.debounce.300ms="searchPurchase" placeholder="Search purchase orders" class="form-control">
                            <select class="form-control" wire:model.debounce.300ms="purchase_id" size="4">
                                <option value="">Select Purchase Order</option>
                                @foreach ($purchases as $purchase)
                                    <option value="{{$purchase->id}}">{{$purchase->purchase_number}} | {{ $purchase->date }} | {{$purchase->vendor ? $purchase->vendor->name : ""}} | {{ $purchase->currency ? $purchase->currency->name : "" }} {{ $purchase->currency ? $purchase->currency->symbol : "" }}{{number_format($purchase->total,2)}}</option>
                                @endforeach
                            </select>
                            @error('purchase_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-md-4">
                                <div class="form-group">
                                <label for="name">Vendors<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="vendor_id">
                                    <option value="">Select Vendor</option>
                                    @foreach ($vendors as $vendor)
                                        <option value="{{$vendor->id}}">{{$vendor->name}}</option>
                                    @endforeach
                                </select>
                                @error('vendor_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                                <div class="form-group">
                                <label for="name">Received By<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="employee_id" >
                                    <option value="">Select Employee</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{$employee->id}}">{{$employee->name}} {{$employee->surname}}</option>
                                    @endforeach
                                </select>
                                @error('employee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Receiving Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Receiving Date" required>
                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                              <div class="form-group">
                                <label for="name">Delivery#</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="delivery_number" placeholder="Enter Delivery Number">
                                @error('delivery_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                              <div class="form-group">
                                <label for="name">Driver FullName</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="driver_name" placeholder="Enter Driver Name & Surname">
                                @error('driver_name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                              <div class="form-group">
                                <label for="name">Delivery Date</label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="delivery_date" placeholder="Enter Receiving Date">
                                @error('delivery_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="name">Condition</label>
                               <select class="form-control" wire:model.debounce.300ms="condition" >
                                    <option value="">Select Option</option>
                                    <option value="New">New</option>
                                    <option value="Second Hand">Second Hand</option>
                                </select>
                                @error('condition') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="name">Comments</label>
                                <textarea class="form-control" wire:model.debounce.300ms="comments" cols="30" rows="4"></textarea>
                                @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="goods_receivedEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Goods Received Voucher<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >

                <div class="modal-body">
                    <div class="mb-20 mt-20">
                        <input type="checkbox" wire:model.debounce.300ms="attach"   class="line-style"  />
                        <label for="one" class="radio-label">Attach a purchase order to this GRVoucher. </label>
                        @error('attach') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                    @if(isset($attach) && $attach == True)
                        <div class="form-group">
                            <label for="name">Purchase Orders</label>
                            <select class="form-control" wire:model.debounce.300ms="purchase_id" size="4">
                                <option value="">Select Purchase Order</option>
                                @foreach ($purchases as $purchase)
                                    <option value="{{$purchase->id}}">{{$purchase->purchase_number}} | {{ $purchase->date }} | {{$purchase->vendor ? $purchase->vendor->name : ""}} | {{ $purchase->currency ? $purchase->currency->name : "" }} {{ $purchase->currency ? $purchase->currency->symbol : "" }}{{number_format($purchase->total,2)}}</option>
                                @endforeach
                            </select>
                            @error('purchase_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    @endif
                   <div class="row">
                        <div class="col-md-4">
                                <div class="form-group">
                                <label for="name">Vendors<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="vendor_id" required>
                                    <option value="">Select Vendor</option>
                                    @foreach ($vendors as $vendor)
                                        <option value="{{$vendor->id}}">{{$vendor->name}}</option>
                                    @endforeach
                                </select>
                                @error('vendor_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                                <div class="form-group">
                                <label for="name">Received By<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.debounce.300ms="employee_id" >
                                    <option value="">Select Employee</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{$employee->id}}">{{$employee->name}} {{$employee->surname}}</option>
                                    @endforeach
                                </select>
                                @error('employee_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Receiving Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Receiving Date" required>
                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                              <div class="form-group">
                                <label for="name">Delivery#</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="delivery_number" placeholder="Enter Delivery Number">
                                @error('delivery_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                              <div class="form-group">
                                <label for="name">Driver FullName</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="driver_name" placeholder="Enter Driver Name & Surname">
                                @error('driver_name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                              <div class="form-group">
                                <label for="name">Delivery Date</label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="delivery_date" placeholder="Enter Receiving Date">
                                @error('delivery_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="name">Condition</label>
                               <select class="form-control" wire:model.debounce.300ms="condition" >
                                    <option value="">Select Option</option>
                                    <option value="New">New</option>
                                    <option value="Second Hand">Second Hand</option>
                                </select>
                                @error('condition') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                             <div class="form-group">
                                <label for="name">Comments</label>
                                <textarea class="form-control" wire:model.debounce.300ms="comments" cols="30" rows="4"></textarea>
                                @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
                    </div>
                    <!-- /.btn-goods_received -->
                </div>
            </form>
            </div>
        </div>
    </div>



</div>

