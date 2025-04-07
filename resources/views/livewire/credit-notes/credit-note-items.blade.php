<div>
    {{-- <blockquote class="blockquote-reverse mt-20"> --}}
        <x-loading/> 
        @if ($credit_note->authorization != "approved")
        <a href="" data-toggle="modal" data-target="#addcredit_noteItemModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Credit Note Item</a>
        <br>
        @endif
      
        <br>
        <table id="credit_note_itemsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead>
              <tr>
                <th class="th-sm">CreditNote#
                </th>
                <th class="th-sm">Item
                </th>
                <th class="th-sm">Description
                </th>
                <th class="th-sm">Qty
                </th>
                <th class="th-sm">Currency
                </th>
                <th class="th-sm">Rate
                </th>
                <th class="th-sm">Subtotal
                </th>
                <th class="th-sm">Action
                </th>
              </tr>
            </thead>
            @if ($credit_note_items->count()>0)
            <tbody>
                @foreach ($credit_note_items as $credit_note_item)
              <tr>
                <td>{{$credit_note_item->credit_note->credit_note_number}}</td>
                <td>{{$credit_note_item->invoice_product ? $credit_note_item->invoice_product->name : ""}}</td>
                <td>{{$credit_note_item->description}}</td>
                <td>{{$credit_note_item->qty}}</td>
                <td>
                    @if ($credit_note_item->credit_note)
                    {{$credit_note_item->credit_note->currency ? $credit_note_item->credit_note->currency->name : ""}}
                    @endif
                </td>
                <td>
                    @if ($credit_note_item->credit_note)
                        @if ($credit_note_item->amount)
                        {{$credit_note_item->credit_note->currency ? $credit_note_item->credit_note->currency->symbol : ""}}{{number_format($credit_note_item->amount,2)}}        
                        @endif
                    @endif
                </td>
                <td>
                    @if ($credit_note_item->credit_note)
                        @if ($credit_note_item->subtotal)
                        {{$credit_note_item->credit_note->currency ? $credit_note_item->credit_note->currency->symbol : ""}}{{number_format($credit_note_item->subtotal,2)}}
                        @endif
                    @endif
                </td>
                <td class="w-10 line-height-35 table-dropdown">
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-bars"></i>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                           
                                @if ($credit_note->authorization != "approved")
                                <li><a href="#" wire:click="edit({{$credit_note_item->id }})"><i class="fa fa-edit color-success"></i>Edit</a></li>
                                <li><a href="#" wire:click="removeShow({{ $credit_note_item->id }})"><i class="fa fa-trash color-danger"></i>Delete</a></li>
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
    
       
       
          <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="removeModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content bg-danger">
                    <div class="modal-body">
                       <center> <strong>Are you sure you want to delete this Credit Note Item</strong> </center>
                    </div>
                    <form wire:submit.prevent="removeCreditNoteItem()" >
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


        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="addcredit_noteItemModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog" role="cargo">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fa fa-file-invoice"></i> Add Credit Note Item(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="store()" >
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="country">Item<span class="required" style="color: red">*</span></label>
                                      <input type="text" wire:model.debounce.300ms="item.0" class="form-control">
                                    @error('item.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Description<span class="required" style="color: red">*</span></label>
                                   <textarea wire:model.debounce.300ms="description.0" class="form-control" cols="30" rows="4" placeholder="Enter Description"></textarea>
                                    @error('description.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                    <input type="number" class="form-control" wire:model.debounce.300ms="qty.0" placeholder="Enter Qty" required >
                                    @error('qty.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Amount<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount.0" placeholder="Enter Amount" required >
                                    @error('amount.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                          
                        </div>
                           
                        @foreach ($inputs as $key => $value)
                            <div class="row">  
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="country">Item<span class="required" style="color: red">*</span></label>
                                          <input type="text" wire:model.debounce.300ms="item.{{ $value }}" class="form-control">
                                        @error('item.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="name">Description<span class="required" style="color: red">*</span></label>
                                           <textarea wire:model.debounce.300ms="description.{{ $value }}" class="form-control" cols="30" rows="4" placeholder="Enter Description"></textarea>
                                            @error('description.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                            <input type="number" class="form-control" wire:model.debounce.300ms="qty.{{ $value }}" placeholder="Enter Qty" required >
                                            @error('qty.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="name">Amount<span class="required" style="color: red">*</span></label>
                                            <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount.{{ $value }}" placeholder="Enter Amount" required >
                                            @error('amount.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label for=""></label>
                                            <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                                        </div>
                                    </div>
                                </div>
                                
                        @endforeach
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i>Item</button>
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

        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="editCredit_noteItemModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog" role="cargo">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fa fa-file-invoice"></i> Edit Credit Note Item <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="update()" >
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="country">Item<span class="required" style="color: red">*</span></label>
                                      <input type="text" wire:model.debounce.300ms="item" class="form-control">
                                    @error('item') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Description<span class="required" style="color: red">*</span></label>
                                   <textarea wire:model.debounce.300ms="description" class="form-control" cols="30" rows="4" placeholder="Enter Description"></textarea>
                                    @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                    <input type="number" class="form-control" wire:model.debounce.300ms="qty" placeholder="Enter Qty" required >
                                    @error('qty') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Amount<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount" placeholder="Enter Amount" required >
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
