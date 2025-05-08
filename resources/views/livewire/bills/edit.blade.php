<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Edit Bill</h5>
                            </div>
                        </div>
                        <div class="panel-body">
                        <form wire:submit.prevent="update()" >
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="name">Bill#<span class="required" style="color: red">*</span></label>
                                        <input type="text" class="form-control" wire:model.debounce.300ms="bill_number" placeholder="Enter Bill Number" required >
                                        @error('bill_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-7">
                                    @if (!isset($bill_for))
                                    <div class="form-group" >
                                        <label for="name">Bill For?</label>
                                        <label class="radio-inline">
                                            <input type="radio" wire:model.debounce.300ms="bill_for" value="Asset" name="optradio" >Asset
                                          </label>
                                        <label class="radio-inline">
                                            <input type="radio" wire:model.debounce.300ms="bill_for" value="Horse" name="optradio" >Driver
                                          </label>
                                        <label class="radio-inline">
                                            <input type="radio" wire:model.debounce.300ms="bill_for" value="Horse" name="optradio" >Horse
                                          </label>
                                          <label class="radio-inline">
                                            <input type="radio" wire:model.debounce.300ms="bill_for" value="Trailer" name="optradio">Trailer
                                          </label>
                                          <label class="radio-inline">
                                            <input type="radio" wire:model.debounce.300ms="bill_for" value="Transporter" name="optradio">Transporter
                                          </label>
                                          <label class="radio-inline">
                                            <input type="radio" wire:model.debounce.300ms="bill_for" value="Vehicle" name="optradio">Vehicle
                                          </label>
                                    </div>
                                    @endif
                                    @if ($bill_for == "Transporter")  
                                            <div class="form-group">
                                                <label for="country">Transporter(s)<span class="required" style="color: red">*</span></label>
                                                <select wire:model.debounce.300ms="transporter_id" class="form-control" required>
                                                        <option value="">Select Transporter</option>
                                                    @foreach ($transporters as $transporter)
                                                        <option value="{{$transporter->id}}">{{$transporter->name}} </option> 
                                                    @endforeach
                                                </select>
                                                @error('transporter_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                            @elseif ($bill_for == "Asset")  
                                            <div class="form-group">
                                                <label for="country">Asset(s)<span class="required" style="color: red">*</span></label>
                                                <select wire:model.debounce.300ms="asset_id" class="form-control" required>
                                                        <option value="">Select Asset</option>
                                                    @foreach ($assets as $asset)
                                                    <option value="{{$asset->id}}"> {{$asset->product->brand ? $asset->product->brand->name : ""}} {{$asset->product ? $asset->product->name : ""}} {{$asset->product ? $asset->product->identification_number : ""}} {{$asset->serial_number ? "SN: ".$asset->serial_number : ""}}</option>
                                                    @endforeach
                                                </select>
                                                @error('asset_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                    @elseif ($bill_for == "Vehicle")  
                                        <div class="form-group">
                                            <label for="country">Vehicle(s)<span class="required" style="color: red">*</span></label>
                                            <select wire:model.debounce.300ms="vehicle_id" class="form-control" required>
                                                    <option value="">Select Vehicle</option>
                                                @foreach ($vehicles as $vehicle)
                                                    <option value="{{$vehicle->id}}">{{$vehicle->registration_number}} {{$vehicle->fleet_number ? "(".$vehicle->fleet_number.")" : ""}}</option> 
                                                @endforeach
                                            </select>
                                            @error('vehicle_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                        @elseif ($bill_for == "Driver")  
                                        <div class="form-group">
                                            <label for="country">Driver(s)<span class="required" style="color: red">*</span></label>
                                            <select wire:model.debounce.300ms="driver_id" class="form-control" required>
                                                    <option value="">Select Driver</option>
                                                @foreach ($drivers as $driver)
                                                    <option value="{{$driver->id}}">{{$driver->employee ? $driver->employee->name : ""}} {{$driver->employee ? $driver->employee->surname : ""}}</option> 
                                                @endforeach
                                            </select>
                                            @error('driver_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    @elseif ($bill_for == "Horse")  
                                    <div class="form-group">
                                        <label for="country">Horse(s)<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="horse_id" class="form-control" required>
                                                <option value="">Select Horse</option>
                                            @foreach ($horses as $horse)
                                                <option value="{{$horse->id}}">{{$horse->registration_number}} {{$horse->fleet_number ? "(".$horse->fleet_number.")" : ""}}</option> 
                                            @endforeach
                                        </select>
                                        @error('horse_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                    @elseif ($bill_for == "Trailer")  
                                        <div class="form-group">
                                            <label for="country">Trailer(s)<span class="required" style="color: red">*</span></label>
                                            <select wire:model.debounce.300ms="trailer_id" class="form-control" required>
                                                    <option value="">Select Trailer</option>
                                                @foreach ($trailers as $trailer)
                                                    <option value="{{$trailer->id}}">{{$trailer->registration_number}} {{$trailer->fleet_number ? "(".$trailer->fleet_number.")" : ""}}</option> 
                                                @endforeach
                                            </select>
                                            @error('trailer_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                    @endif
                                    
                                </div>
                                <div class="col-md-3">
                                    @if (isset($bill_for))
                                    <div class="form-group">
                                        <label for="country">Vendor(s)</label>
                                        <select wire:model.debounce.300ms="selectedVendor" class="form-control">
                                            <option value="">Select Vendor</option>
                                            @foreach ($vendors as $vendor)
                                                <option value="{{$vendor->id}}">{{$vendor->name}} </option> 
                                            @endforeach
                                        </select>
                                        @error('selectedVendor') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        <small> <a href="{{ route('vendors.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Vendor</a></small> 
                                    </div>
                                    @else
                                    <div class="form-group">
                                        <label for="country">Vendor(s)<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="selectedVendor" class="form-control" required>
                                            <option value="">Select Vendor</option>
                                            @foreach ($vendors as $vendor)
                                                <option value="{{$vendor->id}}">{{$vendor->name}} </option> 
                                            @endforeach
                                        </select>
                                        @error('selectedVendor') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        <small> <a href="{{ route('vendors.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Vendor</a></small> 
                                    </div> 
                                    @endif
                                   
                                </div>
                               
                             
                            </div>
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="country">Currencies<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="selectedCurrency" class="form-control" required>
                                           <option value="">Select Currency</option>
                                         @foreach ($currencies as $currency)
                                         <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>                                      
                                         @endforeach
                                      
                                       </select>
                                        @error('selectedCurrency') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        <small>  <a href="{{ route('currencies.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Currency</a></small> 
                                    </div>
                                    @if (!is_null($selectedCurrency))
                                    @if (Auth::user()->employee->company)
                                        @if ($selectedCurrency != Auth::user()->employee->company->currency_id)
                                        <div class="form-group">
                                            <label for="customer">Conversion Rate</label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate {{$selected_currency ? "From ".$selected_currency->name : ""}} {{Auth::user()->employee->company->currency ? "To ".Auth::user()->employee->company->currency->name : ""}}" >
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
                                        <label for="name">Bill Date<span class="required" style="color: red">*</span></label>
                                        <input type="date" class="form-control" wire:change="billDate()" wire:model.debounce.300ms="bill_date" placeholder="Enter Bill Date" required >
                                        @error('bill_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="name">Due Date</label>
                                        <input type="date" class="form-control" wire:model.debounce.300ms="due_date" placeholder="Enter Due Date"  >
                                        @error('due_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="subheading">Notes</label>
                                        <textarea class="form-control" wire:model.debounce.300ms="notes" cols="30" rows="3"></textarea>
                                        @error('notes') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                    </div>
                              
                            </div>
                          
                            @foreach ($bill_expenses as $key => $value)
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="country">Items<span class="required" style="color: red">*</span></label>
                                            <select wire:model.debounce.300ms="selectedCurrentProduct.{{$key}}" class="form-control" required>
                                            <option value="">Select Item</option>
                                                @foreach ($products as $product)
                                                    <option value="{{$product->id}}">
                                                        <strong>{{$product->name}}</strong> {{$product->description ? "| ".$product->description : ""}}
                                                    </option> 
                                                @endforeach
                                            </select>
                                            <small><a href="#" wire:click="showItem({{$value}})"><i class="fa fa-plus-square-o"></i> New Product / Service</a></small> 
                                        @error('selectedCurrentProduct.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="subheading">Expense Accounts<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="selectedCurrentAccount.{{$key}}" class="form-control" required>
                                            <option value="">Select Expense Account</option>
                                        @foreach ($expense_accounts as $account)
                                            <option value="{{$account->id}}">{{$account->name}}</option> 
                                        @endforeach
                                    
                                        </select>
                                        @error('selectedCurrentAccount.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="name">Description</label>
                                       <textarea wire:model.debounce.300ms="current_description.{{$key}}" class="form-control" cols="30" rows="1" placeholder="Enter Description"></textarea>
                                        @error('current_description.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                        <input type="number" class="form-control" wire:model.debounce.300ms="current_qty.{{$key}}"  required />
                                        @error('current_qty.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label for="name">Price<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="current_amount.{{$key}}" required />
                                        @error('current_amount.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="subheading">Taxes</label>
                                        <select wire:model.debounce.300ms="selectedCurrentTax.{{$key}}"  class="form-control">
                                            <option value="">Select Tax</option>
                                                @foreach ($tax_accounts as $tax)
                                                   <option value="{{$tax->id}}">{{$tax->abbreviation}} {{$tax->rate ? $tax->rate."%" : ""}}</option> 
                                                @endforeach
                                            </select>
                                            <small><a href="{{ route('taxes.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small> 
                                        @error('selectedCurrentTax.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>      
                                <div class="col-md-1">
                                    <div class="form-group" style="margin-top: 29px; ">
                                        <a href="#" wire:click.prevent="removeShow({{ $value->id }})"  ><i class="fa fa-trash color-danger"></i></a>
                                    </div>
                                </div>  
                            </div>
                            @endforeach
                     
                         
                            @foreach ($inputs as $key => $value)
                                <div class="row"> 
                                    <div class="col-md-2">
                                        <div class="form-group">
                                             <label for="country">Items<span class="required" style="color: red">*</span></label>
                                                <select wire:model.debounce.300ms="selectedProduct.{{ $value }}" class="form-control" required>
                                                    <option value="">Select Item</option>
                                                    @foreach ($products as $product)
                                                    <option value="{{$product->id}}">
                                                        <strong>{{$product->name}}</strong> {{$product->description ? "| ".$product->description : ""}}
                                                    </option>
                                                    @endforeach
                                                </select>
                                                <small><a href="#" wire:click="showItem({{$value}})"><i class="fa fa-plus-square-o"></i> New Product / Service</a></small> 
                                            @error('selectedProduct.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                        </div> 
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="subheading">Expense Accounts<span class="required" style="color: red">*</span></label>
                                            <select wire:model.debounce.300ms="selectedAccount.{{$value}}" class="form-control" required>
                                                <option value="">Select Expense Account</option>
                                            @foreach ($expense_accounts as $account)
                                                <option value="{{$account->id}}">{{$account->name}}</option> 
                                            @endforeach
                                        
                                            </select>
                                            @error('selectedAccount.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="name">Description</label>
                                               <textarea wire:model.debounce.300ms="description.{{ $value }}" class="form-control" cols="30" rows="1" placeholder="Enter Description"></textarea>
                                                @error('description.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                                <input type="number" min="1" class="form-control" wire:model.debounce.300ms="qty.{{ $value }}" placeholder="" required />
                                                @error('qty.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <label for="name">Price<span class="required" style="color: red">*</span></label>
                                                <input type="number" step="any" min="1" class="form-control" wire:model.debounce.300ms="amount.{{ $value }}" placeholder="" required />
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
                           
                        </div>
                        <div class="modal-footer">
                            <div class="btn-group" role="group">
                                <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                                <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
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


    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="removeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                   <center> <strong>Are you sure you want to delete this Bill Item</strong> </center>
                </div>
                <form wire:submit.prevent="removeBillItem()" >
                <div class="modal-footer no-border">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        @if ($bill_expenses->count() > 1)
                        <button type="submit" class="btn bg-black btn-wide btn-rounded" ><i class="fa fa-trash"></i>Delete</button>
                        @else
                        <button type="submit" class="btn bg-black btn-wide btn-rounded" disabled ><i class="fa fa-trash"></i>Delete</button>
                        @endif
                       
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
                                <input type="checkbox" wire:model.debounce.300ms="sell"   class="line-style" />
                                <label for="one" class="radio-label">Sell this?</label>
                                @error('sell') <span class="text-danger error">{{ $message }}</span>@enderror
                                <br>
                                <small>Allow this product or service to be added to Invoices.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-10">
                                <input type="checkbox" wire:model.debounce.300ms="buy"   class="line-style" disabled/>
                                <label for="one" class="radio-label">Buy this?</label>
                                @error('buy') <span class="text-danger error">{{ $message }}</span>@enderror
                                <br>
                                <small>Allow this product or service to be added to Bills.</small>
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
                        @else
                        <div class="col-md-6">

                        </div>
                        @endif
                              
                        @if (!is_null($buy) && $buy == True)
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="subheading">Expense Category<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="expense_account_id" class="form-control" required>
                                    <option value="">Select Category</option>
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
                        @else
                        <div class="col-md-6">

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

</div>
