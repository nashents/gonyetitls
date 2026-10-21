<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 ">

                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Edit Tyres</h5>
                            </div>
                        </div>
                        <div class="panel-body">
                            <form wire:submit.prevent="update()" >
                                 <div class="form-group">
                                    <label for="country">Goods Received Vouchers
                                         @if (!is_null($selectedPurchase) || !is_null($selectedTransfer) )
                                                <span class="required" style="color: red">*</span>
                                            @endif
                                    </label>
                                    <select wire:model.debounce.300ms="selectedGoodsReceived" class="form-control" {{!is_null($selectedPurchase) || !is_null($selectedTransfer) ? "required" : "" }} disabled>
                                        <option value="">Select GRV</option>
                                        @foreach ($goods_receiveds as $goods_received)
                                        <option value="{{$goods_received->id}}">GRV#: {{$goods_received->goods_received_number}} Receiveing Date: {{$goods_received->date}} ReceivedBy: {{$goods_received->employee ? $goods_received->employee->name : ""}} {{$goods_received->employee ? $goods_received->employee->surname : ""}} Vendor: {{$goods_received->vendor ? $goods_received->vendor->name : ""}} {{$goods_received->delivery_number ? "Delivery#: ".$goods_received->delivery_number : ""}} {{$goods_received->delivery_date ? "Delivery Date: ".$goods_received->delivery_date : ""}} {{$goods_received->driver_name ? "Driver Name: ".$goods_received->driver_name : ""}} </option>
                                        @endforeach
                                    </select>
                                    <small><a href="{{ route('goods_receiveds.tyres') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Goods Received Voucher</a></small><a href="#" wire:click.prevent="refresh('goods_receiveds')" class="float-end"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                    @error('selectedGoodsReceived') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                                <div class="row">
                                                                <div class="col-md-3">
                                        <label for="exampleInputEmail13">Select source of items<span class="required" style="color: red">*</span></label>
                                            <div class="mb-10">
                                                <input type="radio" wire:model.debounce.300ms="source" value="Purchase"  class="line-style"  />
                                                <label for="one" class="radio-label">Purchase Order</label>
                                                <input type="radio" wire:model.debounce.300ms="source" value="Transfer"  class="line-style"  />
                                                <label for="one" class="radio-label">Store Transfer Order</label>
                                            </div>   
                                            <div class="form-group">
                                            @if ($source == "Purchase")
                                                <label for="country">Purchase Orders</label>
                                                <select wire:model.debounce.300ms="selectedPurchase" class="form-control" >
                                                    <option value="">Select Purchase Order</option>
                                                    @foreach ($purchases as $purchase)
                                                    @if ($purchase->inventories && $purchase->inventories->count() > 0)
                                                        <option value="{{$purchase->id}}" style="color: orange">{{$purchase->purchase_number}} | {{ $purchase->date }} | {{$purchase->vendor ? $purchase->vendor->name : ""}} | {{ $purchase->currency ? $purchase->currency->name : "" }} {{ $purchase->currency ? $purchase->currency->symbol : "" }}{{number_format($purchase->total,2)}}</option>
                                                    @else
                                                        <option value="{{$purchase->id}}">{{$purchase->purchase_number}} | {{ $purchase->date }} | {{$purchase->vendor ? $purchase->vendor->name : ""}} | {{ $purchase->currency ? $purchase->currency->name : "" }} {{ $purchase->currency ? $purchase->currency->symbol : "" }}{{number_format($purchase->total,2)}}</option>
                                                    @endif
                                                
                                                    @endforeach
                                                </select>
                                                <small>  <a href="{{ route('tyre_purchases.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Purchase Order</a></small><a href="#" wire:click.prevent="refresh('tyre_purchases')" style="float: right" class="float-end"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                                <br>
                                                <small style="color: green">NB: All fully / partially received purchase orders will appear in orange</small>
                                                @error('selectedPurchase') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            @elseif($source == "Transfer")
                                                <label for="country">Transfer Orders</label>
                                                <select wire:model.debounce.300ms="selectedTransfer" class="form-control" >
                                                    <option value="">Select Transfer Order</option>
                                                    @foreach ($transfers as $transfer)
                                                    @php

                                                        $from_store = App\Models\Store::find($transfer->from);
                                                        $to_store = App\Models\Store::find($transfer->to);

                                                    @endphp
                                                    @if ($transfer->inventories && $transfer->inventories->count() > 0)
                                                        <option value="{{$transfer->id}}" style="color: orange">{{$transfer->purchase_number}} | {{ $purchase->date }} | {{$purchase->vendor ? $purchase->vendor->name : ""}} | {{ $purchase->currency ? $purchase->currency->name : "" }} {{ $purchase->currency ? $purchase->currency->symbol : "" }}{{number_format($purchase->total,2)}}</option>
                                                    @else
                                                        <option value="{{$transfer->id}}">{{$transfer->transfer_number}} {{ $transfer->date }} From {{$from_store?->name}} To {{$to_store?->name}} </option>
                                                    @endif
                                                
                                                    @endforeach
                                                </select>
                                                <small>  <a href="{{ route('inventory_purchases.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Purchase Order</a></small> 
                                                <br>
                                                <small style="color: green">NB: All fully / partially received purchase orders will appear in orange</small>
                                                @error('selectedPurchase') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            @endif
                                    </div>
                                        </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="country">Vendors</label>
                                            <select wire:model.debounce.300ms="vendor_id" class="form-control">
                                                <option value="">Select Vendor</option>
                                                @foreach ($vendors as $vendor)
                                                <option value="{{$vendor->id}}">{{$vendor->name}}</option>
                                                @endforeach
                                            </select>
                                            @error('vendor_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="name">Currencies <span class="required" style="color: red">*</span></label>
                                            <select wire:model.debounce.300ms="selectedCurrency" class="form-control" required>
                                                <option value="">Select Currency</option>
                                            @foreach ($currencies as $currency)
                                            <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                            @endforeach
                                            </select>
                                            @error('selectedCurrency') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                        @if (!is_null($selectedCurrency))
                                        @if (Auth::user()->employee->company)
                                            @if ($selectedCurrency != Auth::user()->employee->company->currency_id)
                                            <div class="form-group">
                                                <label for="customer">Conversion Rate<span class="required" style="color: red">*</span></label>
                                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate {{$selected_currency ? "From ".$selected_currency->name : ""}} {{Auth::user()->employee->company->currency ? "To ".Auth::user()->employee->company->currency->name : ""}}" required>
                                                @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                                <small style="color: green">{{$selected_currency ? " 1 ".$selected_currency->name." is how much in" : ""}} {{Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->name." ?" : ""}}</small>
                                                <small>{{$exchange_amount ? "The converted amount is: ".$exchange_amount : ""}}</small>
                                            </div> 
                                            @endif
                                        @endif
                                        @endif
                                    </div> 
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="Product">Expense Category</label>
                                            <select wire:model.debounce.300ms="selectedAccount" class="form-control" {{$selectedPurchase ? "disabled" : ""}}>
                                                <option value="">Select Category</option>
                                                @foreach ($expense_accounts as $account)
                                                    <option value="{{$account->id}}">{{$account->name}}</option>
                                                @endforeach
                                            </select>
                                            {{-- <small>  <a href="{{ route('accounts.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Category</a></small>  --}}
                                            @error('selectedAccount') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>
                               
                                 <div class="mt-30" style="background-color: lightgrey; padding:5px; border: 1px solid #333; border-radius: 5px;">
                                <div class="row">
                                      <div class="col-md-6">
                                        @if ($source == "Transfer" && $selectedTransfer)
                                            <div class="form-group">
                                                <label for="country">Product(s) — from this Transfer</label>
                                                <select wire:model.debounce.300ms="selectedTransferItem" class="form-control">
                                                    <option value="">Select Product</option>
                                                    @foreach ($transfer_items as $transfer_item)
                                                        <option value="{{$transfer_item->id}}">
                                                            @if ($transfer_item->product)
                                                                {{$transfer_item->product->product_number}} - {{$transfer_item->product->name}} {{$transfer_item->product->identification_number ? "(".$transfer_item->product->identification_number.")" : ""}}
                                                            @endif
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('selectedTransferItem') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        @endif
                                            <div class="form-group">
                                                <label for="country">Product(s)<span class="required" style="color: red">*</span></label>
                                                <div class="mb-10">
                                                    <input type="checkbox" wire:model.debounce.300ms="all_products"   class="line-style" />
                                                        <label for="one" class="radio-label">Show all products</label>
                                                    @error('all_products') <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                                <select wire:model.debounce.300ms="selectedProduct" class="form-control" required>
                                                    <option value="">Select Product</option>
                                                    @foreach ($products as $product)
                                                        <option value="{{$product->id}}" @if($product->department !== $department) disabled title="You can't use this product in {{ $department }} — it's a {{ $product->department }} product." @endif> {{ $product->product_number }} - {{ $product->name }} {{ $product->identification_number ? "(".$product->identification_number.")" : "" }} @if($product->department !== $department)&mdash; {{ ucfirst($product->department) }} product, not usable here @endif</option>
                                                    @endforeach
                                                </select>
                                                 <small><a href="{{ route('inventory_products.create') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Product</a></small><a href="#" wire:click.prevent="refresh('products')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                                @error('selectedProduct') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                       
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="purchase_date">Description</label>
                                            <textarea  class="form-control" wire:model.debounce.300ms="item_description" cols="30" rows="2" disabled></textarea>
                                            @error('item_description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="width">Type</label>
                                          <select class="form-control" wire:model.debounce.300ms="type" >
                                            <option value="">Select Type</option>
                                            <option value="Diff">Diff</option>
                                            <option value="Multipurpose">Multipurpose</option>
                                            <option value="Steer">Steer</option>
                                          </select>
                                            @error('type') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                   
                                  
    
                                </div>
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="width">Width<i>(mL)</i> /</label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="width"   />
                                            @error('width') <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small style="color: green">205 / 65 R 15 </small>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="aspect_ratio">A/Ratio (R)<span class="required" style="color: red">*</span></label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="aspect_ratio"  required />
                                            @error('aspect_ratio') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="diameter">Diameter/Rim<i>(in)</i><span class="required" style="color: red">*</span></label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="diameter"   required />
                                            @error('diameter') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="width">Tread Depth</label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="thread_depth"  placeholder="Tyre Tread Depth " />
                                            @error('thread_depth') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="width">Tyre Pressure(PSI)</label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="pressure_psi"  placeholder="Standard (PSI) " />
                                            @error('pressure_psi') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="aspect_ratio">Lifespan in (Kms)</label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="life_span" placeholder="Tyre life span in kilometers" />
                                            @error('life_span') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
    
                                </div>
                          
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="name">Serial#</label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="serial_number" placeholder="Serial#/UniqueID"/>
                                    @error('serial_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                                </div>
                                <div class="col-md-2">
                                    @if (filled($serial_number))
                                    <div class="form-group">
                                        <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="any" min="1" class="form-control" wire:model.debounce.300ms="qty"  disabled required/>
                                        @error('qty') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                    @else
                                    <div class="form-group">
                                        <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="any" min="1" class="form-control" wire:model.debounce.300ms="qty" disabled  required/>
                                        @error('qty') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                    @endif
                                   
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="name">Rate</label>
                                        <input type="number" step="any"  class="form-control" wire:model.debounce.300ms="amount"/>
                                        @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="subheading">Taxes</label>
                                        <select wire:model.debounce.300ms="selectedTax"  class="form-control">
                                             <option value="">Select Tax Category</option>
                                                @foreach ($tax_accounts as $tax)
                                                   <option value="{{$tax->id}}">{{$tax->abbreviation}}</option> 
                                                @endforeach
                                            </select>
                                            <small><a href="{{ route('taxes.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small> 
                                        @error('selectedTax') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div> 
                                  <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="name">Additional Cost</label>
                                        <input type="number" step="any"  class="form-control" wire:model.debounce.300ms="cost"  />
                                        @error('cost') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>     
                        </div>
                                 </div>
                              <br>
                   <div class="row">
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="purchase_date">Date<span class="required" style="color: red">*</span></label>
                                        <input type="date" class="form-control" wire:model.debounce.300ms="purchase_date" placeholder="Purchase Date" required>
                                        @error('purchase_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="country">Stores<span class="required" style="color: red">*</span></label>
                                       <select wire:model.debounce.300ms="store_id" class="form-control" {{$source == "Transfer" ? "disabled" : ""}} required>
                                           <option value="">Select Store</option>
                                         @foreach ($stores as $store)
                                            <option value="{{$store->id}}">{{$store->name}}</option>
                                         @endforeach
                                       </select>
                                        <small><a href="{{ route('stores.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Store</a></small><a href="#" wire:click.prevent="refresh('stores')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>  
                                        @error('store_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="condition">Conditions</label>
                                        <select wire:model.debounce.300ms="condition" class="form-control" >
                                            <option value="">Select Condition</option>
                                            <option value="New">New</option>
                                            <option value="Refurbished">Refurbished</option>
                                            <option value="Second Hand">Second Hand</option>
                                        </select>
                                        @error('condition') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                               
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="condition">Purchase Types</label>
                                        <select wire:model.debounce.300ms="purchase_type" class="form-control" >
                                            <option value="">Select Purchase Type</option>
                                            <option value="Owned">Owned</option>
                                            <option value="Rented">Rented</option>
                                            <option value="Leased">Leased</option>
                                        </select>
                                        @error('purchase_type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="purchase_date">Warranty Expiry Date</label>
                                        <input type="date" class="form-control" wire:model.debounce.300ms="warranty_exp_date" placeholder="Warranty Expiry Date">
                                        @error('warranty_exp_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="purchase_date">Useful Life</label>
                                        <input type="text" class="form-control" wire:model.debounce.300ms="life" placeholder="Useful Life">
                                        @error('life') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                               
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="condition">Depriciation Types</label>
                                        <select wire:model.debounce.300ms="depreciation_type" class="form-control" >
                                            <option value="">Select Depriciation Type</option>
                                            <option value="Declining Balance">Declining Balance</option>
                                            <option value="Double Declining Balance">Double Declining Balance</option>
                                            <option value="Straight line">Straight line</option>
                                            <option value="Sum of the years digit">Sum of the years digit</option>
                                        </select>
                                        @error('depreciation_type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="residual_value">Residual Value</label>
                                        <input type="text" class="form-control" wire:model.debounce.300ms="residual_value" placeholder="Enter Product Residual Value">
                                        @error('residual_value') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="description">Description</label>
                                        <textarea wire:model.debounce.300ms="description" class="form-control" cols="30" rows="5"></textarea>
                                        @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            @if ($can_fit)
                                <h5 class="underline mt-30">Tyre Assignment</h5>
                                @if ($current_assignment_id)
                                    <small style="color: green">Currently fitted. Change the unit, axle or position to move this tyre; leave as is to keep it where it is.</small>
                                @else
                                    <small style="color: green">Out of stores, not fitted. Pick a unit, axle and position to fit it, or leave blank.</small>
                                @endif
                                <div class="mb-10 mt-10">
                                    <label for="">Assignment For?</label>
                                    <input type="radio" wire:model="fit_type" value="Horse" class="line-style" />
                                    <label class="radio-label">Horse</label>
                                    <input type="radio" wire:model="fit_type" value="Trailer" class="line-style" />
                                    <label class="radio-label">Trailer</label>
                                    <input type="radio" wire:model="fit_type" value="Vehicle" class="line-style" />
                                    <label class="radio-label">Vehicle</label>
                                    @error('fit_type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>{{ $fit_type ? $fit_type : 'Unit' }}</label>
                                            <select class="form-control" wire:model="fit_unit_id" wire:key="fit-unit-{{ $fit_type }}">
                                                <option value="">Select {{ $fit_type ? $fit_type : 'Unit' }}</option>
                                                @if ($fit_type == "Horse")
                                                    @foreach ($horses as $horse)
                                                        <option value="{{$horse->id}}">{{$horse->identifier_label}} {{$horse->horse_make ? $horse->horse_make->name : ""}} {{$horse->horse_model ? $horse->horse_model->name : ""}}</option>
                                                    @endforeach
                                                @elseif ($fit_type == "Trailer")
                                                    @foreach ($trailers as $trailer)
                                                        <option value="{{$trailer->id}}">{{$trailer->identifier_label}} {{$trailer->make}} {{$trailer->model}}</option>
                                                    @endforeach
                                                @elseif ($fit_type == "Vehicle")
                                                    @foreach ($vehicles as $vehicle)
                                                        <option value="{{$vehicle->id}}">{{$vehicle->identifier_label}} {{$vehicle->vehicle_make ? $vehicle->vehicle_make->name : ""}} {{$vehicle->vehicle_model ? $vehicle->vehicle_model->name : ""}}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            @error('fit_unit_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Wheel Axle</label>
                                            <select class="form-control" wire:model="fit_axle">
                                                <option value="">Select Wheel Axle</option>
                                                @foreach (\App\Models\TyreAssignment::AXLES as $axleOption)
                                                    <option value="{{ $axleOption }}">{{ $axleOption }}</option>
                                                @endforeach
                                            </select>
                                            @error('fit_axle') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Wheel Position</label>
                                            <select class="form-control" wire:model="fit_position">
                                                <option value="">Select Wheel Position</option>
                                                @foreach (\App\Models\TyreAssignment::POSITIONS as $positionValue => $positionLabel)
                                                    <option value="{{ $positionValue }}">{{ $positionLabel }}</option>
                                                @endforeach
                                            </select>
                                            @error('fit_position') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Date</label>
                                            <input type="date" class="form-control" wire:model.debounce.300ms="fit_date">
                                            @error('fit_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                @if ($fit_changed)
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Fitting Mileage</label>
                                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="fit_mileage" placeholder="Odometer of the unit it goes on">
                                                @error('fit_mileage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        @if ($fit_moves_unit)
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Removal Mileage ({{ $current_unit_label }})</label>
                                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="removal_mileage" placeholder="Odometer of the unit it comes off">
                                                    @error('removal_mileage') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                                @if ($fit_taken)
                                    <div class="alert alert-warning">
                                        <i class="fa fa-exclamation-triangle"></i>
                                        {{ $fit_taken->axle }} {{ \App\Models\TyreAssignment::POSITIONS[$fit_taken->position] ?? $fit_taken->position }}
                                        on {{ $fit_taken->locationLabel() }} is already assigned to tyre
                                        {{ optional($fit_taken->tyre)->serial_number ? 'SN# '.$fit_taken->tyre->serial_number : optional($fit_taken->tyre)->tyre_number }}.
                                        Saving will remove that assignment (the tyre stays out of stores, unfitted).
                                    </div>
                                @endif
                                @error('fitment') <div class="alert alert-danger">{{ $message }}</div> @enderror
                            @elseif ($fit_blocker)
                                <h5 class="underline mt-30">Tyre Assignment</h5>
                                <small style="color: red">{{ $fit_blocker }}</small>
                            @endif

                            <div class="mb-10 mt-10" style="float: right;">
                                <input type="checkbox" wire:model.debounce.300ms="to_bills" {{$selectedPurchase ? "disabled" : ""}}   class="line-style" />
                                <label for="one" class="radio-label">Add tyre(s) to bills</label>
                                @error('to_bills') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="btn-group pull-right mt-10" >
                                       <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                                        <button type="submit" class="btn bg-success btn-wide btn-rounded" > <i class="fa fa-refresh"></i>Update</button>
                                    </div>
                                </div>
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


</div>
