<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 ">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Edit Asset</h5>
                            </div>
                        </div>
                        <div class="panel-body">
                        <form wire:submit.prevent="update()" >
                             <div class="form-group">
                                    <label for="country">Goods Received Vouchers</label>
                                    <select wire:model.debounce.300ms="selectedGoodsReceived" class="form-control" disabled>
                                        <option value="">Select GRV</option>
                                        @foreach ($goods_receiveds as $goods_received)
                                        <option value="{{$goods_received->id}}">GRV#: {{$goods_received->goods_received_number}} Receiveing Date: {{$goods_received->date}} ReceivedBy: {{$goods_received->employee ? $goods_received->employee->name : ""}} {{$goods_received->employee ? $goods_received->employee->surname : ""}} Vendor: {{$goods_received->vendor ? $goods_received->vendor->name : ""}} {{$goods_received->delivery_number ? "Delivery#: ".$goods_received->delivery_number : ""}} {{$goods_received->delivery_date ? "Delivery Date: ".$goods_received->delivery_date : ""}} {{$goods_received->driver_name ? "Driver Name: ".$goods_received->driver_name : ""}} </option>
                                        @endforeach
                                    </select>
                                    <small>  <a href="{{ route('goods_receiveds.assets') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Goods Received Voucher</a></small>  <a href="#" wire:click.prevent="refresh('goods_receiveds')" class="float-end"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                    @error('selectedGoodsReceived') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="country">Purchase Orders</label>
                                       <select wire:model.debounce.300ms="selectedPurchase" class="form-control" disabled>
                                           <option value="">Select Purchase Order</option>
                                         @foreach ($purchases as $purchase)
                                         <option value="{{$purchase->id}}">{{$purchase->purchase_number}} | {{ $purchase->date }} | {{$purchase->vendor ? $purchase->vendor->name : ""}} | {{ $purchase->currency ? $purchase->currency->name : "" }} {{ $purchase->currency ? $purchase->currency->symbol : "" }}{{number_format($purchase->total,2)}}</option>
                                         @endforeach
                                       </select>
                                       <small>  <a href="{{ route('inventory_purchases.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Purchase Order</a></small> 
                                        @error('selectedPurchase') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="country">Vendors</label>
                                       <select wire:model.debounce.300ms="vendor_id" class="form-control">
                                           <option value="">Select Vendor</option>
                                         @foreach ($vendors as $vendor)
                                            <option value="{{$vendor->id}}">{{$vendor->name}}</option>
                                         @endforeach
                                       </select>
                                       <small>  <a href="#" data-toggle="modal" data-target="#vendorModal" target="_blank"><i class="fa fa-plus-square-o"></i> New Vendor</a></small> 
                                        @error('vendor_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                               
                                <div class="col-md-4">              
                                    <div class="form-group">
                                        <label for="name">Currencies</label>
                                        <select wire:model.debounce.300ms="selectedCurrency" class="form-control">
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

                        </div>
                        <div class="mt-30" style="background-color: lightgrey; padding:5px; border: 1px solid #333; border-radius: 5px;">
                        <div class="row">
                            <div class="col-md-4">
                                @if (is_null($selectedPurchase))
                                    <div class="form-group">
                                        <label for="country">Product(s)<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="selectedProduct" class="form-control" required>
                                            <option value="">Select Product</option>
                                            @foreach ($products as $product)
                                                <option value="{{$product->id}}"> {{$product->brand ? $product->brand->name : ""}} {{$product->name}} {{$product->identification_number}}</option>
                                            @endforeach
                                        </select>
                                       <small><a href="{{ route('products.create') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Product</a></small><a href="#" wire:click.prevent="refresh('products')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>  
                                        @error('selectedProduct') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                @else   
                                    <div class="form-group">
                                        <label for="country">Product(s)<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="selectedPurchaseProduct" class="form-control" required>
                                            <option value="">Select Product</option>
                                            @foreach ($purchase_products as $purchase_product)
                                                <option value="{{$purchase_product->id}}"> {{$purchase_product->product->brand ? $purchase_product->product->brand->name : ""}} {{$purchase_product->product ? $purchase_product->product->name : ""}} {{$purchase_product->product ? $purchase_product->product->identification_number : ""}}</option>
                                            @endforeach
                                        </select>
                                        @error('selectedPurchaseProduct') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                @endif
                               
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
                                    <label for="purchase_date">Container Capacity<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="weight"  required>
                                    @error('weight') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    <small>Full capacity per container (e.g. 100L per drum OR 12 Items per carton). Useful for deductions when invoicing / dispatching</small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="country">UnitOfMeasure<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="measurement" class="form-control" required>
                                        <option value="">Select UOM</option>
                                        <option value="Cubic">Cubic</option>
                                        <option value="Each">Each</option>
                                        <option value="Item(s)">Item(s)</option>
                                        <option value="Kg(s)">Kg(s)</option>
                                        <option value="Litre(s)">Litre(s)</option>
                                        <option value="Metre(s)">Metre(s)</option>
                                        <option value="Piece(s)">Piece(s)</option>
                                        <option value="Ton(s)">Ton(s)</option>
                                        <option value="Unit(s)">Unit(s)</option>
                                    </select>
                                    @error('measurement') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                                        <input type="number" min="1" class="form-control" wire:model.debounce.300ms="qty"  disabled required/>
                                        @error('qty') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                    @else
                                    <div class="form-group">
                                        <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                        <input type="number" min="1" class="form-control" wire:model.debounce.300ms="qty"  required/>
                                        @error('qty') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                    @endif
                                   
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="name">Rate</label>
                                        <input type="number" step="any" min="1" class="form-control" wire:model.debounce.300ms="amount"  />
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
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="cost"/>
                                        @error('cost') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                    </div>
                        </div>
                         </div>
                          <br>               
                                <br>   
                                       <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="purchase_date">Date<span class="required" style="color: red">*</span></label>
                                        <input type="date" class="form-control" wire:model.debounce.300ms="purchase_date" placeholder="Purchase Date" required>
                                            @error('purchase_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="country">Stores</label>
                                               <select wire:model.debounce.300ms="store_id" class="form-control">
                                                   <option value="">Select Store</option>
                                                 @foreach ($stores as $store)
                                                    <option value="{{$store->id}}">{{$store->name}}</option>
                                                 @endforeach
                                               </select>
                                               <small>  <a href="#" data-toggle="modal" data-target="#storeModal" ><i class="fa fa-plus-square-o"></i> New Store</a></small><a href="#" wire:click.prevent="refresh('stores')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>  
                                                @error('store_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="country">Racks</label>
                                               <select wire:model.debounce.300ms="rack_id" class="form-control">
                                                   <option value="">Select Rack</option>
                                                 @foreach ($racks as $rack)
                                                    <option value="{{$rack->id}}">{{$rack->name}} {{$rack->rack_number}}</option>
                                                 @endforeach
                                               </select>
                                                <small><a href="{{route('racks.index')}}" target="_blank"><i class="fa fa-plus-square-o"></i> New Rack</a></small><a href="#" wire:click.prevent="refresh('racks')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                                @error('rack_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="country">Bins</label>
                                               <select wire:model.debounce.300ms="bin_id" class="form-control">
                                                   <option value="">Select Bin</option>
                                                 @foreach ($bins as $bin)
                                                    <option value="{{$bin->id}}">{{$bin->name}} {{$bin->bin_number}}</option>
                                                 @endforeach
                                               </select>
                                                <small> <a href="{{route('bins.index')}}" target="_blank"><i class="fa fa-plus-square-o"></i> New Bin</a></small><a href="#" wire:click.prevent="refresh('bins')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                                @error('bin_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                      
                 
                                </div>
                                <div class="row">
                                    <div class="col-md-3">
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
    
                                        <div class="col-md-3">
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
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="purchase_date">Warranty Expiry Date</label>
                                        <input type="date" class="form-control" wire:model.debounce.300ms="warranty_exp_date" placeholder="Warranty Expiry Date">
                                            @error('warranty_exp_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                        </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="purchase_date">Useful Life</label>
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="life" placeholder="Useful Life" >
                                            @error('life') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                        </div>
                                        <div class="col-md-2">
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
                                </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="residual_value">Residual Value</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="residual_value" placeholder="Enter Product Residual Value">
                                    @error('residual_value') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                                </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea wire:model.debounce.300ms="description" class="form-control" cols="30" rows="5"></textarea>
                                    @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                                </div>
                        </div>

                        <div class="mb-10 mt-10" style="float: right;">
                            <input type="checkbox" wire:model.debounce.300ms="to_bills"  {{$selectedPurchase ? "disabled" : ""}}  class="line-style" />
                            <label for="one" class="radio-label">Add item(s) to bills</label>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="storeModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Store <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="storeStore()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Name<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="store_name" placeholder="Enter Name" required>
                        @error('store_name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Country</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="country" placeholder="Enter Country" >
                                @error('country') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">City</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="city" placeholder="Enter City" >
                                @error('city') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Suburb</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="suburb" placeholder="Enter Suburb" >
                                @error('suburb') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>     
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Street Address</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="street_address" placeholder="Enter Street Address">
                                @error('street_address') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
