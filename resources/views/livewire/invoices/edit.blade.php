<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Edit Invoice</h5>
                            </div>
                        </div>
                        <div class="panel-body">
                            <form wire:submit.prevent="update()" >
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="name">Invoice Number<span class="required" style="color: red">*</span></label>
                                                <input type="text" class="form-control" wire:model.debounce.300ms="invoice_number" placeholder="Enter Invoice Number" required >
                                                @error('invoice_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                <small style="color: green">
                                                    @if (Auth::user()->employee->company->invoice_serialize_by_customer == True)
                                                    Invoice number is serialized by customer
                                                    @else   
                                                    Invoice serialization default
                                                    @endif
                                                   </small>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-10" style="margin-top:32px">
                                                <input type="checkbox" wire:model.debounce.300ms="fiscalize_invoice"   class="line-style" />
                                                <label for="one" class="radio-label">Fiscalize invoice</label>
                                                @error('fiscalize_invoice') <span class="text-danger error">{{ $message }}</span>@enderror
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
                                               <small>  <a href="#" data-toggle="modal" data-target="#customerModal"><i class="fa fa-plus-square-o"></i> New Customer</a></small> 
                                                @error('selectedCustomer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                       
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="name">S.O Number</label>
                                                <input type="text" class="form-control" wire:model.debounce.300ms="sales_order_number">
                                                @error('sales_order_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="name">P.O Number</label>
                                                <input type="text" class="form-control" wire:model.debounce.300ms="purchase_order_number">
                                                @error('purchase_order_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="name">PAT Number</label>
                                                <input type="text" class="form-control" wire:model.debounce.300ms="pat_number">
                                                @error('pat_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="name">Invoice Date<span class="required" style="color: red">*</span></label>
                                                <input type="date" class="form-control" wire:model.debounce.300ms="date" wire:change="invoiceDate()" placeholder="Enter Invoice Date" required >
                                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="date">Payment Due</label>
                                                <input type="date" class="form-control" wire:model.debounce.300ms="expiry" placeholder="Enter Payment Due Date"  >
                                                @error('expiry') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="vat">Currencies<span class="required" style="color: red">*</span></label>
                                               <select class="form-control" wire:model.debounce.300ms="selectedCurrency" required>
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
                                                <div class="row">
                                                    <div class="form-group">
                                                        <label for="customer">Conversion Rate<span class="required" style="color: red">*</span></label>
                                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate" required>
                                                        @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                                        <small>{{$exchange_amount ? "The converted amount is: ".$exchange_amount : ""}}</small>
                                                    </div> 
                                                </div>
                                                @endif
                                            @endif
                                        @endif 
                                        </div>
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label for="vat">Bank Accounts</label>
                                               <select class="form-control" wire:model.debounce.300ms="bank_account_id" multiple >
                                                <option value="">Select Bank Account</option>
                                                @foreach ($bank_accounts as $bank_account)
                                                <option value="{{ $bank_account->id }}">{{ $bank_account->name }} {{ $bank_account->account_name }} {{ $bank_account->account_number }} {{ $bank_account->currency ? $bank_account->currency->name : "" }}({{ $bank_account->currency ? $bank_account->currency->symbol : "" }})</option>
                                                @endforeach
                                               </select>
                                               <small>  <a href="#" data-toggle="modal" data-target="#bank_accountModal"><i class="fa fa-plus-square-o"></i> New Bank Account</a></small> 
                                               <br>
                                               <small>You can select multiple bank accounts visible on invoice.</small>
                                                @error('bank_account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                            </div>
                                    </div>

                                    <br>
                                    <div class="row">
                                        <div class="col-md-10">
                                            <input type="checkbox" wire:model.debounce.300ms="from_trips"   class="line-style" />
                                            <label for="one" class="radio-label">Select from trips</label>
                                            @error('from_trips') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                    </div>

                                    @if (isset($from_trips) && $from_trips == true)
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                        Filter By
                                        </span>
                                        <select wire:model.debounce.300ms="trip_filter" class="form-control" aria-label="..." >
                                            <option value="created_at">Trip Created At</option>
                                            <option value="start_date">Trip Started At</option>
                                        </select>
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-2" >
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                        From
                                        </span>
                                        <input type="date" wire:model.debounce.300ms="from"  class="form-control" aria-label="...">
                                        
                                            </div>
                                        
                                            <!-- /input-group -->
                                        </div>
                                        <div class="col-md-2" style="margin-left:20px;" >
                                            <div class="input-group">
                                        <span class="input-group-addon">
                                            To
                                            </span>
                                            <input type="date" wire:model.debounce.300ms="to"  class="form-control" aria-label="...">
                                            </div>
                                            <!-- /input-group -->
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search trips using: trip#, trip ref, waybill#, customer, HRN...">
                                            </div>
                                        </div>
                                    </div>
                                    @php
                                            $selected_invoice_items = App\Models\InvoiceItem::all();
                                            foreach($selected_invoice_items as $invoice_item){
                                                    $trip_ids[] = $invoice_item->trip_id;
                                            }   
                                           
                                    @endphp

                                    @foreach ($invoice_items as $key => $value)

                                    <div class="row">
                                       
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label for="subheading">Trips<span class="required" style="color: red">*</span></label>
                                                <select wire:model.debounce.300ms="selectedCurrentTrip.{{$key}}"  class="form-control" required size="4">
                                                    <option value="">Select Trip</option>
                                                        @if (isset($selectedCurrency))
                                                            @foreach ($trips->where('currency_id', $selectedCurrency) as $trip)
                                                                @if (isset($trip_ids))
                                                                    @if (in_array($trip->id,$trip_ids))
                                                                    <option value="{{$trip->id}}" style="color: orange">{{$trip->trip_number ? $trip->trip_number." |" : ""}} {{ $trip->trip_ref ? $trip->trip_ref." |" : "" }} {{ isset($pod) ? $pod->document_number." | " : "" }} {{$trip->currency ? $trip->currency->name : ""}} {{$trip->currency ? $trip->currency->symbol : ""}}{{$trip->turnover ? number_format($trip->turnover,2)." |" : ""}} {{$trip->horse ? $trip->horse->registration_number : ""}} | {{$trip->customer ? $trip->customer->name : ""}} </option> 
                                                                    @else
                                                                        <option value="{{$trip->id}}">{{$trip->trip_number ? $trip->trip_number." |" : ""}} {{ $trip->trip_ref ? $trip->trip_ref." |" : "" }} {{ isset($pod) ? $pod->document_number." | " : "" }} {{$trip->currency ? $trip->currency->name : ""}} {{$trip->currency ? $trip->currency->symbol : ""}}{{$trip->turnover ? number_format($trip->turnover,2)." |" : ""}} {{$trip->horse ? $trip->horse->registration_number : ""}} | {{$trip->customer ? $trip->customer->name : ""}}</option>
                                                                    @endif
                                                                @else
                                                                    <option value="{{$trip->id}}">{{$trip->trip_number ? $trip->trip_number." |" : ""}} {{ $trip->trip_ref ? $trip->trip_ref." |" : "" }} {{ isset($pod) ? $pod->document_number." | " : "" }} {{$trip->currency ? $trip->currency->name : ""}} {{$trip->currency ? $trip->currency->symbol : ""}}{{$trip->turnover ? number_format($trip->turnover,2)." |" : ""}} {{$trip->horse ? $trip->horse->registration_number : ""}} | {{$trip->customer ? $trip->customer->name : ""}}</option>
                                                                @endif
                                                            @endforeach  
                                                        @endif 
                                                    </select>
                                                    <small style="color: green">NB: All invoiced trips will appear in orange</small>
                                                @error('selectedCurrentTrip.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="name">Description</label>
                                            <textarea wire:model.debounce.300ms="current_description.{{$key}}" class="form-control" cols="30" rows="4" placeholder="Enter Item Description"></textarea>
                                                @error('current_description.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <label for="date">Qty<span class="required" style="color: red">*</span></label>
                                                <input type="number"  class="form-control" wire:model.debounce.300ms="current_qty.{{$key}}" {{$recorded_payments > 0  ? "disabled" : ""}}   required>
                                                @error('current_qty.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="subheading">Amount<span class="required" style="color: red">*</span></label>
                                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="current_amount.{{$key}}" {{$recorded_payments > 0 ? "disabled" : ""}}   required/>
                                                @error('current_amount.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <label for="subheading">Taxes</label>
                                                <select wire:model.debounce.300ms="selectedCurrentTax.{{$key}}"  class="form-control" {{$recorded_payments > 0 ? "disabled" : ""}}>
                                                    <option value=""></option>
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
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="subheading">Trips<span class="required" style="color: red">*</span></label>
                                                    <select wire:model.debounce.300ms="selectedTrip.{{$value}}"  class="form-control" required size="4">
                                                        <option value="">Select Trip</option>
                                                        @if (isset($selectedCurrency))
                                                                @foreach ($trips->where('currency_id', $selectedCurrency) as $trip)
                                                                    @if (isset($trip_ids))
                                                                        @if (in_array($trip->id,$trip_ids))
                                                                        <option value="{{$trip->id}}" style="color: orange">{{$trip->trip_number ? $trip->trip_number." |" : ""}} {{ $trip->trip_ref ? $trip->trip_ref." |" : "" }} {{ isset($pod) ? $pod->document_number." | " : "" }} {{$trip->currency ? $trip->currency->name : ""}} {{$trip->currency ? $trip->currency->symbol : ""}}{{$trip->turnover ? number_format($trip->turnover,2)." |" : ""}} {{$trip->horse ? $trip->horse->registration_number : ""}} | {{$trip->customer ? $trip->customer->name : ""}} </option> 
                                                                        @else
                                                                            <option value="{{$trip->id}}">{{$trip->trip_number ? $trip->trip_number." |" : ""}} {{ $trip->trip_ref ? $trip->trip_ref." |" : "" }} {{ isset($pod) ? $pod->document_number." | " : "" }} {{$trip->currency ? $trip->currency->name : ""}} {{$trip->currency ? $trip->currency->symbol : ""}}{{$trip->turnover ? number_format($trip->turnover,2)." |" : ""}} {{$trip->horse ? $trip->horse->registration_number : ""}} | {{$trip->customer ? $trip->customer->name : ""}}</option>
                                                                        @endif
                                                                    @else
                                                                        <option value="{{$trip->id}}">{{$trip->trip_number ? $trip->trip_number." |" : ""}} {{ $trip->trip_ref ? $trip->trip_ref." |" : "" }} {{ isset($pod) ? $pod->document_number." | " : "" }} {{$trip->currency ? $trip->currency->name : ""}} {{$trip->currency ? $trip->currency->symbol : ""}}{{$trip->turnover ? number_format($trip->turnover,2)." |" : ""}} {{$trip->horse ? $trip->horse->registration_number : ""}} | {{$trip->customer ? $trip->customer->name : ""}}</option>
                                                                    @endif
                                                                @endforeach  
                                                            @endif 
                                                        </select>
                                                        <small style="color: green">NB: All invoiced trips will appear in orange</small>
                                                    @error('selectedTrip.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="name">Description</label>
                                                <textarea wire:model.debounce.300ms="description.{{$value}}" class="form-control" cols="30" rows="4" placeholder="Enter Item Description"></textarea>
                                                    @error('description.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label for="date">Qty<span class="required" style="color: red">*</span></label>
                                                    <input type="number"  class="form-control" wire:model.debounce.300ms="qty.{{$value}}"   required>
                                                    @error('qty.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label for="subheading">Amount<span class="required" style="color: red">*</span></label>
                                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount.{{$value}}"   required/>
                                                    @error('amount.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label for="subheading">Taxes</label>
                                                    <select wire:model.debounce.300ms="selectedTax.{{$value}}"  class="form-control">
                                                        <option value=""></option>
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
                                                    <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i>Trip</button>
                                                </div>
                                            </div>
                                        </div>
                                    
                                    @elseif(isset($from_trips) && $from_trips == false)
                                    
                                        <h5 class="underline mt-30">Product(s) & Service(s)</h5>
                                        <div class="row">
                                            <div class="col-md-10">
                                                <input type="checkbox" wire:model.debounce.300ms="from_inventory"   class="line-style" />
                                                <label for="one" class="radio-label">Select item(s) from inventory</label>
                                                @error('from_inventory') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                        <br>
                                        @if (isset($from_inventory) && $from_inventory == false)
                                        @foreach ($invoice_items as $key => $value)
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="country">Items<span class="required" style="color: red">*</span></label>
                                                            <select wire:model.debounce.300ms="selectedCurrentProduct.{{$key}}" class="form-control" required>
                                                            <option value="">Select Item</option>
                                                                @foreach ($products as $product)
                                                                <option value="{{$product->id}}">{{$product->brand ? $product->brand->name : ""}} {{$product->name}} {{$product->identification_number ? "ID#:".$product->identification_number : ""}}</option> 
                                                                @endforeach
                                                            </select>
                                                            <small><a href="#" wire:click="showItem({{$value}})"><i class="fa fa-plus-square-o"></i> New Product / Service</a></small> 
                                                        @error('selectedCurrentProduct.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                                                        <input type="number" class="form-control" wire:model.debounce.300ms="current_qty.0" {{$recorded_payments > 0 ? "disabled" : ""}} required >
                                                        @error('current_qty.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label for="name">Rate<span class="required" style="color: red">*</span></label>
                                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="current_amount.{{$key}}" {{$recorded_payments > 0 ? "disabled" : ""}}  required >
                                                        @error('current_amount.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label for="subheading">Taxes</label>
                                                        <select wire:model.debounce.300ms="selectedCurrentTax.{{$key}}"  class="form-control" {{$recorded_payments > 0 ? "disabled" : ""}}>
                                                            <option value=""></option>
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
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label for="country">Items<span class="required" style="color: red">*</span></label>
                                                                <select wire:model.debounce.300ms="selectedProduct.{{ $value }}" class="form-control" required>
                                                                    <option value="">Select Item</option>
                                                                    @foreach ($products as $product)
                                                                    <option value="{{$product->id}}">{{$product->brand ? $product->brand->name : ""}} {{$product->name}} {{$product->identification_number ? "ID#:".$product->identification_number : ""}}</option> 
                                                                    @endforeach
                                                                </select>
                                                                <small>  <a href="#" wire:click="showItem({{$value}})"><i class="fa fa-plus-square-o"></i> New Product / Service</a></small> 
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
                                                                    <option value=""></option>
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
                                        @elseif(isset($from_inventory) && $from_inventory == true)
                                        @foreach ($invoice_items as $key => $value)
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <div class="form-group">
                                                        <label for="horse">Items in Inventory<span class="required" style="color: red">*</span></label>
                                                    <select wire:model.debounce.300ms="selectedCurrentInventory.{{$key}}" class="form-control" required >
                                                        <option value="" selected>Select Item</option>
                                                            @foreach ($inventories as $inventory)
                                                                @if ($inventory->product)
                                                                    @php
                                                                        $product = $inventory->product;
                                                                    @endphp 
                                                                <option value="{{$inventory->id}}">{{$product->product_number}} {{$product->brand ? $product->brand->name : ""}} {{$product->name}} {{$product->identification_number}} | {{$inventory->serial_number ? "SN#: ".$inventory->serial_number : ""}}  </option>
                                                                @endif 
                                                            @endforeach
                                                    </select>
                                                    <small><a href="{{ route('inventories.create') }}" target="_blank"><i class="fa fa-plus-square-o"></i> Add item to inventory</a></small> 
                                                        @error('selectedCurrentInventory.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="name">Description</label>
                                                    <textarea wire:model.debounce.300ms="current_description.{{$key}}" class="form-control" cols="30" rows="2" placeholder="Enter Item Description"></textarea>
                                                        @error('current_description.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label for="name">Item Contents</label>
                                                        <input type="number" min="0" class="form-control" wire:model.debounce.300ms="current_weight.{{$key}}"  {{$recorded_payments > 0 ? "disabled" : ""}} >
                                                        @error('current_weight.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                        <small>Litres, weight, # of pieces or items to invoice eg 10 litres, 1 item</small>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Unit of measure</label>
                                                        <select wire:model.debounce.300ms="current_measurement.{{$key}}" class="form-control" {{$recorded_payments > 0 ? "disabled" : ""}} >
                                                            <option value="" selected>Select Unit</option>
                                                            @foreach ($measurements as $measurement)
                                                            <option value="{{$measurement->name}}">{{$measurement->name}} </option>
                                                            @endforeach
                                                        </select>
                                                        @error('current_measurement.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                                        <input type="number" min="0" max="1" class="form-control" wire:model.debounce.300ms="current_qty.{{$key}}"  required disabled>
                                                        @error('current_qty.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label for="name">Rate<span class="required" style="color: red">*</span></label>
                                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="current_amount.{{$key}}"  required  {{$recorded_payments > 0 ? "disabled" : ""}}>
                                                        @error('current_amount.'.$key) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label for="subheading">Taxes</label>
                                                        <select wire:model.debounce.300ms="selectedCurrentTax.{{$key}}"  class="form-control" {{$recorded_payments > 0 ? "disabled" : ""}}>
                                                            <option value=""></option>
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
                                                    <div class="col-md-8">
                                                        <div class="form-group">
                                                            <label for="horse">Items in Inventory<span class="required" style="color: red">*</span></label>
                                                        <select wire:model.debounce.300ms="selectedInventory.{{$value}}" class="form-control" required >
                                                            <option value="" selected>Select Item</option>
                                                                @foreach ($inventories as $inventory)
                                                                    @if ($inventory->product)
                                                                    @php
                                                                        $product = $inventory->product;
                                                                    @endphp 
                                                                        <option value="{{$inventory->id}}">{{$product->product_number}} {{$product->brand ? $product->brand->name : ""}} {{$product->name}} {{$product->identification_number}} | {{$inventory->serial_number ? "SN#: ".$inventory->serial_number : ""}}  </option>
                                                                    @endif 
                                                                @endforeach
                                                        </select>
                                                            <small><a href="{{ route('inventories.create') }}" target="_blank"><i class="fa fa-plus-square-o"></i> Add item to inventory</a></small> 
                                                            @error('selectedInventory.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="name">Description</label>
                                                        <textarea wire:model.debounce.300ms="description.{{ $value }}" class="form-control" cols="30" rows="2" placeholder="Enter Item Description"></textarea>
                                                            @error('description.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label for="name">Item Contents</label>
                                                            <input type="number" min="0" class="form-control" wire:model.debounce.300ms="weight.{{$value}}"  >
                                                            @error('weight.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                            <small>Litres, weight, # of pieces or items to invoice eg 10 litres, 1 item</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-group">
                                                            <label for="name">Unit of measure</label>
                                                            <select wire:model.debounce.300ms="measurement.{{$value}}" class="form-control"  >
                                                                <option value="" selected>Select Unit</option>
                                                                @foreach ($measurements as $measurement)
                                                                <option value="{{$measurement->name}}">{{$measurement->name}} </option>
                                                                @endforeach
                                                            </select>
                                                            @error('measurement.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-1">
                                                        <div class="form-group">
                                                            <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                                            <input type="number" class="form-control" wire:model.debounce.300ms="qty.{{ $value }}"  required disabled >
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
                                                                <option value=""></option>
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
                                        
                                                <br>
                                            @endforeach
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i>Item</button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                    <br>
                                  
                                   
                                        <div class="row">
                                            @if ($is_discount == False)
                                                @if (!$recorded_payments > 0)
                                                <div class="col-md-3" >
                                                    <a href="#" wire:click.prevent="discount"><i class="fa fa-plus-square-o"></i> Add a discount</a> 
                                                </div>
                                                @endif
                                            @else
                                                <div class="col-md-2">
                                                    <label for="subheading">Discount</label>
                                                </div>
                                                <div class="col-md-5">
                                                    <div class="form-group">
                                                        <input type="text" class="form-control" wire:model.debounce.300ms="discount_description" placeholder="Description (optional)" {{$recorded_payments > 0 ? "disabled" : ""}}>
                                                        @error('discount_description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <input type="number" class="form-control" wire:model.debounce.300ms="discount_amount" {{$recorded_payments > 0 ? "disabled" : ""}} required>
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
                                                        <select wire:model.debounce.300ms="discount_unit"  class="form-control" required {{$recorded_payments > 0 ? "disabled" : ""}}>
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
                                                    <div class="form-group" style="margin-top: 4px; " >
                                                        @if (!$recorded_payments > 0)
                                                        <a href="#" wire:click.prevent="removeDiscount({{$invoice->id}})"  ><i class="fa fa-trash color-danger"></i></a>
                                                        @endif
                                                    </div>
                                                </div>  
                                            @endif
                                        </div>
                                  
                                    <br>
                                    <hr>
                                    <br>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="memo">Notes / Terms</label>
                                            <textarea class="form-control" wire:model.debounce.300ms="memo" cols="30" rows="2" placeholder="Enter notes or terms of service that are visible to your customer."></textarea>
                                                @error('memo') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="footer">Footer</label>
                                               <textarea class="form-control" wire:model.debounce.300ms="footer" cols="30" rows="2" placeholder="Enter a footer for this invoice (eg tax information, thank you note.)"></textarea>
                                                @error('footer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        
                                <div class="modal-footer">
                                    <div class="btn-group" role="group">
                                        <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                                        <button type="submit" class="btn bg-success btn-wide btn-rounded"  wire:loading.attr="disabled" 
                                        wire:loading.class="opacity-50 cursor-not-allowed"><i class="fa fa-refresh"></i>Update</button>
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
                   <center> <strong>Are you sure you want to delete this Invoice Item</strong> </center>
                </div>
                <form wire:submit.prevent="removeInvoiceItem()" >
                <div class="modal-footer no-border">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        @if ($invoice_items->count() > 1)
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
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>
</div>


   