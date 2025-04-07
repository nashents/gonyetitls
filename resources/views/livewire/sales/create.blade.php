<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>New Sale</h5>
                            </div>
                        </div>
                        <div class="panel-body">
                  
                    <form wire:submit.prevent="store()" >
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="vat">Customer<span class="required" style="color: red">*</span></label>
                                       <select class="form-control" wire:model.debounce.300ms="selectedCustomer" required>
                                        <option value="">Select Customers</option>
                                        @foreach ($customers as $customer)
                                                <option value="{{ $customer->id }}">{{ $customer->name }} </option>                                        
                                        @endforeach
                                       </select>
                                       <small><a href="{{ route('customers.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Customer</a></small> 
                                        @error('selectedCustomer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="name">Date<span class="required" style="color: red">*</span></label>
                                        <input type="date" class="form-control" wire:model.debounce.300ms="date"  placeholder="Enter Sale Date" required >
                                        @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="vat">Currencies<span class="required" style="color: red">*</span></label>
                                       <select class="form-control" wire:model.debounce.300ms="selectedCurrency" required>
                                        <option value="">Select Currency</option>
                                        @foreach ($currencies as $currency)
                                                <option value="{{ $currency->id }}">{{ $currency->name }} </option>                                        
                                        @endforeach
                                       </select>
                                       <small><a href="{{ route('currencies.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Currency</a></small> 
                                        @error('selectedCurrency') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                    @if (!is_null($selectedCurrency))
                                    @if (Auth::user()->employee->company)
                                        @if ($selectedCurrency != Auth::user()->employee->company->currency_id)
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="customer">Conversion Rate<span class="required" style="color: red">*</span></label>
                                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate" wire:change="exchangeRate()" placeholder="Exchange Rate" required>
                                                    @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div> 
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="customer">Sale in {{ Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->name : "" }}<span class="required" style="color: red">*</span></label>
                                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_amount" placeholder="Converted Sale" required>
                                                    @error('exchange_amount') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div> 
                                            </div>
                                        </div>
                                        @endif
                                    @endif
                                @endif 
                                </div>
                            </div> 
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="country">Products / Services<span class="required" style="color: red">*</span></label>
                                            <select wire:model.debounce.300ms="selectedProduct.0" class="form-control" required>
                                            <option value="">Select Item</option>
                                                @foreach ($products as $product)
                                                    <option value="{{$product->id}}">
                                                        <strong>{{$product->name}}</strong> {{$product->description ? "| ".$product->description : ""}}
                                                    </option> 
                                                @endforeach
                                            </select>
                                            <small>  <a href="#" data-toggle="modal" data-target="#product_serviceModal"><i class="fa fa-plus-square-o"></i> New Product / Service</a></small> 
                                        @error('selectedProduct.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                    
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="horse">Items in Inventory<span class="required" style="color: red">*</span></label>
                                       <select wire:model.debounce.300ms="selectedInventory.0" class="form-control" required size="6">
                                           <option value="" selected>Select Item</option>
                                            @foreach ($inventories as $inventory)
                                                <option value="{{$inventory->id}}">{{$inventory->inventory_number}} {{$inventory->serial_number ? "SN#: ".$inventory->serial_number : ""}} {{$inventory->part_number ? "PN#: ".$inventory->part_number : ""}} {{$inventory->weight}} {{$inventory->measurement}} {{$inventory->balance ? "Bal: ".$inventory->balance." ".$inventory->measurement : ""}}
                                                    
                                                </option>
                                            @endforeach
                                       </select>
                                       <small><a href="{{ route('inventories.create') }}" target="_blank"><i class="fa fa-plus-square-o"></i> Add item to inventory</a></small> 
                                        @error('selectedInventory.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                        <input type="number" min="0" max="1" class="form-control" wire:model.debounce.300ms="qty.0" placeholder="Qty" required disabled>
                                        @error('qty.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="name">Rate<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount.0" placeholder="Enter Rate" required >
                                        @error('amount.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="subheading">Taxes</label>
                                        <select wire:model.debounce.300ms="selectedTax.0"  class="form-control">
                                            <option value="">Select Tax</option>
                                                @foreach ($tax_accounts as $tax)
                                                   <option value="{{$tax->id}}">{{$tax->abbreviation}} {{$tax->rate ? $tax->rate."%" : ""}}</option> 
                                                @endforeach
                                            </select>
                                            <small><a href="{{ route('taxes.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small> 
                                        @error('selectedTax.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>      
                            </div>
                            @foreach ($inputs as $key => $value)
                                <div class="row">  
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="country">Products / Services<span class="required" style="color: red">*</span></label>
                                                <select wire:model.debounce.300ms="selectedProduct.{{$value}}" class="form-control" required>
                                                <option value="">Select Item</option>
                                                    @foreach ($products as $product)
                                                        <option value="{{$product->id}}">
                                                            <strong>{{$product->name}}</strong> {{$product->description ? "| ".$product->description : ""}}
                                                        </option> 
                                                    @endforeach
                                                </select>
                                                <small>  <a href="#" data-toggle="modal" data-target="#product_serviceModal"><i class="fa fa-plus-square-o"></i> New Product / Service</a></small> 
                                            @error('selectedProduct.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                        
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="horse">Items in Inventory<span class="required" style="color: red">*</span></label>
                                           <select wire:model.debounce.300ms="selectedInventory.{{$value}}" class="form-control" required size="6">
                                               <option value="" selected>Select Item</option>
                                                @foreach ($inventories as $inventory)
                                                    <option value="{{$inventory->id}}">{{$inventory->inventory_number}} {{$inventory->serial_number ? "SN#: ".$inventory->serial_number : ""}} {{$inventory->part_number ? "PN#: ".$inventory->part_number : ""}} {{$inventory->weight}} {{$inventory->measurement}} {{$inventory->balance ? "Bal: ".$inventory->balance." ".$inventory->measurement : ""}}
                                                        @if ($inventory->rate)
                                                        {{$inventory->currency ? $inventory->currency->name : ""}} {{$inventory->currency ? $inventory->currency->symbol : ""}}{{number_format($inventory->rate,2)}}  
                                                        @endif
                                                    </option>
                                                @endforeach
                                           </select>
                                           <small><a href="{{ route('inventories.create') }}" target="_blank"><i class="fa fa-plus-square-o"></i> Add item to inventory</a></small> 
                                            @error('selectedInventory.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="name">Quantity<span class="required" style="color: red">*</span></label>
                                                <input type="number" class="form-control" wire:model.debounce.300ms="qty.{{ $value }}" placeholder="Enter Qty" required >
                                                @error('qty.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="name">Rate<span class="required" style="color: red">*</span></label>
                                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount.{{ $value }}" placeholder="Enter Rate" required >
                                                @error('amount.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                       
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="subheading">Taxes</label>
                                                <select wire:model.debounce.300ms="selectedTax.{{$value}}"  class="form-control">
                                                    <option value="">Select Tax</option>
                                                        @foreach ($tax_accounts as $tax)
                                                           <option value="{{$tax->id}}">{{$tax->abbreviation}} {{$tax->rate ? $tax->rate."%" : ""}}</option> 
                                                        @endforeach
                                                    </select>
                                                    <small><a href="{{ route('taxes.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small> 
                                                @error('selectedTax.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                            <h5 class="underline mt-30">Payment Details</h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="country">Payment Accounts</label>
                                       <select wire:model.debounce.300ms="account_id" class="form-control">
                                           <option value="">Select Payment Account</option>
                                         @foreach ($accounts as $account)
                                         @if ($invoice_currency)
                                         @if ($account->currency->id == $invoice_currency->id)
                                         <option value="{{ $account->id }}">{{ $account->name }} {{ $account->currency ? $account->currency->name : ""}}</option>
                                         @endif     
                                         @else  
                                         select currency for invoice
                                         @endif
                                         @endforeach
                                       </select>
                                        @error('account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="country">Method Of Payment</label>
                                       <select wire:model.debounce.300ms="mode_of_payment" class="form-control" >
                                           <option value="">Select Method Of Payment</option>
                                           <option value="Cash">Cash</option>
                                           <option value="Bank Payment">Bank Payment</option>
                                           <option value="Credit Card">Credit Card</option>
                                           <option value="Paypal">Paypal</option>
                                           <option value="Other">Other</option>   
                                       </select>
                                        @error('mode_of_payment') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                               
                            </div>
                           
                           
                            @if ($mode_of_payment == "Bank Payment" || $mode_of_payment == "Credit Card" || $mode_of_payment == "Paypal")
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Reference Code</label>
                                        <input type="text" class="form-control" wire:model.debounce.300ms="reference_code" placeholder="Enter Reference / Approval code"  >
                                        @error('reference_code') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Proof Of Payment</label>
                                        <input type="file" class="form-control" wire:model.debounce.300ms="pop" placeholder="Upload Pop" >
                                        @error('pop') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                              
                            </div>
                            @elseif ($mode_of_payment == "Cash")
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="country">Denomination</label>
                                       <select wire:model.debounce.300ms="denomination.0" class="form-control"  >
                                           <option value="">Select Denomination</option>
                                           <option value="1">1</option>
                                           <option value="2">2</option>
                                           <option value="5">5</option>
                                           <option value="10">10</option>
                                           <option value="20">20</option>
                                           <option value="50">50</option>
                                           <option value="100">100</option>
                                           <option value="200">200</option>
                                       </select>
                                        @error('denomination.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="name">Quantity</label>
                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="denomination_qty.0" placeholder="Enter Quantity"  >
                                    @error('denomination_qty.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                                </div>
                              
                                <div class="row">
                                    @foreach ($inputs as $key => $value)
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            {{-- <label for="country">Denomination</label> --}}
                                           <select wire:model.debounce.300ms="denomination.{{ $value }}" class="form-control"  >
                                               <option value="">Select Denomination</option>
                                               <option value="1">1</option>
                                               <option value="2">2</option>
                                               <option value="5">5</option>
                                               <option value="10">10</option>
                                               <option value="20">20</option>
                                               <option value="50">50</option>
                                               <option value="100">100</option>
                                               <option value="200">200</option>
                                           </select>
                                            @error('denomination.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        {{-- <label for="name">Quantity</label> --}}
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="denomination_qty.{{ $value }}" placeholder="Enter Quantity"  >
                                        @error('denomination_qty.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label for=""></label>
                                            <button class="btn btn-danger btn-rounded xs"   wire:click.prevent="remove({{$key}})" > <i class="fa fa-times"></i></button>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
        
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i>Denomination</button>
                                        </div>
                                    </div>
                                </div>
                
                        
                            @endif
                          
                          <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Payment Amount<span class="required" style="color: red">*</span></label>
                                    <input type="number" min="0" step="any"  class="form-control" wire:model.debounce.300ms="payment_amount" placeholder="Enter Payment Amount" required >
                                    @error('payment_amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="">Memo / Notes (Optional)</label>
                                    <textarea class="form-control" wire:model.debounce.300ms="notes" cols="30" rows="5"></textarea>
                                    @error('notes') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                          </div>
                            

                           
                        </div>
                        <div class="modal-footer">
                            <div class="btn-group" role="group">
                                <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                                <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Save</button>
                            </div>
                            <!-- /.btn-group -->
                        </div>
                    </form>
                   
                        </div>
                    </div>
                </div>
                <!-- /.col-md-6 -->


            </div>

        </div>
        <!-- /.container-fluid -->
    </section>

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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="inventoryModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> New Item to inventory<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="storeInventory()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="comment">Item Name<span class="required" style="color: red">*</span></label>
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
                    @if ($buy == True)
                    <div class="form-group">
                        <label for="subheading">Select Expense Account</label>
                        <select wire:model.debounce.300ms="expense_account_id" class="form-control">
                            <option value="">Select Tax</option>
                                @foreach ($expense_accounts as $account)
                                <option value="{{$account->id}}">{{$account->name}}</option> 
                                @endforeach
                            </select>
                            {{-- <small><a href="{{ route('accounts.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Account</a></small>  --}}
                        @error('expense_account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    @endif
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="comment">Price</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="item_price" placeholder="Enter Price" >
                                @error('item_price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
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


   