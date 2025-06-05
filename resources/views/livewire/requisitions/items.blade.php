<div>
    {{-- <blockquote class="blockquote-reverse mt-20"> --}}
        <x-loading/>
        <a href="" data-toggle="modal" data-target="#requisition_itemModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Requisition Item</a>
       
        <br>
        <br>
        <table id="requisition_itemsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead >
                <th class="th-sm">Item
                </th>
                <th class="th-sm">Qty
                </th>
                <th class="th-sm">Currency
                </th>
                <th class="th-sm">Amount
                </th>
                <th class="th-sm">Subtotal
                </th>
                <th class="th-sm">Actions
                </th>
              </tr>
            </thead>
            <tbody>
                @if ($requisition_items->count()>0)
                @foreach ($requisition_items as $requisition_item)
              <tr>
                  <td>
                    @if ($requisition_item->expense)
                            {{ $requisition_item->expense ? $requisition_item->expense->name : ""}}
                    @elseif($requisition_item->product)
                    {{ $requisition_item->product->brand ? $requisition_item->product->brand->name : ""}} {{ $requisition_item->product ? $requisition_item->product->name : ""}}
                    @endif
                    </td>
                  <td>{{ $requisition_item->qty}}</td>
                  <td>{{ $requisition_item->requisition->currency ? $requisition_item->requisition->currency->name : ""}}</td>
                  <td>
                    @if ($requisition_item->amount)
                        {{ $requisition_item->requisition->currency ? $requisition_item->requisition->currency->symbol : ""}}{{ number_format($requisition_item->amount,2)}}
                    @endif
                  </td>
                  <td>
                    @if ($requisition_item->subtotal)
                   {{ $requisition_item->requisition->currency ? $requisition_item->requisition->currency->symbol : ""}}{{ number_format($requisition_item->subtotal,2)}}
                    @endif
                  </td>
              
                <td class="w-10 line-height-35 table-dropdown">
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-bars"></i>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            @if ($requisition->authorization == "pending" || $requisition->authorization == "rejected")
                            <li><a href="#" wire:click="edit({{$requisition_item->id}})"><i class="fa fa-edit color-success"></i>Edit</a></li>
                            <li><a href="#" wire:click="removeShow({{$requisition_item->id}})"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                            @endif
                           
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

          <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="requisition_itemDeleteModal"  tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content bg-danger">
                    <div class="modal-body">
                       <center> <strong>Are you sure you want to delete this Requisition Item?</strong> </center>
                    </div>
                    <form wire:submit.prevent="delete()" >
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
        
         
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="requisition_itemModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="border">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Requisition Item(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    
                        <div class="row">
                            
                            <div class="col-md-7">
                                <div class="form-group">
                                    <label for="title">Expenses<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.defer="expense_id.0" required>
                                        <option value="">Select Expense</option>
                                        @foreach ($expenses as $expense)
                                            <option value="{{ $expense->id }}">{{ $expense->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('expense_id.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Qty<span class="required" style="color: red">*</span></label>
                                    <input type="number"  wire:model.defer="qty.0" class="form-control" required/>
                                    @error('qty.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Amount<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" wire:model.defer="amount.0" class="form-control" required/>
                                    @error('amount.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                    </div>
                    <div class="row">
                       
                    </div>
                        @foreach ($inputs as $key => $value)
                        <div class="row">
                          
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Expenses<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.defer="expense_id.{{ $value }}" required>
                                        <option value="">Select Expense</option>
                                        @foreach ($expenses as $expense)
                                            <option value="{{ $expense->id }}">{{ $expense->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('expense_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="">Qty<span class="required" style="color: red">*</span></label>
                                    <input type="number"  wire:model.defer="qty.{{ $value }}" class="form-control" required/>
                                    @error('qty.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                             
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="">Amount<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" wire:model.defer="amount.{{ $value }}" class="form-control" required/>
                                    @error('amount.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
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
                                    <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Item</button>
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="requisition_itemEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="border">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Requisition Item <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                        <div class="row">
                            <div class="col-md-7">
                            <div class="form-group">
                                <label for="title">Expenses<span class="required" style="color: red">*</span></label>
                                <select class="form-control" wire:model.defer="expense_id" required>
                                    <option value="">Select Expense</option>
                                    @foreach ($expenses as $expense)
                                        <option value="{{ $expense->id }}">{{ $expense->name }}</option>
                                    @endforeach
                                </select>
                                @error('expense_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="">Qty<span class="required" style="color: red">*</span></label>
                                <input type="number"  wire:model.defer="qty" class="form-control" required/>
                                @error('qty') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                         
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="">Amount<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" wire:model.defer="amount" class="form-control" required/>
                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
