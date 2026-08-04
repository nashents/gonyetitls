<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>New Invoice</h5>
                            </div>
                        </div>
                        <div class="panel-body">
                            <form wire:submit.prevent="store()" >
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
                                            <label for="exampleInputEmail13">Invoice To<span class="required" style="color: red">*</span></label>
                                            <div class="mb-10">
                                                <input type="radio" wire:model.debounce.300ms="invoice_to" value="Customer"  class="line-style"  />
                                                <label for="one" class="radio-label">Customer</label>
                                                <input type="radio" wire:model.debounce.300ms="invoice_to" value="Transporter"  class="line-style"  />
                                                <label for="one" class="radio-label">Transporter</label>
                                            </div> 
                                            @if (isset($invoice_to) && $invoice_to == "Customer" )
                                              <div class="form-group">
                                                <label for="vat"><a href="{{route('customers.index')}}">Customers<span class="required" style="color: red">*</span></a></label>
                                               <select class="form-control" wire:model.debounce.300ms="selectedCustomer" required>
                                                <option value="">Select Customers</option>
                                                @foreach ($customers as $customer)
                                                        <option value="{{ $customer->id }}">{{ $customer->name }} </option>                                        
                                                @endforeach
                                               </select>
                                               <small>  <a href="#" data-toggle="modal" data-target="#customerModal"><i class="fa fa-plus-square-o"></i> New Customer</a></small> 
                                                 <a href="#" wire:click.prevent="refresh('customers')" class="float-end" style="float: right"><i class="fa fa-refresh"></i></a>
                                                @error('selectedCustomer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                            @elseif(isset($invoice_to) && $invoice_to == "Transporter" )
                                              <div class="form-group">
                                                <label for="vat"><a href="{{route('transporters.index')}}">Transporters<span class="required" style="color: red">*</span></a></label>
                                               <select class="form-control" wire:model.debounce.300ms="selectedTransporter" required>
                                                <option value="">Select Transporters</option>
                                                @foreach ($transporters as $transporter)
                                                        <option value="{{ $transporter->id }}">{{ $transporter->name }} </option>                                        
                                                @endforeach
                                               </select>
                                               <small><a href="{{route('transporters.index')}}" target="_blank" ><i class="fa fa-plus-square-o"></i> New Transporter</a></small> 
                                                 <a href="#" wire:click.prevent="refresh('transporters')" class="float-end" style="float: right"><i class="fa fa-refresh"></i></a>
                                                @error('selectedTransporter') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                            @endif   
                                          
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
                                               <select class="form-control" wire:model.debounce.300ms="selectedCurrency" required {{$invoice_to == "Transporter" ? "disabled" : ""}}>
                                                <option value="">Select Currency</option>
                                                @foreach ($currencies as $currency)
                                                <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>                                              
                                                @endforeach
                                               </select>
                                                @error('selectedCurrency') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                                                <label for="vat">Bank Accounts</label>
                                               <select class="form-control" wire:model.debounce.300ms="bank_account_id" multiple >
                                                <option value="">Select Bank Account</option>
                                                @foreach ($bank_accounts as $bank_account)
                                                <option value="{{ $bank_account->id }}">{{ $bank_account->name }} {{ $bank_account->account_name }} {{ $bank_account->account_number }} {{ $bank_account->currency ? $bank_account->currency->name : "" }}({{ $bank_account->currency ? $bank_account->currency->symbol : "" }})</option>
                                                @endforeach
                                               </select>
                                               <small>  <a href="#" data-toggle="modal" data-target="#bank_accountModal"><i class="fa fa-plus-square-o"></i> New Bank Account</a></small><a href="#" wire:click.prevent="refresh('bank_accounts')" class="float-end" style="float: right"><i class="fa fa-refresh"></i></a> 
                                               <br>
                                               <small>You can select multiple bank accounts visible on invoice.</small>
                                                @error('bank_account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                    <h5 class="underline mt-30">Invoice Items</h5>
                                    <label for="one" class="radio-label">Select source for invoice items.</label>
                                    <div class="mb-10">
                                        @if ($invoice_to == "Transporter")
                                            <input type="radio" wire:model.debounce.300ms="source" value="Booking"  class="line-style"  />
                                            <label for="one" class="radio-label">Bookings</label>
                                        @endif
                                        <input type="radio" wire:model.debounce.300ms="source" value="Generic"  class="line-style"  />
                                        <label for="one" class="radio-label">Generic</label>
                                        <input type="radio" wire:model.debounce.300ms="source" value="Inventory"  class="line-style"  />
                                        <label for="one" class="radio-label">Inventory</label>
                                        @if ($company->type == "Rental")
                                            <input type="radio" wire:model.debounce.300ms="source" value="Rental"  class="line-style"  />
                                            <label for="one" class="radio-label">Rentals</label>
                                        @endif
                                        <input type="radio" wire:model.debounce.300ms="source" value="TTO"  class="line-style"  />
                                        <label for="one" class="radio-label">TTO</label>
                                        <input type="radio" wire:model.debounce.300ms="source" value="Trip"  class="line-style"  />
                                        <label for="one" class="radio-label">Trips</label>
                                        @if ($invoice_to == "Customer")
                                            <input type="radio" wire:model.debounce.300ms="source" value="Transport Order"  class="line-style"  />
                                            <label for="one" class="radio-label">Transport Order</label>
                                        @endif


                                         @error('source') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>

                                    @if ($source == "Trip")
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
                                                    <label for="">Search trips</label>
                                                    <input type="text" wire:model.debounce.300ms="searchTrip" class="form-control" placeholder="Search trips using: trip#, trip ref, waybill#, customer, HRN...">
                                                </div>
                                            </div>
                                            @if ($this->invoice_to == "Customer")
                                                <div class="col-md-7">
                                                    <div class="form-group">
                                                        <label for="name">Select values to use for invoicing?</label>
                                                        <label class="radio-inline">
                                                            <input type="radio" wire:model.debounce.300ms="values" value="scheduled" name="optradio" >Scheduled
                                                        </label>
                                                        <label class="radio-inline">
                                                            <input type="radio" wire:model.debounce.300ms="values" value="loading" name="optradio" >Loading
                                                        </label>
                                                        <label class="radio-inline">
                                                            <input type="radio" wire:model.debounce.300ms="values" value="offloading" name="optradio">Offloading
                                                        </label>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        @php
                                            $invoice_items = App\Models\InvoiceItem::all();
                                            foreach($invoice_items as $invoice_item){
                                                    $trip_ids[] = $invoice_item->trip_id;
                                            }   
                                        @endphp
                                        <div  class="mb-10">
                                            <input type="checkbox" wire:model.debounce.300ms="multi_select"   class="line-style" />
                                            <label for="one" class="radio-label">Multi-select trips</label>
                                            @error('multi_select') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                        @if ($multi_select == True)
                                            <div class="row" >
                                                <div class="col-md-10">
                                                    <div class="form-group">
                                                        <label for="subheading">Trips<span class="required" style="color: red">*</span></label>
                                                        <select wire:model.debounce.300ms="selectedMultiTrip"  class="form-control" required multiple="multiple">
                                                            <option value="">Select Trip</option>
                                                            
                                                                @foreach ($trips as $trip)
                                                                        @if (isset($trip_ids))
                                                                            @if (in_array($trip->id,$trip_ids))
                                                                                <option value="{{$trip->id}}" style="color: orange">
                                                                                    {{$trip->trip_number ? $trip->trip_number: ""}} {{ $trip->trip_ref ? " / ".$trip->trip_ref: "" }} {{ isset($pod) ? "POD#: ".$pod->document_number : "" }} {{$trip->start_date}} {{$trip->currency ? $trip->currency->name : ""}} {{$trip->currency ? $trip->currency->symbol : ""}}{{$trip->freight ? number_format($trip->freight,2) : ""}} 
                                                                                    @if ($trip->horse)
                                                                                        {{$trip->horse ? $trip->horse->registration_number : ""}} {{$trip->horse->fleet_number ? "(".$trip->horse->fleet_number.")" : ""}}
                                                                                    @elseif($trip->vehicle)
                                                                                        {{$trip->vehicle ? $trip->vehicle->registration_number : ""}} {{$trip->vehicle->fleet_number ? "(".$trip->vehicle->fleet_number.")" : ""}}
                                                                                    @endif
                                                                                    {{$trip->customer ? $trip->customer->name : ""}}
                                                                                </option> 
                                                                            @else
                                                                                <option value="{{$trip->id}}">
                                                                                        {{$trip->trip_number ? $trip->trip_number: ""}} {{ $trip->trip_ref ? " / ".$trip->trip_ref: "" }} {{ isset($pod) ? "POD#: ".$pod->document_number : "" }} {{$trip->start_date}} {{$trip->currency ? $trip->currency->name : ""}} {{$trip->currency ? $trip->currency->symbol : ""}}{{$trip->freight ? number_format($trip->freight,2) : ""}} 
                                                                                    @if ($trip->horse)
                                                                                        {{$trip->horse ? $trip->horse->registration_number : ""}} {{$trip->horse->fleet_number ? "(".$trip->horse->fleet_number.")" : ""}}
                                                                                    @elseif($trip->vehicle)
                                                                                        {{$trip->vehicle ? $trip->vehicle->registration_number : ""}} {{$trip->vehicle->fleet_number ? "(".$trip->vehicle->fleet_number.")" : ""}}
                                                                                    @endif
                                                                                    {{$trip->customer ? $trip->customer->name : ""}}
                                                                                </option>
                                                                            @endif
                                                                        @else
                                                                            <option value="{{$trip->id}}">
                                                                                        {{$trip->trip_number ? $trip->trip_number: ""}} {{ $trip->trip_ref ? " / ".$trip->trip_ref: "" }} {{ isset($pod) ? "POD#: ".$pod->document_number : "" }} {{$trip->start_date}} {{$trip->currency ? $trip->currency->name : ""}} {{$trip->currency ? $trip->currency->symbol : ""}}{{$trip->freight ? number_format($trip->freight,2) : ""}} 
                                                                                    @if ($trip->horse)
                                                                                        {{$trip->horse ? $trip->horse->registration_number : ""}} {{$trip->horse->fleet_number ? "(".$trip->horse->fleet_number.")" : ""}}
                                                                                    @elseif($trip->vehicle)
                                                                                        {{$trip->vehicle ? $trip->vehicle->registration_number : ""}} {{$trip->vehicle->fleet_number ? "(".$trip->vehicle->fleet_number.")" : ""}}
                                                                                    @endif
                                                                                        {{$trip->customer ? $trip->customer->name : ""}}
                                                                            </option>
                                                                        @endif
                                                                    @endforeach  
                                                            </select>
                                                            <small style="color: green">NB: All invoiced trips will appear in orange</small>
                                                        @error('selectedMultiTrip') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label for="subheading">Taxes</label>
                                                        <select wire:model.debounce.300ms="selectedMultiTax"  class="form-control">
                                                                <option value="">Select Tax Category</option>
                                                                @foreach ($tax_accounts as $tax)
                                                                <option value="{{$tax->id}}">{{$tax->abbreviation}}</option> 
                                                                @endforeach
                                                            </select>
                                                            <small><a href="{{ route('accounts.tax') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small><a href="#" wire:click.prevent="refresh('taxes')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                                            <small style="color: green">NB: this tax selection will affect all trips selected</small>
                                                        @error('selectedMultiTax') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="row" wire:key="invoice-line-0">
                                                <div class="col-md-5">
                                                    <div class="form-group">
                                                        <label for="subheading">Trips<span class="required" style="color: red">*</span></label>
                                                        <select wire:model.debounce.300ms="selectedTrip.0"  class="form-control" required size="4">
                                                            <option value="">Select Trip</option>
                                                            @foreach ($trips as $trip)
                                                                @if (isset($trip_ids))
                                                                    @if (in_array($trip->id,$trip_ids))
                                                                        <option value="{{$trip->id}}" style="color: orange"
                                                                            @if(in_array($trip->id, $selectedTrip ?? []) && ($selectedTrip[0] ?? null) != $trip->id) 
                                                                                disabled 
                                                                            @endif
                                                                            >
                                                                            {{$trip->trip_number ? $trip->trip_number: ""}} {{ $trip->trip_ref ? " / ".$trip->trip_ref: "" }} {{ isset($pod) ? "POD#: ".$pod->document_number : "" }} {{$trip->start_date}} {{$trip->currency ? $trip->currency->name : ""}} {{$trip->currency ? $trip->currency->symbol : ""}}{{$trip->freight ? number_format($trip->freight,2) : ""}} 
                                                                            @if ($trip->horse)
                                                                                {{$trip->horse ? $trip->horse->registration_number : ""}} {{$trip->horse->fleet_number ? "(".$trip->horse->fleet_number.")" : ""}}
                                                                            @elseif($trip->vehicle)
                                                                                {{$trip->vehicle ? $trip->vehicle->registration_number : ""}} {{$trip->vehicle->fleet_number ? "(".$trip->vehicle->fleet_number.")" : ""}}
                                                                            @endif
                                                                            {{$trip->customer ? $trip->customer->name : ""}}
                                                                        </option> 
                                                                    @else
                                                                        <option value="{{$trip->id}}"
                                                                            @if(in_array($trip->id, $selectedTrip ?? []) && ($selectedTrip[0] ?? null) != $trip->id) 
                                                                                disabled 
                                                                            @endif
                                                                            >
                                                                            {{$trip->trip_number ? $trip->trip_number: ""}} {{ $trip->trip_ref ? " / ".$trip->trip_ref: "" }} {{ isset($pod) ? "POD#: ".$pod->document_number : "" }} {{$trip->start_date}} {{$trip->currency ? $trip->currency->name : ""}} {{$trip->currency ? $trip->currency->symbol : ""}}{{$trip->freight ? number_format($trip->freight,2) : ""}} 
                                                                            @if ($trip->horse)
                                                                                {{$trip->horse ? $trip->horse->registration_number : ""}} {{$trip->horse->fleet_number ? "(".$trip->horse->fleet_number.")" : ""}}
                                                                            @elseif($trip->vehicle)
                                                                                {{$trip->vehicle ? $trip->vehicle->registration_number : ""}} {{$trip->vehicle->fleet_number ? "(".$trip->vehicle->fleet_number.")" : ""}}
                                                                            @endif
                                                                            {{$trip->customer ? $trip->customer->name : ""}}
                                                                        </option>
                                                                    @endif
                                                                @else
                                                                    <option value="{{$trip->id}}"
                                                                        @if(in_array($trip->id, $selectedTrip ?? []) && ($selectedTrip[0] ?? null) != $trip->id) 
                                                                            disabled 
                                                                        @endif
                                                                        >
                                                                        {{$trip->trip_number ? $trip->trip_number: ""}} {{ $trip->trip_ref ? " / ".$trip->trip_ref: "" }} {{ isset($pod) ? "POD#: ".$pod->document_number : "" }} {{$trip->start_date}} {{$trip->currency ? $trip->currency->name : ""}} {{$trip->currency ? $trip->currency->symbol : ""}}{{$trip->freight ? number_format($trip->freight,2) : ""}} 
                                                                        @if ($trip->horse)
                                                                            {{$trip->horse ? $trip->horse->registration_number : ""}} {{$trip->horse->fleet_number ? "(".$trip->horse->fleet_number.")" : ""}}
                                                                        @elseif($trip->vehicle)
                                                                            {{$trip->vehicle ? $trip->vehicle->registration_number : ""}} {{$trip->vehicle->fleet_number ? "(".$trip->vehicle->fleet_number.")" : ""}}
                                                                        @endif
                                                                        {{$trip->customer ? $trip->customer->name : ""}}    
                                                                    </option>
                                                                @endif
                                                            @endforeach  
                                                        </select>
                                                        <small style="color: green">NB: All invoiced trips will appear in orange</small>
                                                        @error('selectedTrip.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Description</label>
                                                    <textarea wire:model.debounce.300ms="description.0" class="form-control" cols="30" rows="4" placeholder="Enter Item Description"></textarea>
                                                        @error('description.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label for="date">Qty<span class="required" style="color: red">*</span></label>
                                                        <input type="number"  class="form-control" wire:model.debounce.300ms="qty.0"   required>
                                                        @error('qty.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label for="subheading">Amount<span class="required" style="color: red">*</span></label>
                                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount.0"   required/>
                                                        @error('amount.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label for="subheading">Taxes</label>
                                                        <select wire:model.debounce.300ms="selectedTax.0"  class="form-control">
                                                                <option value="">Select Tax Category</option>
                                                                @foreach ($tax_accounts as $tax)
                                                                <option value="{{$tax->id}}">{{$tax->abbreviation}}</option> 
                                                                @endforeach
                                                            </select>
                                                            <small><a href="{{ route('accounts.tax') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small><a href="#" wire:click.prevent="refresh('taxes')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                                        @error('selectedTax.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            @foreach ($inputs as $key => $value)
                                            
                                                <div class="row">
                                                    <div class="col-md-12" >
                                                        <input type="checkbox" wire:model.debounce.300ms="is_custom_item.{{ $value }}"   class="line-style" />
                                                        <label for="one" class="radio-label">Add custom item</label>
                                                        @error('is_custom_item.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                        @if(!($is_custom_item[$value] ?? false))
                                                                <label for="subheading">Trips<span class="required" style="color: red">*</span></label>
                                                                <select wire:model.debounce.300ms="selectedTrip.{{$value}}"  class="form-control" required size="4">
                                                                    <option value="">Select Trip</option>
                                                                    @foreach ($trips->where('currency_id', $selectedCurrency) as $trip)
                                                                        @if (isset($trip_ids))
                                                                            @if (in_array($trip->id,$trip_ids))
                                                                            <option value="{{$trip->id}}" style="color: orange"
                                                                                @if(in_array($trip->id, $selectedTrip ?? []) && ($selectedTrip[$value] ?? null) != $trip->id) 
                                                                                disabled 
                                                                                @endif
                                                                            >
                                                                                {{$trip->trip_number ? $trip->trip_number: ""}} {{ $trip->trip_ref ? " / ".$trip->trip_ref: "" }} {{ isset($pod) ? "POD#: ".$pod->document_number : "" }} {{$trip->start_date}} {{$trip->currency ? $trip->currency->name : ""}} {{$trip->currency ? $trip->currency->symbol : ""}}{{$trip->freight ? number_format($trip->freight,2) : ""}} 
                                                                                @if ($trip->horse)
                                                                                    {{$trip->horse ? $trip->horse->registration_number : ""}} {{$trip->horse->fleet_number ? "(".$trip->horse->fleet_number.")" : ""}}
                                                                                @elseif($trip->vehicle)
                                                                                    {{$trip->vehicle ? $trip->vehicle->registration_number : ""}} {{$trip->vehicle->fleet_number ? "(".$trip->vehicle->fleet_number.")" : ""}}
                                                                                @endif
                                                                                {{$trip->customer ? $trip->customer->name : ""}}
                                                                            </option> 
                                                                            @else
                                                                                <option value="{{$trip->id}}"
                                                                                    @if(in_array($trip->id, $selectedTrip ?? []) && ($selectedTrip[$value] ?? null) != $trip->id) 
                                                                                        disabled 
                                                                                    @endif    
                                                                                >
                                                                                {{$trip->trip_number ? $trip->trip_number: ""}} {{ $trip->trip_ref ? " / ".$trip->trip_ref: "" }} {{ isset($pod) ? "POD#: ".$pod->document_number : "" }} {{$trip->start_date}} {{$trip->currency ? $trip->currency->name : ""}} {{$trip->currency ? $trip->currency->symbol : ""}}{{$trip->freight ? number_format($trip->freight,2) : ""}} 
                                                                                @if ($trip->horse)
                                                                                    {{$trip->horse ? $trip->horse->registration_number : ""}} {{$trip->horse->fleet_number ? "(".$trip->horse->fleet_number.")" : ""}}
                                                                                @elseif($trip->vehicle)
                                                                                    {{$trip->vehicle ? $trip->vehicle->registration_number : ""}} {{$trip->vehicle->fleet_number ? "(".$trip->vehicle->fleet_number.")" : ""}}
                                                                                @endif
                                                                                {{$trip->customer ? $trip->customer->name : ""}}   
                                                                                </option>
                                                                            @endif
                                                                        @else
                                                                            <option value="{{$trip->id}}"
                                                                                @if(in_array($trip->id, $selectedTrip ?? []) && ($selectedTrip[$value] ?? null) != $trip->id) 
                                                                                disabled 
                                                                            @endif
                                                                                >
                                                                                {{$trip->trip_number ? $trip->trip_number: ""}} {{ $trip->trip_ref ? " / ".$trip->trip_ref: "" }} {{ isset($pod) ? "POD#: ".$pod->document_number : "" }} {{$trip->start_date}} {{$trip->currency ? $trip->currency->name : ""}} {{$trip->currency ? $trip->currency->symbol : ""}}{{$trip->freight ? number_format($trip->freight,2) : ""}} 
                                                                                @if ($trip->horse)
                                                                                    {{$trip->horse ? $trip->horse->registration_number : ""}} {{$trip->horse->fleet_number ? "(".$trip->horse->fleet_number.")" : ""}}
                                                                                @elseif($trip->vehicle)
                                                                                    {{$trip->vehicle ? $trip->vehicle->registration_number : ""}} {{$trip->vehicle->fleet_number ? "(".$trip->vehicle->fleet_number.")" : ""}}
                                                                                @endif
                                                                                {{$trip->customer ? $trip->customer->name : ""}}    
                                                                            </option>
                                                                        @endif
                                                                    @endforeach  
                                                                </select>
                                                                <small style="color: green">NB: All invoiced trips will appear in orange</small>
                                                                @error('selectedTrip.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                            @else
                                                                <label for="country">Items<span class="required" style="color: red">*</span></label>
                                                                <select wire:model.debounce.300ms="selectedProduct.{{ $value }}" class="form-control" required>
                                                                    <option value="">Select Item</option>
                                                                    @foreach ($products as $product)
                                                                    <option value="{{$product->id}}"
                                                                            @if(in_array($product->id, $selectedProduct ?? []) && ($selectedProduct[$value] ?? null) != $product->id) 
                                                                            disabled 
                                                                        @endif
                                                                        >{{$product->brand ? $product->brand->name : ""}} {{$product->name}} {{$product->identification_number ? "ID#:".$product->identification_number : ""}}</option> 
                                                                    @endforeach
                                                                </select>
                                                                <small>  <a href="#" wire:click="showItem({{$value}})"><i class="fa fa-plus-square-o"></i> New Product / Service</a></small><a href="#" wire:click.prevent="refresh('products')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                                                @error('selectedProduct.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                            @endif
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
                                                                 <option value="">Select Tax Category</option>
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
                                                            <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="remove({{$key}},{{$value}})"> <i class="fa fa-times"></i></button>
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
                                        @endif
                                    @elseif ($source == "TTO")
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="input-group">
                                                    <span class="input-group-addon">
                                                        Filter By
                                                    </span>
                                                    <select wire:model.debounce.300ms="trip_filter" class="form-control" aria-label="..." >
                                                        <option value="created_at">TTO Created At</option>
                                                        <option value="start_date">TTO Started At</option>
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
                                                    <label for="">Search TTOs</label>
                                                    <input type="text" wire:model.debounce.300ms="searchTTO" class="form-control" placeholder="Search TTOs using: trip#, trip ref, waybill#, customer, HRN...">
                                                </div>
                                            </div>
                                            @if ($this->invoice_to == "Customer")
                                                <div class="col-md-7">
                                                    <div class="form-group">
                                                        <label for="name">Select values to use for invoicing?</label>
                                                        <label class="radio-inline">
                                                            <input type="radio" wire:model.debounce.300ms="values" value="scheduled" name="optradio" >Scheduled
                                                        </label>
                                                        <label class="radio-inline">
                                                            <input type="radio" wire:model.debounce.300ms="values" value="loading" name="optradio" >Loading
                                                        </label>
                                                        <label class="radio-inline">
                                                            <input type="radio" wire:model.debounce.300ms="values" value="offloading" name="optradio">Offloading
                                                        </label>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        @php
                                            $invoice_items = App\Models\InvoiceItem::all();
                                            foreach($invoice_items as $invoice_item){
                                                    $trip_transport_order_ids[] = $invoice_item->trip_transport_order_id;
                                            }   
                                        @endphp
                                        <div  class="mb-10">
                                            <input type="checkbox" wire:model.debounce.300ms="multi_select"   class="line-style" />
                                            <label for="one" class="radio-label">Multi-select TTOs</label>
                                            @error('multi_select') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div>
                                        @if ($multi_select == True)
                                            <div class="row" >
                                                <div class="col-md-10">
                                                    <div class="form-group">
                                                        <label for="subheading">TTOs<span class="required" style="color: red">*</span></label>
                                                        <select wire:model.debounce.300ms="selectedMultiTTO"  class="form-control" required multiple="multiple">
                                                            <option value="">Select TTO</option>
                                                            
                                                                @foreach ($trip_transport_orders as $tto)
                                                                        @php
                                                                            $trip = App\Models\Trip::find($tto->trip_id);
                                                                            $transport_order = App\Models\TransportOrder::find($tto->transport_order_id);
                                                                        @endphp
                                                                        @if (isset($trip_transport_order_ids))
                                                                            @if (in_array($tto->id,$trip_transport_order_ids))
                                                                                <option value="{{$tto->id}}" style="color: orange">
                                                                                    {{$trip->trip_number ? $trip->trip_number: ""}} {{ $trip->trip_ref ? " / ".$trip->trip_ref: "" }} {{ isset($pod) ? "POD#: ".$pod->document_number : "" }} {{$trip->start_date}} {{$tto->currency ? $tto->currency->name : ""}} {{$tto->currency ? $tto->currency->symbol : ""}}{{$tto->allocated_freight ? number_format($tto->allocted_freight,2) : ""}} 
                                                                                    @if ($trip->horse)
                                                                                        {{$trip->horse ? $trip->horse->registration_number : ""}} {{$trip->horse->fleet_number ? "(".$trip->horse->fleet_number.")" : ""}}
                                                                                    @elseif($trip->vehicle)
                                                                                        {{$trip->vehicle ? $trip->vehicle->registration_number : ""}} {{$trip->vehicle->fleet_number ? "(".$trip->vehicle->fleet_number.")" : ""}}
                                                                                    @endif
                                                                                    {{$transport_order->customer ? $transport_order->customer->name : ""}}
                                                                                </option> 
                                                                            @else
                                                                                <option value="{{$tto->id}}">
                                                                                        {{$trip->trip_number ? $trip->trip_number: ""}} {{ $trip->trip_ref ? " / ".$trip->trip_ref: "" }} {{ isset($pod) ? "POD#: ".$pod->document_number : "" }} {{$trip->start_date}} {{$tto->currency ? $tto->currency->name : ""}} {{$tto->currency ? $tto->currency->symbol : ""}}{{$tto->allocated_freight ? number_format($tto->allocated_freight,2) : ""}} 
                                                                                    @if ($trip->horse)
                                                                                        {{$trip->horse ? $trip->horse->registration_number : ""}} {{$trip->horse->fleet_number ? "(".$trip->horse->fleet_number.")" : ""}}
                                                                                    @elseif($trip->vehicle)
                                                                                        {{$trip->vehicle ? $trip->vehicle->registration_number : ""}} {{$trip->vehicle->fleet_number ? "(".$trip->vehicle->fleet_number.")" : ""}}
                                                                                    @endif
                                                                                    {{$transport_order->customer ? $transport_order->customer->name : ""}}
                                                                                </option>
                                                                            @endif
                                                                        @else
                                                                            <option value="{{$tto->id}}">
                                                                                        {{$trip->trip_number ? $trip->trip_number: ""}} {{ $trip->trip_ref ? " / ".$trip->trip_ref: "" }} {{ isset($pod) ? "POD#: ".$pod->document_number : "" }} {{$trip->start_date}} {{$tto->currency ? $tto->currency->name : ""}} {{$tto->currency ? $tto->currency->symbol : ""}}{{$tto->allocted_freight ? number_format($tto->allocted_freight,2) : ""}} 
                                                                                    @if ($trip->horse)
                                                                                        {{$trip->horse ? $trip->horse->registration_number : ""}} {{$trip->horse->fleet_number ? "(".$trip->horse->fleet_number.")" : ""}}
                                                                                    @elseif($trip->vehicle)
                                                                                        {{$trip->vehicle ? $trip->vehicle->registration_number : ""}} {{$trip->vehicle->fleet_number ? "(".$trip->vehicle->fleet_number.")" : ""}}
                                                                                    @endif
                                                                                        {{$transport_order->customer ? $transport_order->customer->name : ""}}
                                                                            </option>
                                                                        @endif
                                                                    @endforeach  
                                                            </select>
                                                            <small style="color: green">NB: All invoiced TTOs will appear in orange</small>
                                                        @error('selectedMultiTrip') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label for="subheading">Taxes</label>
                                                        <select wire:model.debounce.300ms="selectedMultiTax"  class="form-control">
                                                                <option value="">Select Tax Category</option>
                                                                @foreach ($tax_accounts as $tax)
                                                                <option value="{{$tax->id}}">{{$tax->abbreviation}}</option> 
                                                                @endforeach
                                                            </select>
                                                            <small><a href="{{ route('accounts.tax') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small><a href="#" wire:click.prevent="refresh('taxes')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                                            <small style="color: green">NB: this tax selection will affect all TTOs selected</small>
                                                        @error('selectedMultiTax') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="row" wire:key="invoice-line-0">
                                                <div class="col-md-5">
                                                    <div class="form-group">
                                                        <label for="subheading">Trip Transport Orders<span class="required" style="color: red">*</span></label>
                                                        <select wire:model.debounce.300ms="selectedTTO.0"  class="form-control" required size="4">
                                                            <option value="">Select TTO</option>
                                                            @foreach ($trip_transport_orders as $tto)
                                                                @php
                                                                    $trip = App\Models\Trip::find($tto->trip_id);
                                                                    $transport_order = App\Models\TransportOrder::find($tto->transport_order_id);
                                                                @endphp
                                                                @if (isset($trip_transport_order_ids))
                                                                    @if (in_array($tto->id,$trip_transport_order_ids))
                                                                        <option value="{{$tto->id}}" style="color: orange"
                                                                            @if(in_array($tto->id, $selectedTTO ?? []) && ($selectedTTO[0] ?? null) != $tto->id) 
                                                                                disabled 
                                                                            @endif
                                                                            >
                                                                            {{$trip->trip_number ? $trip->trip_number: ""}} {{ $trip->trip_ref ? " / ".$trip->trip_ref: "" }} {{ isset($pod) ? "POD#: ".$pod->document_number : "" }} {{$trip->start_date}} {{$tto->currency ? $tto->currency->name : ""}} {{$tto->currency ? $tto->currency->symbol : ""}}{{$tto->allocated_freight ? number_format($tto->allocated_freight,2) : ""}} 
                                                                            @if ($trip->horse)
                                                                                {{$trip->horse ? $trip->horse->registration_number : ""}} {{$trip->horse->fleet_number ? "(".$trip->horse->fleet_number.")" : ""}}
                                                                            @elseif($trip->vehicle)
                                                                                {{$trip->vehicle ? $trip->vehicle->registration_number : ""}} {{$trip->vehicle->fleet_number ? "(".$trip->vehicle->fleet_number.")" : ""}}
                                                                            @endif
                                                                            {{$transport_order->customer ? $transport_order->customer->name : ""}}
                                                                        </option> 
                                                                    @else
                                                                        <option value="{{$tto->id}}"
                                                                            @if(in_array($tto->id, $selectedTTO ?? []) && ($selectedTTO[0] ?? null) != $tto->id) 
                                                                                disabled 
                                                                            @endif
                                                                            >
                                                                            {{$trip->trip_number ? $trip->trip_number: ""}} {{ $trip->trip_ref ? " / ".$trip->trip_ref: "" }} {{ isset($pod) ? "POD#: ".$pod->document_number : "" }} {{$trip->start_date}} {{$tto->currency ? $tto->currency->name : ""}} {{$tto->currency ? $tto->currency->symbol : ""}}{{$tto->allocated_freight ? number_format($tto->allocated_freight,2) : ""}} 
                                                                            @if ($trip->horse)
                                                                                {{$trip->horse ? $trip->horse->registration_number : ""}} {{$trip->horse->fleet_number ? "(".$trip->horse->fleet_number.")" : ""}}
                                                                            @elseif($trip->vehicle)
                                                                                {{$trip->vehicle ? $trip->vehicle->registration_number : ""}} {{$trip->vehicle->fleet_number ? "(".$trip->vehicle->fleet_number.")" : ""}}
                                                                            @endif
                                                                           {{$transport_order->customer ? $transport_order->customer->name : ""}}
                                                                        </option>
                                                                    @endif
                                                                @else
                                                                    <option value="{{$tto->id}}"
                                                                        @if(in_array($tto->id, $selectedTTO ?? []) && ($selectedTTO[0] ?? null) != $tto->id) 
                                                                            disabled 
                                                                        @endif
                                                                        >
                                                                        {{$trip->trip_number ? $trip->trip_number: ""}} {{ $trip->trip_ref ? " / ".$trip->trip_ref: "" }} {{ isset($pod) ? "POD#: ".$pod->document_number : "" }} {{$trip->start_date}} {{$tto->currency ? $tto->currency->name : ""}} {{$tto->currency ? $tto->currency->symbol : ""}}{{$tto->allocated_freight ? number_format($tto->allocated_freight,2) : ""}} 
                                                                        @if ($trip->horse)
                                                                            {{$trip->horse ? $trip->horse->registration_number : ""}} {{$trip->horse->fleet_number ? "(".$trip->horse->fleet_number.")" : ""}}
                                                                        @elseif($trip->vehicle)
                                                                            {{$trip->vehicle ? $trip->vehicle->registration_number : ""}} {{$trip->vehicle->fleet_number ? "(".$trip->vehicle->fleet_number.")" : ""}}
                                                                        @endif
                                                                       {{$transport_order->customer ? $transport_order->customer->name : ""}}
                                                                    </option>
                                                                @endif
                                                            @endforeach  
                                                        </select>
                                                        <small style="color: green">NB: All invoiced TTOs will appear in orange</small>
                                                        @error('selectedTTO.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Description</label>
                                                    <textarea wire:model.debounce.300ms="description.0" class="form-control" cols="30" rows="4" placeholder="Enter Item Description"></textarea>
                                                        @error('description.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label for="date">Qty<span class="required" style="color: red">*</span></label>
                                                        <input type="number"  class="form-control" wire:model.debounce.300ms="qty.0"   required>
                                                        @error('qty.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label for="subheading">Amount<span class="required" style="color: red">*</span></label>
                                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount.0"   required/>
                                                        @error('amount.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label for="subheading">Taxes</label>
                                                        <select wire:model.debounce.300ms="selectedTax.0"  class="form-control">
                                                                <option value="">Select Tax Category</option>
                                                                @foreach ($tax_accounts as $tax)
                                                                <option value="{{$tax->id}}">{{$tax->abbreviation}}</option> 
                                                                @endforeach
                                                            </select>
                                                            <small><a href="{{ route('accounts.tax') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small><a href="#" wire:click.prevent="refresh('taxes')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                                        @error('selectedTax.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                            </div>
                                            @foreach ($inputs as $key => $value)
                                                <div class="row">
                                                    <div class="col-md-12" >
                                                        <input type="checkbox" wire:model.debounce.300ms="is_custom_item.{{ $value }}"   class="line-style" />
                                                        <label for="one" class="radio-label">Add custom item</label>
                                                        @error('is_custom_item.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                        @if(!($is_custom_item[$value] ?? false))
                                                                <label for="subheading">Trip Transport Orders<span class="required" style="color: red">*</span></label>
                                                                <select wire:model.debounce.300ms="selectedTTO.{{$value}}"  class="form-control" required size="4">
                                                                    <option value="">Select TTO</option>
                                                                    @foreach ($trip_transport_orders->where('currency_id', $selectedCurrency) as $tto)
                                                                        @php
                                                                            $trip = App\Models\Trip::find($tto->trip_id);
                                                                            $transport_order = App\Models\TransportOrder::find($tto->transport_order_id);
                                                                        @endphp
                                                                        @if (isset($trip_transport_order_ids))
                                                                            @if (in_array($tto->id,$trip_transport_order_ids))
                                                                            <option value="{{$tto->id}}" style="color: orange"
                                                                                @if(in_array($tto->id, $selectedTTO ?? []) && ($selectedTTO[$value] ?? null) != $tto->id) 
                                                                                disabled 
                                                                                @endif
                                                                            >
                                                                                {{$trip->trip_number ? $trip->trip_number: ""}} {{ $trip->trip_ref ? " / ".$trip->trip_ref: "" }} {{ isset($pod) ? "POD#: ".$pod->document_number : "" }} {{$trip->start_date}} {{$tto->currency ? $tto->currency->name : ""}} {{$tto->currency ? $tto->currency->symbol : ""}}{{$tto->allocated_freight ? number_format($tto->allocated_freight,2) : ""}} 
                                                                                @if ($trip->horse)
                                                                                    {{$trip->horse ? $trip->horse->registration_number : ""}} {{$trip->horse->fleet_number ? "(".$trip->horse->fleet_number.")" : ""}}
                                                                                @elseif($trip->vehicle)
                                                                                    {{$trip->vehicle ? $trip->vehicle->registration_number : ""}} {{$trip->vehicle->fleet_number ? "(".$trip->vehicle->fleet_number.")" : ""}}
                                                                                @endif
                                                                                {{$transport_order->customer ? $transport_order->customer->name : ""}}
                                                                            </option> 
                                                                            @else
                                                                                <option value="{{$tto->id}}"
                                                                                    @if(in_array($tto->id, $selectedTTO ?? []) && ($selectedTTO[$value] ?? null) != $tto->id) 
                                                                                        disabled 
                                                                                    @endif    
                                                                                >
                                                                                {{$trip->trip_number ? $trip->trip_number: ""}} {{ $trip->trip_ref ? " / ".$trip->trip_ref: "" }} {{ isset($pod) ? "POD#: ".$pod->document_number : "" }} {{$trip->start_date}} {{$tto->currency ? $tto->currency->name : ""}} {{$tto->currency ? $tto->currency->symbol : ""}}{{$tto->allocated_freight ? number_format($tto->allocated_freight,2) : ""}} 
                                                                                @if ($trip->horse)
                                                                                    {{$trip->horse ? $trip->horse->registration_number : ""}} {{$trip->horse->fleet_number ? "(".$trip->horse->fleet_number.")" : ""}}
                                                                                @elseif($trip->vehicle)
                                                                                    {{$trip->vehicle ? $trip->vehicle->registration_number : ""}} {{$trip->vehicle->fleet_number ? "(".$trip->vehicle->fleet_number.")" : ""}}
                                                                                @endif
                                                                                {{$transport_order->customer ? $transport_order->customer->name : ""}}   
                                                                                </option>
                                                                            @endif
                                                                        @else
                                                                            <option value="{{$tto->id}}"
                                                                                @if(in_array($tto->id, $selectedTTO ?? []) && ($selectedTTO[$value] ?? null) != $tto->id) 
                                                                                disabled 
                                                                            @endif
                                                                                >
                                                                                {{$trip->trip_number ? $trip->trip_number: ""}} {{ $trip->trip_ref ? " / ".$trip->trip_ref: "" }} {{ isset($pod) ? "POD#: ".$pod->document_number : "" }} {{$trip->start_date}} {{$tto->currency ? $tto->currency->name : ""}} {{$tto->currency ? $tto->currency->symbol : ""}}{{$tto->allocated_freight ? number_format($tto->allocated_freight,2) : ""}} 
                                                                                @if ($trip->horse)
                                                                                    {{$trip->horse ? $trip->horse->registration_number : ""}} {{$trip->horse->fleet_number ? "(".$trip->horse->fleet_number.")" : ""}}
                                                                                @elseif($trip->vehicle)
                                                                                    {{$trip->vehicle ? $trip->vehicle->registration_number : ""}} {{$trip->vehicle->fleet_number ? "(".$trip->vehicle->fleet_number.")" : ""}}
                                                                                @endif
                                                                                {{$transport_order->customer ? $transport_order->customer->name : ""}}    
                                                                            </option>
                                                                        @endif
                                                                    @endforeach  
                                                                </select>
                                                                <small style="color: green">NB: All invoiced TTOs will appear in orange</small>
                                                                @error('selectedTTO.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                            @else
                                                                <label for="country">Items<span class="required" style="color: red">*</span></label>
                                                                <select wire:model.debounce.300ms="selectedProduct.{{ $value }}" class="form-control" required>
                                                                    <option value="">Select Item</option>
                                                                    @foreach ($products as $product)
                                                                    <option value="{{$product->id}}"
                                                                            @if(in_array($product->id, $selectedProduct ?? []) && ($selectedProduct[$value] ?? null) != $product->id) 
                                                                            disabled 
                                                                        @endif
                                                                        >{{$product->brand ? $product->brand->name : ""}} {{$product->name}} {{$product->identification_number ? "ID#:".$product->identification_number : ""}}</option> 
                                                                    @endforeach
                                                                </select>
                                                                <small>  <a href="#" wire:click="showItem({{$value}})"><i class="fa fa-plus-square-o"></i> New Product / Service</a></small><a href="#" wire:click.prevent="refresh('products')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                                                @error('selectedProduct.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                            @endif
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
                                                                    <option value="">Select Tax Category</option>
                                                                    @foreach ($tax_accounts as $tax)
                                                                    <option value="{{$tax->id}}">{{$tax->abbreviation}}</option> 
                                                                    @endforeach
                                                                </select>
                                                                <small><a href="{{ route('accounts.tax') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small><a href="#" wire:click.prevent="refresh('taxes')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                                            @error('selectedTax.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                        </div>
                                                    </div>
                                                    <div class="col-md-1">
                                                        <div class="form-group">
                                                            <label for=""></label>
                                                            <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="remove({{$key}},{{$value}})"> <i class="fa fa-times"></i></button>
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
                                        @endif
                                    @elseif ($source == "Transport Order")
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="">Search Transport Orders</label>
                                                    <input type="text" wire:model.debounce.300ms="searchTransportOrder" class="form-control" placeholder="Search by order#, manifest#, customer, cargo...">
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Primary row (index 0) --}}
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="">Transport Order<span class="required" style="color: red">*</span></label>
                                                    <select wire:model.debounce.300ms="selectedTransportOrder.0" class="form-control" required size="4">
                                                        <option value="">Select Transport Order</option>
                                                        @foreach ($this->transportOrders as $transport_order)
                                                            <option value="{{ $transport_order->id }}"
                                                                @if(in_array($transport_order->id, $selectedTransportOrder ?? []) && ($selectedTransportOrder[0] ?? null) != $transport_order->id) disabled @endif>
                                                                {{ $transport_order->transport_order_number }} {{ $transport_order->customer ? "— ".$transport_order->customer->name : "" }} {{ $transport_order->freight ? "(".number_format($transport_order->freight,2).")" : "" }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('selectedTransportOrder.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="country">Items<span class="required" style="color: red">*</span></label>
                                                    <select wire:model.debounce.300ms="selectedProduct.0" class="form-control" required>
                                                        <option value="">Select Item</option>
                                                        @foreach ($products as $product)
                                                            <option value="{{$product->id}}">{{$product->brand ? $product->brand->name : ""}} {{$product->name}}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('selectedProduct.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="name">Description</label>
                                                    <textarea wire:model.debounce.300ms="description.0" class="form-control" cols="30" rows="4" placeholder="Enter Item Description"></textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label for="date">Qty<span class="required" style="color: red">*</span></label>
                                                    <input type="number" class="form-control" wire:model.debounce.300ms="qty.0" required>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label for="subheading">Amount<span class="required" style="color: red">*</span></label>
                                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount.0" required/>
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Additional rows --}}
                                        @foreach ($inputs as $key => $value)
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="">Transport Order<span class="required" style="color: red">*</span></label>
                                                        <select wire:model.debounce.300ms="selectedTransportOrder.{{$value}}" class="form-control" required size="4">
                                                            <option value="">Select Transport Order</option>
                                                            @foreach ($this->transportOrders as $transport_order)
                                                                <option value="{{ $transport_order->id }}"
                                                                    @if(in_array($transport_order->id, $selectedTransportOrder ?? []) && ($selectedTransportOrder[$value] ?? null) != $transport_order->id) disabled @endif>
                                                                    {{ $transport_order->transport_order_number }} {{ $transport_order->customer ? "— ".$transport_order->customer->name : "" }} {{ $transport_order->freight ? "(".number_format($transport_order->freight,2).")" : "" }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('selectedTransportOrder.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="country">Items<span class="required" style="color: red">*</span></label>
                                                        <select wire:model.debounce.300ms="selectedProduct.{{ $value }}" class="form-control" required>
                                                            <option value="">Select Item</option>
                                                            @foreach ($products as $product)
                                                                <option value="{{$product->id}}">{{$product->brand ? $product->brand->name : ""}} {{$product->name}}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('selectedProduct.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="name">Description</label>
                                                        <textarea wire:model.debounce.300ms="description.{{$value}}" class="form-control" cols="30" rows="4" placeholder="Enter Item Description"></textarea>
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label for="date">Qty<span class="required" style="color: red">*</span></label>
                                                        <input type="number" class="form-control" wire:model.debounce.300ms="qty.{{$value}}" required>
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label for="subheading">Amount<span class="required" style="color: red">*</span></label>
                                                        <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount.{{$value}}" required/>
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label for=""></label>
                                                        <button class="btn btn-danger btn-rounded xs" style="margin-top:23px" wire:click.prevent="remove({{$key}},{{$value}})"> <i class="fa fa-times"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i>Transport Order</button>
                                                </div>
                                            </div>
                                        </div>
                                    @elseif ($source == "Rental")
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="input-group">
                                                    <span class="input-group-addon">
                                            Filter By
                                            </span>
                                            <select wire:model.debounce.300ms="rental_filter" class="form-control" aria-label="..." >
                                                <option value="created_at">Rental Created At</option>
                                                <option value="pickup_at">Rental Pickup At</option>
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
                                                    <label for="">Search rentals</label>
                                                    <input type="text" wire:model.debounce.300ms="searchRental" class="form-control" placeholder="Search rentals using: rental#, rental ref, waybill#, customer, HRN...">
                                                </div>
                                            </div>
                                           
                                        </div>
                                        @php
                                            $invoice_items = App\Models\InvoiceItem::all();
                                            foreach($invoice_items as $invoice_item){
                                                    $rental_ids[] = $invoice_item->rental_id;
                                            }   
                                        @endphp
                                        <div class="row" wire:key="invoice-line-0">
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <label for="subheading">Rentals<span class="required" style="color: red">*</span></label>
                                                    <select wire:model.debounce.300ms="selectedRental.0"  class="form-control" required size="4">
                                                        <option value="">Select Rental</option>
                                                            @foreach ($rentals->where('currency_id', $selectedCurrency) as $rental)
                                                                    @if (isset($rental_ids))
                                                                        @if (in_array($rental->id,$rental_ids))
                                                                            <option value="{{$rental->id}}" style="color: orange"
                                                                            @if(in_array($rental->id, $selectedRental ?? []) && ($selectedRental[0] ?? null) != $rental->id) 
                                                                                disabled 
                                                                            @endif
                                                                            >
                                                                            {{$rental->car_rental_number ? $rental->car_rental_number : ""}} {{ $rental->customer ? $rental->customer->name : "" }}  {{$rental->currency ? $rental->currency->name : ""}} {{$rental->currency ? $rental->currency->symbol : ""}}{{$rental->rate_amount ? number_format($rental->rate_amount,2): ""}} 
                                                                            @if ($rental->vehicle)
                                                                                {{$rental->vehicle->vehicle_make ? $rental->vehicle->vehicle_make->name : ""}} {{$rental->vehicle->vehicle_model ? $rental->vehicle->vehicle_model->name : ""}} {{$rental->vehicle ? "(".$rental->vehicle->registration_number.")" : ""}}    
                                                                            @endif
                                                                            </option> 
                                                                        @else
                                                                            <option value="{{$rental->id}}"
                                                                                @if(in_array($rental->id, $selectedRental ?? []) && ($selectedRental[0] ?? null) != $rental->id) 
                                                                                disabled 
                                                                            @endif
                                                                                >
                                                                                {{$rental->car_rental_number ? $rental->car_rental_number : ""}} {{ $rental->customer ? $rental->customer->name : "" }}  {{$rental->currency ? $rental->currency->name : ""}} {{$rental->currency ? $rental->currency->symbol : ""}}{{$rental->rate_amount ? number_format($rental->rate_amount,2) : ""}} 
                                                                                @if ($rental->vehicle)
                                                                                    {{$rental->vehicle->vehicle_make ? $rental->vehicle->vehicle_make->name : ""}} {{$rental->vehicle->vehicle_model ? $rental->vehicle->vehicle_model->name : ""}} {{$rental->vehicle ? "(".$rental->vehicle->registration_number.")" : ""}}    
                                                                                @endif
                                                                            </option>
                                                                        @endif
                                                                    @else
                                                                            <option value="{{$rental->id}}"
                                                                                @if(in_array($rental->id, $selectedRental ?? []) && ($selectedRental[0] ?? null) != $rental->id) 
                                                                                disabled 
                                                                            @endif
                                                                            >
                                                                            {{$rental->car_rental_number ? $rental->car_rental_number : ""}} {{ $rental->customer ? $rental->customer->name : "" }}  {{$rental->currency ? $rental->currency->name : ""}} {{$rental->currency ? $rental->currency->symbol : ""}}{{$rental->rate_amount ? number_format($rental->rate_amount,2): ""}} 
                                                                            @if ($rental->vehicle)
                                                                                {{$rental->vehicle->vehicle_make ? $rental->vehicle->vehicle_make->name : ""}} {{$rental->vehicle->vehicle_model ? $rental->vehicle->vehicle_model->name : ""}} {{$rental->vehicle ? "(".$rental->vehicle->registration_number.")" : ""}}    
                                                                            @endif
                                                                            </option>
                                                                      
                                                                    @endif
                                                                @endforeach  
                                                        </select>
                                                        <small style="color: green">NB: All invoiced rentals will appear in orange</small>
                                                    @error('selectedRental.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="name">Description</label>
                                                <textarea wire:model.debounce.300ms="description.0" class="form-control" cols="30" rows="4" placeholder="Enter Item Description"></textarea>
                                                    @error('description.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label for="date">Qty<span class="required" style="color: red">*</span></label>
                                                    <input type="number"  class="form-control" wire:model.debounce.300ms="qty.0"   required>
                                                    @error('qty.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label for="subheading">Amount<span class="required" style="color: red">*</span></label>
                                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount.0"   required/>
                                                    @error('amount.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label for="subheading">Taxes</label>
                                                    <select wire:model.debounce.300ms="selectedTax.0"  class="form-control">
                                                            <option value="">Select Tax Category</option>
                                                            @foreach ($tax_accounts as $tax)
                                                            <option value="{{$tax->id}}">{{$tax->abbreviation}}</option> 
                                                            @endforeach
                                                        </select>
                                                        <small><a href="{{ route('accounts.tax') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small><a href="#" wire:click.prevent="refresh('taxes')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                                    @error('selectedTax.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        </div>
                                        @foreach ($inputs as $key => $value)
                                            <div class="row">
                                                <div class="col-md-12" >
                                                    <input type="checkbox" wire:model.debounce.300ms="is_custom_item.{{ $value }}"   class="line-style" />
                                                    <label for="one" class="radio-label">Add custom item</label>
                                                    @error('is_custom_item.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                      @if(!($is_custom_item[$value] ?? false))
                                                            <label for="subheading">Rentals<span class="required" style="color: red">*</span></label>
                                                            <select wire:model.debounce.300ms="selectedRental.{{$value}}"  class="form-control" required size="4">
                                                                <option value="">Select Rental</option>
                                                                @foreach ($rentals->where('currency_id', $selectedCurrency) as $rental)
                                                                    @if (isset($rental_ids))
                                                                        @if (in_array($rental->id,$rental_ids))
                                                                        <option value="{{$rental->id}}" style="color: orange"
                                                                            @if(in_array($rental->id, $selectedRental ?? []) && ($selectedRental[$value] ?? null) != $rental->id) 
                                                                            disabled 
                                                                        @endif
                                                                            >
                                                                            {{$rental->car_rental_number ? $rental->car_rental_number : ""}} {{ $rental->customer ? $rental->customer->name : "" }}  {{$rental->currency ? $rental->currency->name : ""}} {{$rental->currency ? $rental->currency->symbol : ""}}{{$rental->rate_amount ? number_format($rental->rate_amount,2): ""}} 
                                                                            @if ($rental->vehicle)
                                                                                {{$rental->vehicle->vehicle_make ? $rental->vehicle->vehicle_make->name : ""}} {{$rental->vehicle->vehicle_model ? $rental->vehicle->vehicle_model->name : ""}} {{$rental->vehicle ? "(".$rental->vehicle->registration_number.")" : ""}}    
                                                                            @endif 
                                                                        </option> 
                                                                        @else
                                                                            <option value="{{$rental->id}}"
                                                                                @if(in_array($rental->id, $selectedRental ?? []) && ($selectedRental[$value] ?? null) != $rental->id) 
                                                                            disabled 
                                                                        @endif
                                                                                >
                                                                                {{$rental->car_rental_number ? $rental->car_rental_number : ""}} {{ $rental->customer ? $rental->customer->name : "" }}  {{$rental->currency ? $rental->currency->name : ""}} {{$rental->currency ? $rental->currency->symbol : ""}}{{$rental->rate_amount ? number_format($rental->rate_amount,2): ""}} 
                                                                                @if ($rental->vehicle)
                                                                                    {{$rental->vehicle->vehicle_make ? $rental->vehicle->vehicle_make->name : ""}} {{$rental->vehicle->vehicle_model ? $rental->vehicle->vehicle_model->name : ""}} {{$rental->vehicle ? "(".$rental->vehicle->registration_number.")" : ""}}    
                                                                                @endif
                                                                            </option>
                                                                        @endif
                                                                    @else
                                                                        <option value="{{$rental->id}}"
                                                                            @if(in_array($rental->id, $selectedRental ?? []) && ($selectedRental[$value] ?? null) != $rental->id) 
                                                                            disabled 
                                                                        @endif
                                                                            >
                                                                            {{$rental->car_rental_number ? $rental->car_rental_number : ""}} {{ $rental->customer ? $rental->customer->name : "" }}  {{$rental->currency ? $rental->currency->name : ""}} {{$rental->currency ? $rental->currency->symbol : ""}}{{$rental->rate_amount ? number_format($rental->rate_amount,2): ""}} 
                                                                            @if ($rental->vehicle)
                                                                                {{$rental->vehicle->vehicle_make ? $rental->vehicle->vehicle_make->name : ""}} {{$rental->vehicle->vehicle_model ? $rental->vehicle->vehicle_model->name : ""}} {{$rental->vehicle ? "(".$rental->vehicle->registration_number.")" : ""}}    
                                                                            @endif    
                                                                        </option>
                                                                    @endif
                                                                @endforeach  
                                                            </select>
                                                            <small style="color: green">NB: All invoiced rentals will appear in orange</small>
                                                            @error('selectedRental.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                        @else
                                                            <label for="country">Items<span class="required" style="color: red">*</span></label>
                                                            <select wire:model.debounce.300ms="selectedProduct.{{ $value }}" class="form-control" required>
                                                                <option value="">Select Item</option>
                                                                @foreach ($products as $product)
                                                                <option value="{{$product->id}}"
                                                                        @if(in_array($product->id, $selectedProduct ?? []) && ($selectedProduct[$value] ?? null) != $product->id) 
                                                                        disabled 
                                                                    @endif
                                                                    >{{$product->brand ? $product->brand->name : ""}} {{$product->name}} {{$product->identification_number ? "ID#:".$product->identification_number : ""}}</option> 
                                                                @endforeach
                                                            </select>
                                                            <small>  <a href="#" wire:click="showItem({{$value}})"><i class="fa fa-plus-square-o"></i> New Product / Service</a></small><a href="#" wire:click.prevent="refresh('products')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                                            @error('selectedProduct.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                        @endif
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
                                                        <input type="number"  class="form-control" wire:model.debounce.300ms="qty.{{$value}}" disabled required>
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
                                                                <option value="">Select Tax Category</option>
                                                                @foreach ($tax_accounts as $tax)
                                                                <option value="{{$tax->id}}">{{$tax->abbreviation}}</option> 
                                                                @endforeach
                                                            </select>
                                                            <small><a href="{{ route('accounts.tax') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small><a href="#" wire:click.prevent="refresh('taxes')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                                        @error('selectedTax.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label for=""></label>
                                                        <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="remove({{$key}},{{$value}})"> <i class="fa fa-times"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i>Rental</button>
                                                </div>
                                            </div>
                                        </div>
                                    @elseif($source == "Booking")
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="input-group">
                                                    <span class="input-group-addon">
                                            Filter By
                                            </span>
                                            <select wire:model.debounce.300ms="booking_filter" class="form-control" aria-label="..." >
                                                <option value="created_at">Booking Created At</option>
                                                <option value="in_date">Booking Date</option>
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
                                                    <label for="">Search bookings</label>
                                                    <input type="text" wire:model.debounce.300ms="searchBooking" class="form-control" placeholder="Search bookings using: booking#, ticket#,transporter, reg#, fleet#, jobtype, ...">
                                                </div>
                                            </div>
                                        </div>
                                        @php
                                            $invoice_items = App\Models\InvoiceItem::all();
                                            foreach($invoice_items as $invoice_item){
                                                    $booking_ids[] = $invoice_item->booking_id;
                                            }   
                                        @endphp
                                        <div class="row">
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <label for="subheading">Bookings<span class="required" style="color: red">*</span></label>
                                                    <select wire:model.debounce.300ms="selectedBooking.0"  class="form-control" required size="4">
                                                        <option value="">Select Booking</option>
                                                            @foreach ($bookings as $booking)
                                                                    @if (isset($booking_ids))
                                                                        @if (in_array($booking->id,$booking_ids))
                                                                            <option value="{{$booking->id}}" style="color: orange"
                                                                            @if(in_array($booking->id, $selectedBooking ?? []) && ($selectedBooking[0] ?? null) != $booking->id) 
                                                                                disabled 
                                                                            @endif
                                                                            > Booking#: {{$booking->booking_number}} Ticket#: {{ $booking->ticket ? $booking->ticket->ticket_number : "" }} Job: {{ $booking->service_type ? $booking->service_type->name : "" }} Date: {{$booking->in_date}} Reason: {{$booking->description}}</option> 
                                                                        @else
                                                                            <option value="{{$booking->id}}"
                                                                                @if(in_array($booking->id, $selectedBooking ?? []) && ($selectedBooking[0] ?? null) != $booking->id) 
                                                                                disabled 
                                                                            @endif
                                                                                >Booking#: {{$booking->booking_number}} Ticket#: {{ $booking->ticket ? $booking->ticket->ticket_number : "" }} Job: {{ $booking->service_type ? $booking->service_type->name : "" }} Date: {{$booking->in_date}} Reason: {{$booking->description}}</option>
                                                                        @endif
                                                                    @else
                                                                        <option value="{{$booking->id}}"
                                                                            @if(in_array($booking->id, $selectedBooking ?? []) && ($selectedBooking[0] ?? null) != $booking->id) 
                                                                                disabled 
                                                                            @endif
                                                                            >Booking#: {{$booking->booking_number}} Ticket#: {{ $booking->ticket ? $booking->ticket->ticket_number : "" }} Job: {{ $booking->service_type ? $booking->service_type->name : "" }} Date: {{$booking->in_date}} Reason: {{$booking->description}}</option>
                                                                    @endif
                                                                @endforeach  
                                                        </select>
                                                        <small style="color: green">NB: All invoiced bookings will appear in orange</small>
                                                    @error('selectedBooking.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="name">Description</label>
                                                <textarea wire:model.debounce.300ms="description.0" class="form-control" cols="30" rows="4" placeholder="Enter Item Description"></textarea>
                                                    @error('description.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label for="date">Qty<span class="required" style="color: red">*</span></label>
                                                    <input type="number"  class="form-control" wire:model.debounce.300ms="qty.0"   required>
                                                    @error('qty.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label for="subheading">Amount<span class="required" style="color: red">*</span></label>
                                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount.0"   required/>
                                                    @error('amount.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label for="subheading">Taxes</label>
                                                    <select wire:model.debounce.300ms="selectedTax.0"  class="form-control">
                                                            <option value="">Select Tax Category</option>
                                                            @foreach ($tax_accounts as $tax)
                                                            <option value="{{$tax->id}}">{{$tax->abbreviation}}</option> 
                                                            @endforeach
                                                        </select>
                                                        <small><a href="{{ route('accounts.tax') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small><a href="#" wire:click.prevent="refresh('taxes')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                                    @error('selectedTax.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        </div>
                                        @foreach ($inputs as $key => $value)
                                            <div class="row" >
                                                <div class="col-md-12" >
                                                    <input type="checkbox" wire:model.debounce.300ms="is_custom_item.{{ $value }}"   class="line-style" />
                                                    <label for="one" class="radio-label">Add custom item</label>
                                                    @error('is_custom_item.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                      @if(!($is_custom_item[$value] ?? false))
                                                        <label for="subheading">Bookings<span class="required" style="color: red">*</span></label>
                                                        <select wire:model.debounce.300ms="selectedBooking.{{$value}}"  class="form-control" required size="4">
                                                            <option value="">Select Booking</option>
                                                                   @foreach ($bookings as $booking)
                                                                        @if (isset($booking_ids))
                                                                            @if (in_array($booking->id,$booking_ids))
                                                                            <option value="{{$booking->id}}" style="color: orange"
                                                                                @if(in_array($booking->id, $selectedBooking ?? []) && ($selectedBooking[$value] ?? null) != $booking->id) 
                                                                                    disabled 
                                                                                @endif
                                                                                > Booking#: {{$booking->booking_number}} Ticket#: {{ $booking->ticket ? $booking->ticket->ticket_number : "" }} Job: {{ $booking->service_type ? $booking->service_type->name : "" }} Date: {{$booking->in_date}} Reason: {{$booking->description}}</option> 
                                                                            @else
                                                                                <option value="{{$booking->id}}"
                                                                                    @if(in_array($booking->id, $selectedBooking ?? []) && ($selectedBooking[$value] ?? null) != $booking->id) 
                                                                                    disabled 
                                                                                @endif
                                                                                    >Booking#: {{$booking->booking_number}} Ticket#: {{ $booking->ticket ? $booking->ticket->ticket_number : "" }} Job: {{ $booking->service_type ? $booking->service_type->name : "" }} Date: {{$booking->in_date}} Reason: {{$booking->description}}</option>
                                                                            @endif
                                                                        @else
                                                                            <option value="{{$booking->id}}"
                                                                                @if(in_array($booking->id, $selectedBooking ?? []) && ($selectedBooking[$value] ?? null) != $booking->id) 
                                                                                    disabled 
                                                                                @endif
                                                                                >Booking#: {{$booking->booking_number}} Ticket#: {{ $booking->ticket ? $booking->ticket->ticket_number : "" }} Job: {{ $booking->service_type ? $booking->service_type->name : "" }} Date: {{$booking->in_date}} Reason: {{$booking->description}}</option>
                                                                        @endif
                                                                    @endforeach   
                                                            </select>
                                                            <small style="color: green">NB: All invoiced trips will appear in orange</small>
                                                        @error('selectedTrip.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                         @else
                                                            <label for="country">Items<span class="required" style="color: red">*</span></label>
                                                            <select wire:model.debounce.300ms="selectedProduct.{{ $value }}" class="form-control" required>
                                                                <option value="">Select Item</option>
                                                                @foreach ($products as $product)
                                                                <option value="{{$product->id}}"
                                                                        @if(in_array($product->id, $selectedProduct ?? []) && ($selectedProduct[$value] ?? null) != $product->id) 
                                                                        disabled 
                                                                    @endif
                                                                    >{{$product->brand ? $product->brand->name : ""}} {{$product->name}} {{$product->identification_number ? "ID#:".$product->identification_number : ""}}</option> 
                                                                @endforeach
                                                            </select>
                                                            <small>  <a href="#" wire:click="showItem({{$value}})"><i class="fa fa-plus-square-o"></i> New Product / Service</a></small><a href="#" wire:click.prevent="refresh('products')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                                            @error('selectedProduct.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                        @endif
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
                                                                <option value="">Select Tax Category</option>
                                                                @foreach ($tax_accounts as $tax)
                                                                <option value="{{$tax->id}}">{{$tax->abbreviation}}</option> 
                                                                @endforeach
                                                            </select>
                                                            <small><a href="{{ route('accounts.tax') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small><a href="#" wire:click.prevent="refresh('taxes')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                                        @error('selectedTax.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label for=""></label>
                                                        <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="remove({{$key}},{{$value}})"> <i class="fa fa-times"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i>Booking</button>
                                                </div>
                                            </div>
                                        </div>
                                    @elseif($source == "Inventory")
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="form-group">
                                                    <label for="horse">Items in Inventory<span class="required" style="color: red">*</span></label>
                                                <select wire:model.debounce.300ms="selectedInventory.0" class="form-control" required >
                                                    <option value="" selected>Select Item</option>
                                                        @foreach ($inventories as $inventory)
                                                            @if ($inventory->product)
                                                                @php
                                                                    $product = $inventory->product;
                                                                @endphp 
                                                            <option value="{{$inventory->id}}"
                                                                    @if(in_array($inventory->id, $selectedInventory ?? []) && ($selectedInventory[0] ?? null) != $inventory->id) 
                                                                        disabled 
                                                                    @endif
                                                                >{{$product->product_number}} {{$product->brand ? $product->brand->name : ""}} {{$product->name}} {{$product->identification_number}} | {{$inventory->serial_number ? "SN#: ".$inventory->serial_number : ""}}  </option>
                                                            @endif 
                                                        @endforeach
                                                </select>
                                                <small><a href="{{ route('inventories.create') }}" target="_blank"><i class="fa fa-plus-square-o"></i> Add item to inventory</a></small> 
                                                    @error('selectedInventory.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Description</label>
                                                <textarea wire:model.debounce.300ms="description.0" class="form-control" cols="30" rows="2" placeholder="Enter Item Description"></textarea>
                                                    @error('description.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="name">Item Contents</label>
                                                    <input type="number" min="0" class="form-control" wire:model.debounce.300ms="weight.0"  >
                                                    @error('weight.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    <small>Litres, weight, # of pieces or items to invoice eg 10 litres, 1 item</small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="name">Unit of measure</label>
                                                    <select wire:model.debounce.300ms="measurement.0" class="form-control"  >
                                                        <option value="" selected>Select Unit</option>
                                                        @foreach ($measurements as $measurement)
                                                        <option value="{{$measurement->name}}">{{$measurement->name}} </option>
                                                        @endforeach
                                                    </select>
                                                    @error('measurement.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                                    <input type="number" min="0" max="1" class="form-control" wire:model.debounce.300ms="qty.0"  required disabled>
                                                    @error('qty.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label for="name">Rate<span class="required" style="color: red">*</span></label>
                                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount.0"  required >
                                                    @error('amount.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label for="subheading">Taxes</label>
                                                    <select wire:model.debounce.300ms="selectedTax.0"  class="form-control">
                                                            <option value="">Select Tax Category</option>
                                                            @foreach ($tax_accounts as $tax)
                                                            <option value="{{$tax->id}}">{{$tax->abbreviation}}</option> 
                                                            @endforeach
                                                        </select>
                                                    <small><a href="{{ route('accounts.tax') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small><a href="#" wire:click.prevent="refresh('taxes')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                                    @error('selectedTax.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div> 
                                        </div>
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
                                                                    <option value="{{$inventory->id}}"
                                                                            @if(in_array($inventory->id, $selectedInventory ?? []) && ($selectedInventory[$value] ?? null) != $inventory->id) 
                                                                        disabled 
                                                                    @endif
                                                                >{{$product->product_number}} {{$product->brand ? $product->brand->name : ""}} {{$product->name}} {{$product->identification_number}} | {{$inventory->serial_number ? "SN#: ".$inventory->serial_number : ""}}  </option>
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
                                                                <option value="">Select Tax Category</option>
                                                                @foreach ($tax_accounts as $tax)
                                                                <option value="{{$tax->id}}">{{$tax->abbreviation}}</option> 
                                                                @endforeach
                                                            </select>
                                                            <small><a href="{{ route('accounts.tax') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small><a href="#" wire:click.prevent="refresh('taxes')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                                        @error('selectedTax.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>      

                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label for=""></label>
                                                        <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="remove({{$key}},{{$value}})"> <i class="fa fa-times"></i></button>
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
                                    @else
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="country">Items<span class="required" style="color: red">*</span></label>
                                                        <select wire:model.debounce.300ms="selectedProduct.0" class="form-control" required>
                                                        <option value="">Select Item</option>
                                                            @foreach ($products as $product)
                                                            <option value="{{$product->id}}"
                                                                    @if(in_array($product->id, $selectedProduct ?? []) && ($selectedProduct[0] ?? null) != $product->id) 
                                                                        disabled 
                                                                    @endif
                                                                >{{$product->brand ? $product->brand->name : ""}} {{$product->name}} {{$product->identification_number ? "ID#:".$product->identification_number : ""}}</option> 
                                                            @endforeach
                                                        </select>
                                                        <small>  <a href="#" wire:click="showItem({{0}})"><i class="fa fa-plus-square-o"></i> New Product / Service</a></small><a href="#" wire:click.prevent="refresh('products')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                                    @error('selectedProduct.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="name">Description</label>
                                                    <textarea wire:model.debounce.300ms="description.0" class="form-control" cols="30" rows="2" placeholder="Enter Item Description"></textarea>
                                                    @error('description.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                                    <input type="number" class="form-control" wire:model.debounce.300ms="qty.0"  required >
                                                    @error('qty.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label for="name">Rate<span class="required" style="color: red">*</span></label>
                                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount.0"  required >
                                                    @error('amount.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <label for="subheading">Taxes</label>
                                                    <select wire:model.debounce.300ms="selectedTax.0"  class="form-control">
                                                            <option value="">Select Tax Category</option>
                                                            @foreach ($tax_accounts as $tax)
                                                            <option value="{{$tax->id}}">{{$tax->abbreviation}}</option> 
                                                            @endforeach
                                                        </select>
                                                        <small><a href="{{ route('accounts.tax') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small><a href="#" wire:click.prevent="refresh('taxes')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                                    @error('selectedTax.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                </div>
                                            </div>      
                                        </div>
                                        @foreach ($inputs as $key => $value)
                                            <div class="row">  
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label for="country">Items<span class="required" style="color: red">*</span></label>
                                                            <select wire:model.debounce.300ms="selectedProduct.{{ $value }}" class="form-control" required>
                                                                <option value="">Select Item</option>
                                                                @foreach ($products as $product)
                                                                <option value="{{$product->id}}"
                                                                        @if(in_array($product->id, $selectedProduct ?? []) && ($selectedProduct[$value] ?? null) != $product->id) 
                                                                        disabled 
                                                                    @endif
                                                                    >{{$product->brand ? $product->brand->name : ""}} {{$product->name}} {{$product->identification_number ? "ID#:".$product->identification_number : ""}}</option> 
                                                                @endforeach
                                                            </select>
                                                            <small>  <a href="#" wire:click="showItem({{$value}})"><i class="fa fa-plus-square-o"></i> New Product / Service</a></small><a href="#" wire:click.prevent="refresh('products')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
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
                                                                <option value="">Select Tax Category</option>
                                                                @foreach ($tax_accounts as $tax)
                                                                <option value="{{$tax->id}}">{{$tax->abbreviation}}</option> 
                                                                @endforeach
                                                            </select>
                                                            <small><a href="{{ route('accounts.tax') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small><a href="#" wire:click.prevent="refresh('taxes')" style="float: right"><i class="fa fa-refresh" aria-hidden="true"></i></a> 
                                                        @error('selectedTax.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                                    </div>
                                                </div>      
                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label for=""></label>
                                                        <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="remove({{$key}},{{$value}})"> <i class="fa fa-times"></i></button>
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
                                    <div class="row mt-15 mb-35">
                                        @if ($is_discount == False)
                                            <div class="col-md-3 " >
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
                                                    <a href="#" wire:click.prevent="removeDiscount"  ><i class="fa fa-trash color-danger"></i></a>
                                                </div>
                                            </div>  
                                        @endif
                                    </div>
                                    
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
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"  wire:loading.attr="disabled" 
                        wire:loading.class="opacity-50 cursor-not-allowed"><i class="fa fa-save"></i>Save</button>
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
                                <option value="">Select Tax Category</option>
                                @foreach ($tax_accounts as $tax)
                                <option value="{{$tax->id}}">{{$tax->abbreviation}} </option> 
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


   