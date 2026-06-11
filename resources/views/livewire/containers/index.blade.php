<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div>
                                @include('includes.messages')
                            </div>
                            {{-- <div class="panel-title" style="float: right">
                                <a href="{{route('containers.export.excel')}}"  class="btn btn-default"><i class="fa fa-download"></i>Excel</a>
                                <a href="{{route('containers.export.csv')}}"  class="btn btn-default"><i class="fa fa-download"></i>CSV</a>
                                <a href="{{route('containers.export.pdf')}}"  class="btn btn-default"><i class="fa fa-download"></i>PDF</a>
                            </div> --}}
                            <div class="panel-title">
                                <a href="" data-toggle="modal" data-target="#containerModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Fueling Station</a>
                                <a href="" data-toggle="modal" data-target="#transferModal" class="btn btn-default"><i class="fa fa-exchange"></i>Fuel Transfer</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search fueling stations...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Name
                                    </th>
                                    <th class="th-sm">Purchase Type
                                    </th>
                                    <th class="th-sm">Fuel Type
                                    </th>
                                    <th class="th-sm">Currency
                                    </th>
                                    <th class="th-sm">Acc Bal($)
                                    </th>
                                    <th class="th-sm">Capacity(l)
                                    </th>
                                    <th class="th-sm">Qty Bal(l)
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($containers))
                                <tbody>
                                    @forelse ($containers as $container)
                                  <tr>
                                    <td>{{$container->name}}</td>
                                    <td>{{$container->purchase_type}}</td>
                                    <td>{{$container->fuel_type}}</td>
                                    <td>{{$container->currency ? $container->currency->name : ""}}</td>
                                    <td>
                                       {{$container->currency ? $container->currency->symbol : ""}}{{number_format($container->account_balance ? $container->account_balance : 0,2)}}        
                                    </td>
                                    <td>
                                        {{$container->capacity}}         
                                    </td>
                                    <td>{{$container->balance}}</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('containers.show', $container->id)}}"><i class="fa fa-eye color-default"></i>View</a></li>
                                                <li><a href="#"  wire:click="edit({{$container->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                @if ($container->purchase_type == "Bulk Buy")
                                                    <li><a href="#"  wire:click="showTopUpModal({{$container->id}})" ><i class="fa fa-gas-pump color-success"></i> Fuel Top Up</a></li>
                                                @endif
                                                
                                                <li><a href="#" data-toggle="modal" data-target="#containerDeleteModal{{ $container->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('containers.delete')
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="7">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                           No Fueling Stations Found ....
                                        </div>
                                       
                                    </td>
                                  </tr>
                                  @endforelse
                                </tbody>
                               
                                 @endif
                              </table>
                              <nav class="text-center" style="float: right">
                                <ul class="pagination rounded-corners">
                                    @if (isset($containers))
                                        {{ $containers->links() }} 
                                    @endif 
                                </ul>
                            </nav>   
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="top_upModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog  mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-gas-pump"></i>Topup to {{$selected_container?->name}} fuel station <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="topup()" >
                <div class="modal-body">
                    <div class="mb-10">
                        <input type="checkbox" wire:model.debounce.300ms="attach_po"   class="line-style" />
                        <label for="one" class="radio-label">Attach a purchase order to this top up</label>
                        @error('attach_po') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                    @if (!is_null($attach_po) && $attach_po == True)
                        <div class="form-group">
                            <label for="country">Purchase Orders</label>
                            <input type="text" wire:model.debounce.300ms="searchPurchase" placeholder="Search with order#, vendor, currency..." class="form-control">
                                <select wire:model.debounce.300ms="selectedPurchase" class="form-control" size="8">
                                    <option value="">Select Purchase Order</option>
                                    @foreach ($purchases as $purchase)
                                        <option value="{{ $purchase->id }}">{{ $purchase->purchase_number }} | 
                                            {{ $purchase->date}} |
                                            {{ $purchase->vendor ? $purchase->vendor->name : "" }} |
                                            {{ $purchase->currency ? $purchase->currency->name : "" }} {{ $purchase->currency ? $purchase->currency->symbol : "" }}{{ number_format($purchase->total ? $purchase->total : 0,2)}}
                                        </option>
                                    @endforeach
                                </select>
                            @error('selectedPurchase') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vendor_id">Vendors</label>
                               <select wire:model.debounce.300ms="vendor_id" class="form-control"  >
                                   <option value="">Select Vendor</option>
                                   @foreach ($vendors as $vendor)
                                    <option value="{{$vendor->id}}">{{$vendor->name}}</option>
                                   @endforeach
                               </select>
                                <small><a href="{{ route('vendors.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Vendor</a></small><a href="#" wire:click.prevent="refresh('vendors')" class="float-end"><i class="fa fa-refresh"></i></a>
                                @error('vendor_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-6">
                            <div class="form-group">
                                <label for="name"> Date<span class="required" style="color: red">*</span></label>
                                <input type="date"  class="form-control" wire:model.debounce.300ms="date" placeholder="Enter TopUp date" required >
                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-5">
                            <label for="exampleInputEmail13">Select what to top up<span class="required" style="color: red">*</span></label>
                            <div class="mb-10">
                                <input type="radio" wire:model.debounce.300ms="top_up_to" value="account"  class="line-style"  required/>
                                <label for="one" class="radio-label">Account Bal($)</label>
                                <input type="radio" wire:model.debounce.300ms="top_up_to" value="quantity"  class="line-style"  required/>
                                <label for="one" class="radio-label">Quantity Bal(l)</label>
                            </div>     
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="fuel_type">Fuel Type<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="fuel_type" class="form-control" required disabled>
                                   <option value="">Select Fuel Type</option>
                                    <option value="{{ $fuel_type }}">{{ $fuel_type }}</option>
                                   
                               </select>
                                @error('fuel_type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="currency_id">Currency<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="selectedCurrency" class="form-control" required {{$top_up_to == "account" ? "disabled" : ""}}>
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
                        
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Account Topup Amount</label>
                                <input type="number" step="any" min="0"  class="form-control" wire:model.debounce.300ms="account_amount" {{$top_up_to == "quantity" ? "disabled" : ""}} {{isset($selectedPurchase) ? "disabled" : ""}} placeholder="Enter Account Top Up Amount"  >
                                @error('account_amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @if (isset($capacity) && $capacity > 0)
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="quantity">Top Up Qty</label>
                                    <input type="number" step="any" min="0" max="{{ $capacity }}" class="form-control" wire:model.debounce.300ms="quantity" {{$top_up_to == "account" ? "disabled" : ""}} placeholder="Enter Top Up Quantity"/>
                                    @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    @if ($total_fuel > $capacity)
                                        <small style="color: red" >Quantity + Balance exceeds station capacity</small>
                                    @endif
                                </div>
                            </div>
                        @else 
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="quantity">Top Up Qty</label>
                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="quantity" placeholder="Enter Top Up Quantity" {{$top_up_to == "account" ? "disabled" : ""}}/>
                                    @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        @endif    
                   </div>
                   <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="rate">Rate</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="rate" placeholder="Enter Unit Price" {{$top_up_to == "account" ? "disabled" : ""}}>
                                @error('rate') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="amount">Total</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="amount" {{$top_up_to == "account" ? "disabled" : ""}}>
                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
  
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="transferModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Transfer Fuel Between Stations <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="transferFuel()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="from">From<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="from_station" class="form-control" required>
                                   <option value="">Select Sending Fueling Station</option>
                                   @foreach ($containers as $container)
                                           <option value="{{$container->id}}"
                                             @if ($to_station && $to_station == $container->id)
                                                disabled
                                            @endif
                                            >{{$container->name}} {{$container->balance ? number_format($container->balance,2)."l" : ""}}</option>
                                   @endforeach
                               </select>
                                @error('from_station') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="to">To<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="to_station" class="form-control" required>
                                   <option value="">Select Receiving Fueling Station</option>
                                   @foreach ($containers as $container)
                                           <option value="{{$container->id}}" 
                                            @if ($from_station && $from_station == $container->id)
                                                disabled
                                            @endif
                                            >{{$container->name}} {{$container->balance ? number_format($container->balance,2)."l" : ""}}</option>
                                   @endforeach
                               </select>
                                @error('to_station') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Quantity<span class="required" style="color: red">*</span></label>
                                <input type="text"  class="form-control" wire:model.debounce.300ms="transfer_quantity" placeholder="Enter Transfer Quanity" required />
                                @error('transfer_quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="date">Date<span class="required" style="color: red">*</span></label>
                                <input type="date"  class="form-control" wire:model.debounce.300ms="transfer_date" placeholder="Enter Date"  required/>
                                @error('transfer_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="date">Reason<span class="required" style="color: red">*</span></label>
                        <textarea class="form-control" wire:model.debounce.300ms="reason" placeholder="Write reason for transfer" cols="30" rows="2" required></textarea>
                        @error('reason') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                     <div class="mb-10">
                            <input type="checkbox" wire:model.debounce.300ms="acknowledgment"   class="line-style" required/>
                            <label for="one" class="radio-label">I confirm the quantities entered above are correct before transfer<span class="required" style="color: red">*</span>.</label>
                            @error('acknowledgment') <span class="text-danger error">{{ $message }}</span>@enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="containerModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Fueling Station <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Name<span class="required" style="color: red">*</span></label>
                        <input type="text"  class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Station / Tank Name" required />
                        @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        <small style="color: green">Enter a descriptive name eg: Zuva(Diesel) / Zuva Msasa Diesel</small>
                    </div>
                    <h5 class="underline mt-15">Location & Contact Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="email" placeholder="Enter Station Email"  />
                                @error('email') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phonenumber">Phonenumber</label>
                                <input type="text"  class="form-control" wire:model.debounce.300ms="phonenumber" placeholder="Enter Station Phonenumber"  />
                                @error('phonenumber') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text"  class="form-control" wire:model.debounce.300ms="address" placeholder="Enter Station Address" />
                        @error('address') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fuel_type">Purchase Type<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="purchase_type" class="form-control" required>
                                   <option value="">Select Purchase Type</option>
                                   <option value="Bulk Buy">Bulk Buy</option>
                                    <option value="Once Off Buy">Once Off Buy</option>
                               </select>
                                @error('purchase_type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fuel_type">Fuel Type<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="fuel_type" class="form-control" required>
                                   <option value="">Select Fuel Type</option>
                                    <option value="Petrol">Petrol</option>
                                    <option value="Diesel">Diesel</option>
                               </select>
                                @error('fuel_type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="capacity">Fuel Tank Capacity</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="capacity" placeholder="Enter Tank Capacity" />
                                @error('capacity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="currency_id">Account Currency @if (isset($account_balance))
                                    <span class="required" style="color: red">*</span>
                                @endif</label>
                               <select wire:model.debounce.300ms="container_currency_id" class="form-control" {{isset($account_balance) ? "required" : ""}} >
                                   <option value="">Select Currency</option>
                                   @foreach ($currencies as $currency)
                                   <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                   @endforeach
                               </select>
                                @error('container_currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <h5 class="underline mt-30">Sitting Account ($) Balance / Fuel Quantity(l) Balance Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="capacity">Account Balance($)</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="account_balance" placeholder="Enter Sitting Account Amount" />
                                @error('account_balance') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @if (isset($capacity) && $capacity > 0)
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="quantity">Quantity Balance(l)</label>
                                <input type="number" step="any" min="0" max="{{ $capacity }}" class="form-control" wire:model.debounce.300ms="quantity" placeholder="Enter Sitting Quantity"/>
                                @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                @if ($quantity > $capacity)
                                    <small style="color: red" >Quantity exceeds station capacity</small>
                                @endif
                            </div>
                        </div>
                        @else 
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="quantity">Quantity(l)</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="quantity" placeholder="Enter Initial Fuel Deposit Quantity" />
                                @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @endif
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="containerEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Fueling Station <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Name<span class="required" style="color: red">*</span></label>
                        <input type="text"  class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Station / Tank Name" required />
                        @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        <small style="color: green">Enter a descriptive name eg: Zuva(Diesel) / Zuva Msasa Diesel</small>
                    </div>
                    <h5 class="underline mt-15">Location & Contact Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="email" placeholder="Enter Station Email"  />
                                @error('email') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phonenumber">Phonenumber</label>
                                <input type="text"  class="form-control" wire:model.debounce.300ms="phonenumber" placeholder="Enter Station Phonenumber"  />
                                @error('phonenumber') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text"  class="form-control" wire:model.debounce.300ms="address" placeholder="Enter Station Address" />
                        @error('address') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fuel_type">Purchase Type<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="purchase_type" class="form-control" required>
                                   <option value="">Select Purchase Type</option>
                                   <option value="Bulk Buy">Bulk Buy</option>
                                    <option value="Once Off Buy">Once Off Buy</option>
                               </select>
                                @error('purchase_type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="fuel_type">Fuel Type<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="fuel_type" class="form-control" required>
                                   <option value="">Select Fuel Type</option>
                                    <option value="Petrol">Petrol</option>
                                    <option value="Diesel">Diesel</option>
                               </select>
                                @error('fuel_type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="capacity">Fuel Tank Capacity</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="capacity" placeholder="Enter Tank Capacity" />
                                @error('capacity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="currency_id">Account Currency @if (isset($account_balance))
                                    <span class="required" style="color: red">*</span>
                                @endif</label>
                               <select wire:model.debounce.300ms="container_currency_id" class="form-control" {{isset($account_balance) ? "required" : ""}} >
                                   <option value="">Select Currency</option>
                                   @foreach ($currencies as $currency)
                                   <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                   @endforeach
                               </select>
                                @error('container_currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <h5 class="underline mt-30">Account ($) Balance / Fuel Quantity(l) Balance Details</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="capacity">Account Balance($)</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="account_balance" placeholder="Enter Account Amount" />
                                @error('account_balance') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @if (isset($capacity) && $capacity > 0)
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="quantity">Quantity Balance(l)</label>
                                <input type="number" step="any" min="0" max="{{ $capacity }}" class="form-control" wire:model.debounce.300ms="quantity" placeholder="Enter Station Quantity"/>
                                @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                @if ($quantity > $capacity)
                                    <small style="color: red" >Quantity exceeds station capacity</small>
                                @endif
                            </div>
                        </div>
                        @else 
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="quantity">Quantity Balance(l)</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="quantity" placeholder="Enter Station Quantity" />
                                @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @endif
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

