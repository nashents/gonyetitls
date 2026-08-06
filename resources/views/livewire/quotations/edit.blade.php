<div>
    <section class="section">
        {{-- <x-loading/> --}}
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Edit Quotation</h5>
                            </div>
                        </div>
                        <div class="panel-body">
                            <form wire:submit.prevent="update()" >
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="name">Quotation Number<span class="required" style="color: red">*</span></label>
                                                <input type="text" class="form-control" wire:model.debounce.300ms="quotation_number" placeholder="Enter Quotation Number" disabled required>
                                                @error('quotation_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                <small style="color: green">
                                                    @if (Auth::user()->employee->company->quotation_serialize_by_customer == True)
                                                    Quotation number is serialized by customer
                                                    @else   
                                                    Quotation serialization default
                                                    @endif
                                                   </small>
                                            </div>
                                        </div>
                                          <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="name">Custom Reference</label>
                                                <input type="text" class="form-control" wire:model.debounce.300ms="custom_ref" placeholder="Enter Custom Quote Ref">
                                                @error('custom_ref') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="vat"><a href="{{route('customers.index')}}">Customers<span class="required" style="color: red">*</span></a></label>
                                               <select class="form-control" wire:model.debounce.300ms="selectedCustomer" required>
                                                <option value="">Select Customers</option>
                                                @foreach ($customers as $customer)
                                                        <option value="{{ $customer->id }}">{{ $customer->name }} </option>                                        
                                                @endforeach
                                               </select>
                                               <small>  <a href="{{route('customers.index')}}" ><i class="fa fa-plus-square-o"></i> New Customer</a></small><a href="#" wire:click.prevent="refresh('customers')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                                @error('selectedCustomer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="name">Date<span class="required" style="color: red">*</span></label>
                                                <input type="date" class="form-control" wire:model.debounce.300ms="date" wire:change="quotationDate" placeholder="Enter Quotation Date" required >
                                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="date">Expires</label>
                                                <input type="date" class="form-control" wire:model.debounce.300ms="expiry"  placeholder="Enter Quotation Expiry Date" >
                                                @error('expiry') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="to">Currency<span class="required" style="color: red">*</span></label>
                                              <select class="form-control" wire:model.debounce.300ms="selectedCurrency" required>
                                                  <option value="">Select Currency</option>
                                                  @foreach ($currencies as $currency)
                                                  <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                                  @endforeach
                                              </select>
                                                @error('selectedCurrency') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                            @if (!is_null($selectedCurrency))
                                                @if ($company)
                                                    @if ($selectedCurrency != $company->currency_id)
                                                    <div class="form-group">
                                                        <label for="customer">Conversion Rate<span class="required" style="color: red">*</span></label>
                                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate {{$selected_currency ? "From ".$selected_currency->name : ""}} {{$company->currency ? "To ".$company->currency->name : ""}}" required>
                                                        @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                                        <small style="color: green">{{$selected_currency ? " 1 ".$selected_currency->name." is how much in" : ""}} {{$company->currency ? $company->currency->name." ?" : ""}}</small>
                                                        <small>{{$exchange_amount ? "The converted amount is: ".$exchange_amount : ""}}</small> <br>
                                                    </div> 
                                                    @endif
                                                @endif
                                            @endif
                                        </div>
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label for="stops">Bank Accounts</label>
                                                <select wire:model.debounce.300ms="bank_account_id" class="form-control" multiple>
                                                        <option value="">Select Bank Account</option>
                                                        @foreach ($bank_accounts as $bank_account)
                                                            <option value="{{ $bank_account->id }}">{{ $bank_account->name }} {{ $bank_account->account_name }} {{ $bank_account->account_number }} {{ $bank_account->currency ? $bank_account->currency->name : "" }}({{ $bank_account->currency ? $bank_account->currency->symbol : "" }})</option>
                                                        @endforeach
                                                </select>
                                                <small>  <a href="#" data-toggle="modal" data-target="#bank_accountModal"><i class="fa fa-plus-square-o"></i> New Bank Account</a></small><a href="#" wire:click.prevent="refresh('bank_accounts')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                                 <br>
                                                <small>You can select multiple bank accounts visible on invoice.</small>
                                                @error('bank_account_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                    </div>

                                    <br>
                                    <div class="row">
                                        <div class="col-md-10">
                                            <input type="checkbox" wire:model.debounce.300ms="for_trips"   class="line-style" />
                                            <label for="one" class="radio-label">Create quote for trips</label>
                                            @error('for_trips') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>

                                    @if (isset($for_trips) && $for_trips == True)
                                    <h5 class="underline mt-30">Trip Detail(s)</h5>

                                    @foreach ($quotation_items as $key => $value)
                                    <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="currency">From<span class="required" style="color: red">*</span></label>
                                          <select class="form-control" wire:model.debounce.300ms="current_from.{{$key}}" required>
                                              <option value="">Select Origin</option>
                                              @foreach ($destinations as $destination)
                                                  <option value="{{$destination->id}}">{{$destination->country ? $destination->country->name : ""}} {{$destination->city}}</option>
                                              @endforeach
                                          </select>
                                            @error('current_from.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small>  <a href="{{ route('destinations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Destination</a></small><a href="#" wire:click.prevent="refresh('destinations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="currency">Loading Point(s)</label>
                                          <select class="form-control" wire:model.debounce.300ms="current_loading_point_id.{{$key}}" >
                                              <option value="">Select Loading Point</option>
                                              @foreach ($loading_points as $loading_point)
                                                  <option value="{{$loading_point->id}}">{{$loading_point->name}}</option>
                                              @endforeach
                                          </select>
                                            @error('current_loading_point_id.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small>  <a href="{{ route('loading_points.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Loading Point</a></small><a href="#" wire:click.prevent="refresh('loading_points')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="to">To<span class="required" style="color: red">*</span></label>
                                          <select class="form-control" wire:model.debounce.300ms="current_to.{{$key}}" required>
                                              <option value="">Select Destination</option>
                                              @foreach ($destinations as $destination)
                                                  <option value="{{$destination->id}}">{{$destination->country ? $destination->country->name : ""}} {{$destination->city}}</option>
                                              @endforeach
                                          </select>
                                            @error('current_to.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small>  <a href="{{ route('destinations.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Destination</a></small><a href="#" wire:click.prevent="refresh('destinations')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="currency">Offloading Point(s)</label>
                                          <select class="form-control" wire:model.debounce.300ms="current_offloading_point_id.{{$key}}" >
                                              <option value="">Select Offloading Point</option>
                                              @foreach ($offloading_points as $offloading_point)
                                              <option value="{{$offloading_point->id}}">{{$offloading_point->name}}</option>
                                              @endforeach
                                          </select>
                                            @error('current_offloading_point_id.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                            <small>  <a href="{{ route('offloading_points.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Offloading Point</a></small><a href="#" wire:click.prevent="refresh('offloading_points')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                        </div>
                                    </div>
                                </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="customer">Cargo(s)<span class="required" style="color: red">*</span></label>
                                              <select class="form-control" wire:model.debounce.300ms="currentSelectedCargo.{{$key}}" required>
                                                  <option value="">Select Cargo</option>
                                                  @foreach ($cargos as $cargo)
                                                      <option value="{{$cargo->id}}">{{$cargo->name}}</option>
                                                  @endforeach
                                              </select>
                                                @error('currentSelectedCargo.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                                <small>  <a href="{{ route('cargos.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Cargo</a></small><a href="#" wire:click.prevent="refresh('cargos')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                            </div>
                                        </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="amount">Weight(Tons)<span class="required" style="color: red">*</span></label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="current_weight.{{$key}}" required>
                                            @error('current_weight.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label for="amount">Qty<span class="required" style="color: red">*</span></label>
                                            <input type="number" step="any" min="1" class="form-control" wire:model.debounce.300ms="current_qty.{{$key}}"  required>
                                            @error('current_qty.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="rate">Freight<span class="required" style="color: red">*</span></label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="current_amount.{{$key}}" required>
                                            @error('current_amount.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="subheading">Taxes</label>
                                            <select wire:model.debounce.300ms="currentSelectedTax.{{$key}}"  class="form-control">
                                                <option value="">Select Tax</option>
                                                    @foreach ($tax_accounts as $tax)
                                                       <option value="{{$tax->id}}">{{$tax->abbreviation}} {{$tax->rate ? $tax->rate."%" : ""}}</option> 
                                                    @endforeach
                                                </select>
                                                <small><a href="{{ route('accounts.tax') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small><a href="#" wire:click.prevent="refresh('taxes')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                            @error('currentSelectedTax.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="currency">From<span class="required" style="color: red">*</span></label>
                                          <select class="form-control" wire:model.debounce.300ms="from.{{ $value }}" required>
                                              <option value="">Select Origin</option>
                                              @foreach ($destinations as $destination)
                                                  <option value="{{$destination->id}}">{{$destination->country ? $destination->country->name : ""}} {{$destination->city}}</option>
                                              @endforeach
                                          </select>
                                            @error('from.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="currency">Loading Point</label>
                                          <select class="form-control" wire:model.debounce.300ms="loading_point_id.{{ $value }}" >
                                              <option value="">Select Loading Point</option>
                                              @foreach ($loading_points as $loading_point)
                                                  <option value="{{$loading_point->id}}">{{$loading_point->name}}</option>
                                              @endforeach
                                          </select>
                                            @error('loading_point_id.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="to">To<span class="required" style="color: red">*</span></label>
                                          <select class="form-control" wire:model.debounce.300ms="to.{{ $value }}" required>
                                              <option value="">Select Destination,</option>
                                              @foreach ($destinations as $destination)
                                                  <option value="{{$destination->id}}">{{$destination->country ? $destination->country->name : ""}} {{$destination->city}}</option>
                                              @endforeach
                                          </select>
                                            @error('to.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="currency">Offloading Point</label>
                                          <select class="form-control" wire:model.debounce.300ms="offloading_point_id.{{ $value }}" >
                                              <option value="">Select Offloading Point</option>
                                              @foreach ($offloading_points as $offloading_point)
                                              <option value="{{$offloading_point->id}}">{{$offloading_point->name}}</option>
                                              @endforeach
                                          </select>
                                            @error('offloading_point_id.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="customer">Cargo(s)<span class="required" style="color: red">*</span></label>
                                              <select class="form-control" wire:model.debounce.300ms="selectedCargo.{{ $value }}" required>
                                                  <option value="">Select Cargo</option>
                                                  @foreach ($cargos as $cargo)
                                                      <option value="{{$cargo->id}}">{{$cargo->name}}</option>
                                                  @endforeach
                                              </select>
                                                @error('selectedCargo.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                       
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="amount">Weight(Tons)<span class="required" style="color: red">*</span></label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="weight.{{ $value }}"  required>
                                            @error('weight.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label for="amount">Qty</label>
                                            <input type="number" step="any" min="1" class="form-control" wire:model.debounce.300ms="qty.{{ $value }}" >
                                            @error('qty.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                  
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="rate">Freight<span class="required" style="color: red">*</span></label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="amount.{{ $value }}"  required>
                                            @error('amount.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="subheading">Taxes</label>
                                            <select wire:model.debounce.300ms="selectedTax.{{$value}}"  class="form-control">
                                                <option value="">Select Tax</option>
                                                    @foreach ($tax_accounts as $tax)
                                                       <option value="{{$tax->id}}">{{$tax->abbreviation}} </option> 
                                                    @endforeach
                                                </select>
                                                <small><a href="{{ route('accounts.tax') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small><a href="#" wire:click.prevent="refresh('taxes')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
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
                                            <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i>Trip</button>
                                        </div>
                                    </div>
                                </div>
                            @else
                            <h5 class="underline mt-30">Product(s) & Service(s)</h5>
                                    @foreach ($quotation_items as $key => $value)
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="country">Items<span class="required" style="color: red">*</span></label>
                                                    <select wire:model.debounce.300ms="currentSelectedProduct.{{$key}}" class="form-control" required>
                                                    <option value="">Select Item</option>
                                                        @foreach ($products as $product)
                                                        <option value="{{$product->id}}">{{$product->brand ? $product->brand->name : ""}} {{$product->name}} {{$product->identification_number ? "ID#:".$product->identification_number : ""}}</option> 
                                                        @endforeach
                                                    </select>
                                                    <small>  <a href="#"  wire:click="showItem('current', {{ $key }})"><i class="fa fa-plus-square-o"></i> New Product / Service</a></small><a href="#" wire:click.prevent="refresh('products')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                                @error('currentSelectedProduct.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="name">Description</label>
                                               <textarea wire:model.debounce.300ms="current_description.{{$key}}" class="form-control" cols="30" rows="2" placeholder="Enter Item Description"></textarea>
                                                @error('current_description.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                                <input type="number" class="form-control" wire:model.debounce.300ms="current_qty.{{$key}}"  required >
                                                @error('current_qty.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="name">Rate<span class="required" style="color: red">*</span></label>
                                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="current_amount.{{$key}}"  required >
                                                @error('current_amount.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="subheading">Taxes</label>
                                                <select wire:model.debounce.300ms="currentSelectedTax.{{$key}}"  class="form-control">
                                                    <option value="">Select Tax</option>
                                                        @foreach ($tax_accounts as $tax)
                                                           <option value="{{$tax->id}}">{{$tax->abbreviation}} {{$tax->rate ? $tax->rate."%" : ""}}</option> 
                                                        @endforeach
                                                    </select>
                                                    <small><a href="{{ route('accounts.tax') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small><a href="#" wire:click.prevent="refresh('taxes')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                                @error('currentSelectedTax.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                     <label for="country">Items<span class="required" style="color: red">*</span></label>
                                                        <select wire:model.debounce.300ms="selectedProduct.{{ $value }}" class="form-control" required>
                                                            <option value="">Select Item</option>
                                                            @foreach ($products as $product)
                                                            <option value="{{$product->id}}">{{$product->brand ? $product->brand->name : ""}} {{$product->name}} {{$product->identification_number ? "ID#:".$product->identification_number : ""}}</option> 
                                                            @endforeach
                                                        </select>
                                                        <small>  <a href="#"  wire:click="showItem('new', {{ $value }})"><i class="fa fa-plus-square-o"></i> New Product / Service</a></small>
                                                    @error('selectedProduct.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Description</label>
                                                       <textarea wire:model.debounce.300ms="description.{{ $value }}" class="form-control" cols="30" rows="2" placeholder="Enter Item Description"></textarea>
                                                        @error('description.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                                        <input type="number" class="form-control" wire:model.debounce.300ms="qty.{{ $value }}"  required >
                                                        @error('qty.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label for="name">Rate<span class="required" style="color: red">*</span></label>
                                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount.{{ $value }}"  required >
                                                        @error('amount.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                               
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label for="subheading">Taxes</label>
                                                        <select wire:model.debounce.300ms="selectedTax.{{$value}}"  class="form-control">
                                                            <option value="">Select Tax</option>
                                                                @foreach ($tax_accounts as $tax)
                                                                   <option value="{{$tax->id}}">{{$tax->abbreviation}} </option> 
                                                                @endforeach
                                                            </select>
                                                            <small><a href="{{ route('accounts.tax') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small><a href="#" wire:click.prevent="refresh('taxes')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
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
                                    @endif
        
                                    <br>
                                    <h5 class="underline mt-30">Additional Costs</h5>

                                    @foreach ($current_additional_costs as $key => $value)
                                        <div class="row">  
                                            <div class="col-md-7">
                                                <div class="form-group">
                                                     <label for="country">Cost Items<span class="required" style="color: red">*</span></label>
                                                        <select wire:model.debounce.300ms="current_cost_item_id.{{ $key }}" class="form-control" required>
                                                            <option value="">Select Item</option>
                                                            @foreach ($cost_items as $item)
                                                            <option value="{{$item->id}}">{{$item->name}}</option> 
                                                            @endforeach
                                                        </select>
                                                        <small><a href="#"  wire:click="showCost({{$key}})" ><i class="fa fa-plus-square-o"></i> New Cost</a></small> 
                                                    @error('current_cost_item_id.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="name">Total<span class="required" style="color: red">*</span></label>
                                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="current_cost_total.{{ $key }}"  required >
                                                        @error('current_cost_total.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group" style="margin-top: 29px; ">
                                                        <a href="#" wire:click.prevent="removeAdditionalCostShow({{ $value->id }})"  ><i class="fa fa-trash color-danger"></i></a>
                                                    </div>
                                                </div>      
                                        </div>
                                    @endforeach
                                   
                                     @foreach ($cost_inputs as $key => $value)
                                        <div class="row">  
                                            <div class="col-md-7">
                                                <div class="form-group">
                                                     <label for="country">Cost Items<span class="required" style="color: red">*</span></label>
                                                        <select wire:model.debounce.300ms="cost_item_id.{{ $value }}" class="form-control" required>
                                                            <option value="">Select Item</option>
                                                            @foreach ($cost_items as $item)
                                                            <option value="{{$item->id}}">{{$item->name}}</option> 
                                                            @endforeach
                                                        </select>
                                                        <small><a href="#"  wire:click="showCost({{$value}})" ><i class="fa fa-plus-square-o"></i> New Cost</a></small> 
                                                    @error('cost_item_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="name">Total<span class="required" style="color: red">*</span></label>
                                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="cost_total.{{ $value }}"  required >
                                                        @error('cost_total.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label for=""></label>
                                                        <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="removeCost({{$key}})"> <i class="fa fa-times"></i></button>
                                                    </div>
                                                </div>
                                        </div>
                                    @endforeach
                                     <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="addCost({{$c}})"> <i class="fa fa-plus"></i>Additional Cost</button>
                                            </div>
                                        </div>
                                    </div> 
                                    <br>
                                    <div class="row">
                                        @if ($is_discount == False)
                                        <div class="col-md-3" >
                                            <a href="#" wire:click.prevent="discount"><i class="fa fa-plus-square-o"></i> Add a discount</a> 
                                        </div>
                                        @else
                                        <div class="col-md-2">
                                            <label for="subheading">Discount</label>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <input type="text" class="form-control" wire:model.debounce.300ms="discount_description" placeholder="Description (optional)">
                                                @error('discount_description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="number" class="form-control" wire:model.debounce.300ms="discount_amount" required>
                                                @error('discount_amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        @php
                                            if (isset($selectedCurrency)) {
                                                $set_currency = App\Models\Currency::find($selectedCurrency);
                                            }
                                        @endphp
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <select wire:model.debounce.300ms="discount_unit"  class="form-control" required>
                                                    <option value="">Select Option</option>
                                                    <option value="currency">
                                                        @if (isset($set_currency))
                                                        {{$set_currency->name}} {{$set_currency->symbol}}
                                                        @else
                                                        {{Auth::user()->employee->company->currency->name}} {{Auth::user()->employee->company->currency->symbol}}
                                                        @endif
                                                    </option>
                                                    <option value="percentage">%</option>
                                                    </select>
                                                @error('discount_unit') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group" style="margin-top: 4px; ">
                                                <a href="#" wire:click.prevent="removeDiscount({{$quotation->id}})"  ><i class="fa fa-trash color-danger"></i></a>
                                            </div>
                                        </div> 
                                        
                                        @endif
                                       
                                    </div>
                                    <br>

                                <div class="row">
                                    <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="memo">Notes / Terms</label>
                                       <textarea class="form-control" wire:model.debounce.300ms="memo" cols="30" rows="2" placeholder="Enter notes or terms of service that are visible to your customer." ></textarea>
                                        @error('memo') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="footer">Footer</label>
                                           <textarea class="form-control" wire:model.debounce.300ms="footer" cols="30" rows="2" placeholder="Enter footer for your quotations eg (Tax Information, Thank you note)."></textarea>
                                            @error('footer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                        </div>
                                </div>
                                </div>
                                <div class="modal-footer">
                                    <div class="btn-group" role="group">
                                        <button type="button" onclick="goBack()" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-arrow-left"></i>Back</button>
                                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.container-fluid -->
    </section>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="removeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                   <center> <strong>Are you sure you want to delete this Quotation Item</strong> </center>
                </div>
                <form wire:submit.prevent="removeQuotationItem()" >
                <div class="modal-footer no-border">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        @if ($quotation_items->count() > 1)
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="removeCostModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                   <center> <strong>Are you sure you want to delete this Cost Item</strong> </center>
                </div>
                <form wire:submit.prevent="removeCostItem()" >
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="bank_accountModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="bank_account">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Bank Account <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="storeBankAccount()" >
                <div class="modal-body">
                    <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="title">Bank<span class="required" style="color: red">*</span></label>
                            <input type="text" class="form-control"  wire:model.debounce.300ms="bank_name" placeholder="Enter Bank Name" required />
                            @error('bank_name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="file">Account Name<span class="required" style="color: red">*</span></label>
                            <input type="text" class="form-control"  wire:model.debounce.300ms="account_name" placeholder="Enter Account Name" required/>
                            @error('account_name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                   
                   
                </div>
                <div class="row">
                   
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="file">Account Number<span class="required" style="color: red">*</span></label>
                            <input type="text" class="form-control"  wire:model.debounce.300ms="account_number" placeholder="Enter Account Number" required/>
                            @error('account_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="title">Account Currency<span class="required" style="color: red">*</span></label>
                            <select class="form-control" wire:model.debounce.300ms="bank_currency_id" required>
                                <option value="">Select Currency</option>
                              @foreach ($currencies as $currency)
                                  <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                              @endforeach
                            </select>
                            @error('bank_currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="expiry_date">Account Branch</label>
                                <input type="text" class="form-control"  wire:model.debounce.300ms="branch" placeholder="Enter Branch"  />
                                @error('branch') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="expiry_date">Branch Code</label>
                                <input type="text" class="form-control"  wire:model.debounce.300ms="branch_code" placeholder="Enter Branch Code" />
                                @error('branch_code') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="expiry_date">Swift Code</label>
                                <input type="text" class="form-control"  wire:model.debounce.300ms="swift_code" placeholder="Enter Swift Code" />
                                @error('swift_code') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="customerModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Add Customer <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="storeCustomer()" >

                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Name<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="customer_name" placeholder="Name of a business or person." required />
                        @error('customer_name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" wire:model.debounce.300ms="email" placeholder="Enter Email" />
                                @error('email') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phonenumber">Phonenumber</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="phonenumber" placeholder="Enter Phonenumber" />
                                @error('phonenumber') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <h5 class="underline mt-30">Billing Details</h5>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="country">Currency</label>
                               <select class="form-control" wire:model.debounce.300ms="currency_id">
                                    <option value="">Select Currency</option>
                                    @foreach ($currencies as $currency)
                                    <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                    @endforeach
                               </select>
                                @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="vat_number">VAT#</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="vat_number" placeholder="Customer Vat#" >
                                @error('vat_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="tin_number">TIN#</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="tin_number" placeholder="Customer Tin#" >
                                @error('tin_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="country">Country</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="country" placeholder="Enter Country" />
                                @error('country') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="city">City</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="city" placeholder="Enter City"  />
                                @error('city') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Suburb</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="suburb" placeholder="Enter Suburb"  />
                                @error('suburb') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="street_address">Street Address</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="street_address" placeholder="Enter Street Address"/>
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
                                <br>
                                <small>Allow this product or service to be added to Invoices.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-10">
                                <input type="checkbox" wire:model.debounce.300ms="buy"   class="line-style" />
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
                            <small><a href="{{ route('accounts.tax') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small><a href="#" wire:click.prevent="refresh('taxes')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
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
