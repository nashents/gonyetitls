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
                                <a href="{{route('inventories.create')}}"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Inventory</a>
                                <a href="" data-toggle="modal" data-target="#inventoryImportModal" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-upload"></i>Import</a>
                            </div>

                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search inventory...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Product
                                    </th>
                                    <th class="th-sm">Location
                                    </th>
                                    <th class="th-sm">Qty
                                    </th>
                                    <th class="th-sm">Capacity & Bal
                                    </th>
                                    <th class="th-sm">Date
                                    </th>
                                    <th class="th-sm">Ccy
                                    </th>
                                    <th class="th-sm">Rate
                                    </th>
                                    <th class="th-sm">Tax
                                    </th>
                                    <th class="th-sm">Cost
                                    </th>
                                    <th class="th-sm">Total
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($inventories))
                                <tbody>
                                    @forelse ($inventories as $inventory)
                                  <tr> 
                                    <td>
                                        @if ($inventory->product)
                                            {{$inventory->product->brand ? $inventory->product->brand->name : ""}} {{$inventory->product ? $inventory->product->name : ""}}
                                            @if ($inventory->product->identification_number)
                                            <br>
                                            {{$inventory->product->identification_number ? "ID#: ".$inventory->product->identification_number : ""}}
                                            @endif
                                        @endif
                                      
                                        @if ($inventory->serial_number)
                                            <br>
                                            <small><strong>S#: </strong> {{$inventory->serial_number}}</small>
                                        @endif
                                    </td>    
                                    <td>
                                        @if ($inventory->store)
                                             <strong>Store:</strong>  {{$inventory->store ? $inventory->store->name : ""}}
                                             <br>
                                        @endif
                                        @if ($inventory->product)
                                            @if ($inventory->product->category)
                                                <strong>Category:</strong> {{$inventory->product->category ? $inventory->product->category->name : ""}}  {{$inventory->product->category_value ? $inventory->product->category_value->name : ""}} 
                                                <br>
                                            @endif
                                        @endif
                                      
                                        @if ($inventory->rack)
                                          
                                              <strong>Rack:</strong> {{$inventory->rack ? $inventory->rack->name : ""}} {{$inventory->rack ? $inventory->rack->rack_number : ""}}
                                                <br>
                                        @endif
                                        @if ($inventory->bin)
                                              <strong>Bin:</strong> {{$inventory->bin ? $inventory->bin->name : ""}} {{$inventory->bin ? $inventory->bin->bin_number : ""}} 
                                               <br>
                                        @endif
                                       
                                    </td>
                                    <td>{{$inventory->qty}}</td>
                                    <td>
                                        @if ($inventory->weight)
                                            <strong>Capacity: </strong> {{$inventory->weight}}
                                        @endif
                                        @if ($inventory->balance)
                                            <strong>Bal: </strong> {{$inventory->balance ? $inventory->balance : ""}}  {{$inventory->measurement ? $inventory->measurement : $inventory->product->unit_of_measure}}
                                        @endif
                                         
                                    </td>
                                    <td>
                                        @if ($inventory->purchase_date)
                                            {{Carbon\Carbon::parse($inventory->purchase_date)->format('Y-m-d')}}        
                                        @endif
                                    </td>
                                    <td>{{$inventory->currency ? $inventory->currency->name : ""}}</td>
                                    <td>
                                       {{$inventory->currency ? $inventory->currency->symbol : ""}}{{number_format($inventory->amount ? $inventory->amount : 0,2)}}  
                                    </td>
                                    <td>
                                        {{$inventory->currency ? $inventory->currency->symbol : ""}}{{number_format($inventory->tax_amount ? $inventory->tax_amount : 0,2)}}  
                                    </td>
                                    <td>
                                        {{$inventory->currency ? $inventory->currency->symbol : ""}}{{number_format($inventory->cost ? $inventory->cost : 0,2)}}  
                                    </td>
                                    <td>
                                        {{$inventory->currency ? $inventory->currency->symbol : ""}}{{number_format($inventory->total ? $inventory->total: 0,2)}}
                                        @if (Auth::user()->employee->company->currency_id != $inventory->currency_id)
                                            <br>
                                            <small>
                                                <strong>Exc Rate:</strong> {{number_format($inventory->exchange_rate,2)}} <br>
                                                <strong>Exc Total:</strong> {{Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->name : ""}} {{Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->symbol : ""}}{{number_format($inventory->exchange_amount,2)}}
                                            </small>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-{{$inventory->status == 1 ? "success" : "danger"}}">{{$inventory->status == 1 ? "Instore" : "Out Of stock"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('inventories.show',$inventory->id )}}"  ><i class="fa fa-eye color-default"></i> View</a></li>
                                                <li><a href="{{route('inventories.edit',$inventory->id )}}"  ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" wire:click="showDispose({{$inventory->id}})"  ><i class="fa fa-times color-warning"></i> Dispose</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#inventoryDeleteModal{{ $inventory->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('inventories.delete')
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="10">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Inventory Item(s) Found ....
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
                                    @if (isset($inventories))
                                        {{ $inventories->links() }} 
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="disposeModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Dispose Item <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="dispose()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Dispose Item<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="dispose" class="form-control" required >
                                   <option value="">Select Option</option>
                                   <option value="1">Yes</option>
                                   <option value="0">No</option>
                               </select>
                                @error('dispose') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Date" required />
                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="name">Reason<span class="required" style="color: red">*</span></label>
                      <textarea class="form-control" wire:model.debounce.300ms="comments" cols="30" rows="3" required></textarea>
                        @error('comments') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="inventoryImportModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-upload"></i>Import Inventory Product(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="importInventory()" enctype="multipart/form-data">
                  
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Upload Inventory Products Excel File</label>
                        <input type="file" class="form-control" wire:model.debounce.300ms="importFile" placeholder="Upload Loading Points File" >
                        @error('importFile') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button  type="submit"  class="btn bg-success btn-wide btn-rounded"><i class="fa fa-upload"></i>Upload</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>



</div>

