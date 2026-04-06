<div>
    {{-- <blockquote class="blockquote-reverse mt-20"> --}}
        <x-loading/>

        {{-- @if ($invoice->payments->count()>0)
        @else    
        <a href="" data-toggle="modal" data-target="#addInvoiceItemModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Item</a>
        @endif
        <br>
        <br> --}}
        <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead>
              <tr>
                <th class="th-sm">Item
                </th>
                <th class="th-sm">Qty
                </th>
                <th class="th-sm">Ccy
                </th>
                <th class="th-sm">Rate
                </th>
                <th class="th-sm">Subtotal
                </th>
                <th class="th-sm">Tax Amt
                </th>
                <th class="th-sm">Subtotal(Incl)
                </th>
              </tr>
            </thead>
            @if (isset($invoice_items))
            <tbody>
                @forelse ($invoice_items as $invoice_item)
              <tr>
                <td>
                    @if ($invoice_item->product)
                        <strong>{{$invoice_item->product ? $invoice_item->product->name : ""}} {{$invoice_item->product ? $invoice_item->product->identification_number : ""}} {{$invoice_item->inventory ? $invoice_item->inventory->serial_number : ""}}</strong>  
                    @elseif($invoice_item->trip)  
                        <a href="{{route('trips.show',$invoice_item->trip?->id)}}" target="_blank" style="color: blue"><strong>{{$invoice_item->trip ? $invoice_item->trip->trip_number : ""}}</strong></a>
                    @elseif($invoice_item->trip_transport_order)  
                        <a href="{{route('trip_transport_orders.show', $invoice_item->trip_transport_order->id)}}" target="_blank" style="color: blue"><strong>{{$invoice_item->trip_transport_order->transport_order ? $invoice_item->trip_transport_order->tto_number : ""}}</strong></a>  
                    @elseif($invoice_item->rental)  
                        <strong>{{$invoice_item->rental ? $invoice_item->rental->car_rental_number : ""}}</strong>  
                    @endif 
                  {{$invoice_item->description}}
                </td>
                <td>{{$invoice_item->qty}}</td>
                <td>{{$invoice_item->invoice->currency->name}}</td>
                <td>
                    @if ($invoice_item->amount)
                    {{$invoice_item->invoice->currency->symbol}}{{number_format($invoice_item->amount,2)}}        
                    @endif
                </td>
                <td>
                    @if ($invoice_item->subtotal)
                    {{$invoice_item->invoice->currency->symbol}}{{number_format($invoice_item->subtotal,2)}}
                    @endif
                </td>
                <td>
                    {{$invoice_item->invoice->currency->symbol}}{{number_format($invoice_item->tax_amount ? $invoice_item->tax_amount : 0,2)}}
                </td>
                <td>
                    @if ($invoice_item->subtotal_incl)
                    {{$invoice_item->invoice->currency->symbol}}{{number_format($invoice_item->subtotal_incl,2)}}
                    @endif
                </td>
              </tr>
            @empty
                <tr>
                <td colspan="7">
                    <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                        No Invoice Items Recorded....
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
                @if (isset($invoice_items))
                    {{ $invoice_items->links() }} 
                @endif 
            </ul>
        </nav>    
     
       
       
          <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="removeModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content bg-danger">
                    <div class="modal-body">
                       <center> <strong>Are you sure you want to delete this Invoice Item</strong> </center>
                    </div>
                    <form wire:submit.prevent="removeInvoiceItem()" >
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



        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="addInvoiceItemModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog" role="cargo">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Item <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="store()" >
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="country">Items<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="selectedItem" class="form-control" required>
                                        <option value="">Select Item</option>
                                            @foreach ($products as $product)
                                                <option value="{{$product->id}}">
                                                    <strong>{{$product->name}}</strong> {{$product->description ? "| ".$product->description : ""}}
                                                </option> 
                                            @endforeach
                                        </select>
                                        <small>  <a href="#" data-toggle="modal" data-target="#product_serviceModal"><i class="fa fa-plus-square-o"></i> New Product / Service</a></small> 
                                    @error('selectedItem') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                                
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                    <input type="number" class="form-control" wire:model.debounce.300ms="qty"  required >
                                    @error('qty') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="name">Rate<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount"  required >
                                    @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="subheading">Taxes</label>
                                    <select wire:model.debounce.300ms="selectedTax"  class="form-control">
                                        <option value="">Select Tax</option>
                                            @foreach ($tax_accounts as $tax)
                                               <option value="{{$tax->id}}">{{$tax->abbreviation}} {{$tax->rate ? $tax->rate."%" : ""}}</option> 
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

        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="product_serviceModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog  mw-100 w-50" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> New Product / Service<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="storeItem()" >
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="comment">Name<span class="required" style="color: red">*</span></label>
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
                                    <input type="checkbox" wire:model.debounce.300ms="sell"   class="line-style" disabled/>
                                    <label for="one" class="radio-label">Sell this?</label>
                                    @error('sell') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-10">
                                    <input type="checkbox" wire:model.debounce.300ms="buy"   class="line-style" />
                                    <label for="one" class="radio-label">Buy this?</label>
                                    @error('buy') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            @if (!is_null($sell) && $sell == True)
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="subheading">Income Accounts<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="income_account_id" class="form-control" required>
                                        <option value="">Select Income Account</option>
                                            @foreach ($income_accounts as $account)
                                            <option value="{{$account->id}}">{{$account->name}} </option> 
                                            @endforeach
                                        </select>
                                        <small><a href="{{ route('accounts.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Income Account</a></small> 
                                    @error('income_account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="name">Selling Price</label>
                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="sell_price" placeholder="Enter Selling Price">
                                    @error('sell_price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            @endif
                                  
                            @if (!is_null($buy) && $buy == True)
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="subheading">Expense Accounts<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="expense_account_id" class="form-control" required>
                                        <option value="">Select Expense Account</option>
                                            @foreach ($expense_accounts as $account)
                                            <option value="{{$account->id}}">{{$account->name}} </option> 
                                            @endforeach
                                        </select>
                                        <small><a href="{{ route('accounts.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Expense Account</a></small> 
                                    @error('expense_account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="name">Buying Price</label>
                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="buy_price" placeholder="Enter Buying Price">
                                    @error('buy_price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            @endif
                           
                           
                        </div>
                        <div class="form-group">
                            <label for="subheading">Sales Tax</label>
                            <select wire:model.debounce.300ms="tax_id" class="form-control">
                                <option value="">Select Tax</option>
                                    @foreach ($tax_accounts as $tax)
                                    <option value="{{$tax->id}}">{{$tax->abbreviation}} {{$tax->rate ? $tax->rate."%" : ""}}</option> 
                                    @endforeach
                                </select>
                                <small><a href="{{ route('taxes.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small> 
                            @error('tax_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
     
        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="editInvoiceItemModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog" role="cargo">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Item<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="update()" >
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-5">
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
                                    <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                    <input type="number" class="form-control" wire:model.debounce.300ms="qty"  required >
                                    @error('qty') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="name">Rate<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount"  required >
                                    @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="subheading">Taxes</label>
                                    <select wire:model.debounce.300ms="selectedTax"  class="form-control">
                                        <option value="">Select Tax</option>
                                            @foreach ($tax_accounts as $tax)
                                               <option value="{{$tax->id}}">{{$tax->abbreviation}} {{$tax->rate ? $tax->rate."%" : ""}}</option> 
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
   
</div>
