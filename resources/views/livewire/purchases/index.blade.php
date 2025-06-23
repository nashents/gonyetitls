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
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="panel-title">
                                <div class="row">
                                
                                <div class="col-lg-3">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                  Filter By
                                  </span>
                                  <select wire:model.debounce.300ms="purchase_order_filter" class="form-control" aria-label="..." >
                                    <option value="created_at">Purchase Order Created At</option>
                                    <option value="date">Purchase Order Date</option>
                              </select>
                                    </div>
                                    <!-- /input-group -->
                                </div>
                               
                                <div class="col-lg-2" style=" margin-left:-15px;">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                  From
                                  </span>
                                  <input type="date" wire:model.debounce.300ms="from" class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                <div class="col-lg-2" style="margin-left: 30px">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                  To
                                  </span>
                                  <input type="date" wire:model.debounce.300ms="to" class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                          
                              
                               
                                <!-- /input-group -->
                            </div>
                          
                           
                            </div>
                            <div class="panel-title" >
                                <a href="#"  data-toggle="modal" data-target="#purchaseModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Purchase Order</a>
                                <a href="#" wire:click.prevent="exportPurchasesExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                <a href="#" wire:click.prevent="exportPurchasesCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                <a href="#" wire:click.prevent="exportPurchasesPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a> 
                            </div>
                            <br>
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search purchase orders...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th class="th-sm">Purchase#
                                        </th>
                                        <th class="th-sm">CreatedBy
                                        </th>
                                        <th class="th-sm">Date
                                        </th>
                                        <th class="th-sm">Expiry
                                        </th>
                                        <th class="th-sm">Vendor
                                        </th>
                                        <th class="th-sm">Summary
                                        </th>
                                        <th class="th-sm">Ccy
                                        </th>
                                        <th class="th-sm">Total
                                        </th>
                                        <th class="th-sm">Paid
                                        </th>
                                        <th class="th-sm">GReceived
                                        </th>
                                        <th class="th-sm">Sent
                                        </th>
                                        <th class="th-sm">Auth
                                        </th>
                                        <th class="th-sm">Action
                                        </th>
                                      </tr>
                                </thead>
                                @if (isset($purchases))
                                <tbody>
                                    @forelse ($purchases as $purchase)
                                    <tr>
                                        <td>{{$purchase->purchase_number}}</td>
                                        <td>
                                            @if ($purchase->user)
                                                 {{$purchase->user ? $purchase->user->name : ""}} {{$purchase->user ? $purchase->user->surname : ""}}
                                                @if ($purchase->user->employee)
                                                    <br>
                                                    <small><strong>{{$purchase->user->employee->departments ? $purchase->user->employee->departments->first()->name : ""}}</strong></small>
                                                @endif
                                            @endif
                                        </td>
                                        <td>{{$purchase->date}}</td>
                                        <td><span class="badge bg-{{Carbon\Carbon::now() < $purchase->expiry ? 'success' : 'danger' }}">{{Carbon\Carbon::parse($purchase->expiry)->format('d-m-Y')}}</span></td>
                                        <td>{{$purchase->vendor ? $purchase->vendor->name : ""}}</td>
                                        <td>
                                            @foreach ($purchase->purchase_products as $purchase_product )
                                            {{$purchase_product->product ? $purchase_product->product->name : ""}} {{$purchase_product->product->brand ? $purchase_product->product->brand->name : ""}} ({{$purchase_product->qty}}) <br>
                                            @endforeach
                                            @if ($purchase->description)
                                                <br>
                                                <i><strong>Notes: </strong> {{$purchase->description}}</i>
                                            @endif
                                        </td>
                                        <td>{{$purchase->currency ? $purchase->currency->name : ""}}</td>
                                        <td>{{$purchase->currency ? $purchase->currency->symbol : ""}}{{number_format($purchase->total ? $purchase->total : 0,2)}}</td>
                                        <td>
                                            @if ($purchase->bill)
                                                 {{$purchase->bill->currency ? $purchase->bill->currency->symbol : ""}}{{number_format($purchase->bill->payments->sum('amount'),2)}}
                                            @else
                                            {{$purchase->currency ? $purchase->currency->symbol : ""}}0.00
                                            @endif
                                        </td>
                                        <td>

                                            @if ($department == "tyre")
                                                {{$purchase->tyres->count()}}
                                            @elseif($department == "inventory")
                                                {{$purchase->inventories->count()}}
                                            @elseif($department == "asset")
                                                {{$purchase->assets->count()}}
                                            @endif
                                        </td>
                                        <td>
                                           <span class="badge bg-{{$purchase->is_sent == True ? 'success' :  'primary' }}">{{ $purchase->is_sent == True ? "Sent" : "Not Sent" }}</span>
                                        </td>
                                        <td><span class="badge bg-{{($purchase->authorization == 'approved') ? 'success' : (($purchase->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($purchase->authorization == 'approved') ? 'approved' : (($purchase->authorization == 'rejected') ? 'rejected' : 'pending') }}</span>
                                            @php
                                                $user = App\Models\User::find($purchase->authorized_by_id);
                                            @endphp
                                            @if ($user)
                                                <br>
                                               <small><strong style="background-color: orange">AuthBy: {{$user->name}} {{$user->surname}}</strong></small>  
                                            @endif
                                            @if ($purchase->authorization_comments)
                                            <br>
                                            <small><strong style="background-color: orange">Auth Comments: {{$purchase->authorization_comments}}</strong></small>  
                                            @endif 
                                        </td>
                                        <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{route('purchases.show',$purchase->id)}}" ><i class="fa fa-eye color-default"></i> View</a></li>
                                                    @if ($purchase->authorization == "approved")
                                                    <li><a href="{{route('purchases.preview',$purchase->id)}}"  ><i class="fas fa-file-invoice color-primary"></i> Preview</a></li>
                                                    @if ($purchase->is_sent == False)
                                                         <li><a href="" wire:click.prevent="markSent({{$purchase->id}})"  ><i class="fas fa-check color-secondary"></i> Mark as sent</a></li>
                                                    @endif
                                                    @endif
                                                    @if ($purchase->authorization != "approved")
                                                    <li><a href="#"  wire:click="edit({{$purchase->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                    <li><a href="#" data-toggle="modal" data-target="#purchaseDeleteModal{{ $purchase->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                                    @endif
                                                  
                                                  
                                                </ul>
                                            </div>
                                            @include('purchases.delete')
                                    </td>
                                      </tr>
                                  @empty
                                  <tr>
                                    <td colspan="12">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Purchase Orders Found ....
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
                                    @if (isset($purchases))
                                        {{ $purchases->links() }} 
                                    @endif 
                                </ul>
                            </nav>    

                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="purchaseModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Create Purchase Order <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    @if (!is_null($department))
                    @if ($department == "tyre" || $department == "inventory" )
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="Product">Vendors<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="vendor_id" class="form-control" required>
                                        <option value="">Select Selected Vendor</option>
                                        @foreach ($vendors as $vendor)
                                        <option value="{{$vendor->id}}">{{$vendor->name}}</option>
                                    @endforeach
                                    </select>
                                    <small>  <a href="#" data-toggle="modal" data-target="#vendorModal" ><i class="fa fa-plus-square-o"></i> New Vendor</a></small> 
                                    <br>
                                    <small style="color: green">Select the prefered vendor of choice for the purchase order.</small>
                                    @error('vendor_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="name">Date<span class="required" style="color: red">*</span></label>
                                    <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Purchase Order Date" required >
                                    @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Garage Bookings</label>
                                    <select wire:model.debounce.300ms="booking_id" class="form-control">
                                        <option value="">Select Booking</option>
                                        @foreach ($bookings as $booking)
                                        <option value="{{ $booking->id }}">
                                            {{ $booking->booking_number }} 
                                            {{ $booking->service_type ? $booking->service_type->name : "" }}
                                            @if ($booking->horse)
                                            {{ $booking->horse ? $booking->horse->registration_number : "" }} {{ $booking->horse->fleet_number ? "(".$booking->horse->fleet_number.")" : "" }}
                                        @elseif ($booking->vehicle)
                                            {{ $booking->vehicle ? $booking->vehicle->registration_number : "" }} {{ $booking->vehicle->fleet_number ? "(".$booking->vehicle->fleet_number.")" : "" }}
                                        @elseif ($booking->trailer)
                                        {{ $booking->trailer ? $booking->trailer->registration_number : "" }} {{ $booking->trailer->fleet_number ? "(".$booking->trailer->fleet_number.")" : "" }}
                                        @endif
                                        </option>                                      
                                        @endforeach
                                    </select>
                                    @error('booking_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        </div>
                    @else  
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="Product">Vendors<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="vendor_id" class="form-control" required>
                                    <option value="">Select Selected Vendor</option>
                                    @foreach ($vendors as $vendor)
                                    <option value="{{$vendor->id}}">{{$vendor->name}}</option>
                                @endforeach
                                </select>
                                <small>  <a href="#" data-toggle="modal" data-target="#vendorModal" ><i class="fa fa-plus-square-o"></i> New Vendor</a></small> 
                                <br>
                                <small style="color: green">Select the prefered vendor of choice for the purchase order.</small>
                                @error('vendor_id') <span class="text-danger error">{{ $message }}</span>@enderror
                            
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Purchase Order Date" required >
                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    @endif
                    @endif
              
                  
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Currencies<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="selectedCurrency" class="form-control" required>
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
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="Product">Expense Category<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="selectedAccount" class="form-control" required>
                                    <option value="">Select Category</option>
                                    @foreach ($expense_accounts as $account)
                                      <option value="{{$account->id}}">{{$account->name}}</option>
                                    @endforeach
                                </select>
                                <small>  <a href="{{ route('accounts.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Category</a></small> 
                                @error('selectedAccount') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                       </div>
                  
                       <div class="form-group">
                        <label for="name">Additional Notes</label>
                       <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="2" placeholder="Enter purchase order notes"></textarea>
                        @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                      
                    <h5 class="underline mt-n">Select products</h5>
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="title">Products<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="selectedProduct.0" class="form-control" required>
                                    <option value="">Select Product</option>
                                    @foreach ($products as $product)
                                    <option value="{{$product->id}}"> {{$product->name}} {{$product->brand ? $product->brand->name : ""}} {{$product->identification_number}} ({{$product->product_number}})</option>
                                    @endforeach
                                </select>
                                @if ($department == "tyre")
                                    <small><a href="{{ route('tyre_products.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Product</a></small> 
                                @elseif ($department == "inventory")
                                    <small><a href="{{ route('inventory_products.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Product</a></small> 
                                @elseif ($department == "asset")
                                    <small><a href="{{ route('products.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Product</a></small> 
                                @endif
                                @error('selectedProduct.0') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="Product">Qty<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" min="1" class="form-control" wire:model.debounce.300ms="qty.0"  required>
                                @error('qty.0') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="Product">Rate<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" min="0.01" class="form-control" wire:model.debounce.300ms="amount.0"   required>
                                @error('amount.0') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-3">
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
                       
                    </div>

                        @foreach ($inputs as $key => $value)
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Products<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="selectedProduct.{{$value}}" class="form-control" required>
                                        <option value="">Select Product</option>
                                        @foreach ($products as $product)
                                        <option value="{{$product->id}}"> {{$product->name}} {{$product->brand ? $product->brand->name : ""}} {{$product->identification_number}} ({{$product->product_number}})</option>
                                        @endforeach
                                    </select>
                                    @error('selectedProduct.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="Product">Qty<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" min="1" class="form-control" wire:model.debounce.300ms="qty.{{$value}}"  required>
                                    @error('qty.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="Product">Rate<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" min="0.01" class="form-control" wire:model.debounce.300ms="amount.{{$value}}"   required>
                                    @error('amount.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
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
                            <!-- /.col-md-6 -->
                        </div>
                        @endforeach
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Product</button>
                                </div>
                            </div>
                        </div>

                        
                        <h5 class="underline mt-n">Upload Purchase Order Attachments</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Titles</label>
                                    <select wire:model.debounce.300ms="title.0" class="form-control" >
                                        <option value="">Select Title</option>
                                        <option value="Invoice">Invoice</option>
                                        <option value="Quotation 1">Quotation 1</option>
                                        <option value="Quotation 2">Quotation 2</option>
                                        <option value="Quotation 3">Quotation 3</option>
                                        <option value="Receipt">Receipt</option>
                                    </select>
                                    @error('title.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="file">File</label>
                                    <input type="file" class="form-control" wire:model.debounce.300ms="file.0"  placeholder="Upload File " >
                                    @error('file.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="expires_at">Expiry Date</label>
                                    <input type="date" class="form-control" wire:model.debounce.300ms="expires_at.0" placeholder="dd/mm/yy" />
                                    @error('expires_at.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>


                            <!-- /.col-md-6 -->
                        </div>
                        @foreach ($documentInputs as $key => $value)
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Titles</label>
                                    <select wire:model.debounce.300ms="title.{{$value}}" class="form-control">
                                        <option value="">Select Title</option>
                                        <option value="Invoice">Invoice</option>
                                        <option value="Quotation 1">Quotation 1</option>
                                        <option value="Quotation 2">Quotation 2</option>
                                        <option value="Quotation 3">Quotation 3</option>
                                        <option value="Receipt">Receipt</option>
                                    </select>
                                    @error('title.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="file">File</label>
                                    <input type="file" class="form-control" wire:model.debounce.300ms="file.{{$value}}"  placeholder="Upload File ">
                                    @error('file.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="file">Expiry Date</label>
                                    <input type="date" class="form-control" wire:model.debounce.300ms="expires_at.{{$value}}"  placeholder="dd/mm/yy"/>
                                    @error('expires_at.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                        
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label for=""></label>
                                    <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="documentsRemove({{$key}})"> <i class="fa fa-times"></i></button>
                                </div>
                            </div>
                            <!-- /.col-md-6 -->
                        </div>
                        @endforeach
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="documentsAdd({{$m}})"> <i class="fa fa-plus"></i> File</button>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="purchaseEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Purchase Order <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                    <div class="modal-body">
                        @if (!is_null($department))
                        @if ($department == "tyre" || $department == "inventory" )
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group">
                                        <label for="Product">Vendors<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="vendor_id" class="form-control" required>
                                            <option value="">Select Selected Vendor</option>
                                            @foreach ($vendors as $vendor)
                                            <option value="{{$vendor->id}}">{{$vendor->name}}</option>
                                        @endforeach
                                        </select>
                                        <small>  <a href="#" data-toggle="modal" data-target="#vendorModal" ><i class="fa fa-plus-square-o"></i> New Vendor</a></small> 
                                        <br>
                                        <small style="color: green">Select the prefered vendor of choice for the purchase order.</small>
                                        @error('vendor_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                    
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="name">Date<span class="required" style="color: red">*</span></label>
                                        <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Purchase Order Date" required >
                                        @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="title">Garage Bookings</label>
                                        <select wire:model.debounce.300ms="booking_id" class="form-control">
                                            <option value="">Select Booking</option>
                                            @foreach ($bookings as $booking)
                                            <option value="{{ $booking->id }}">
                                                {{ $booking->booking_number }} 
                                                {{ $booking->service_type ? $booking->service_type->name : "" }}
                                                @if ($booking->horse)
                                                {{ $booking->horse ? $booking->horse->registration_number : "" }} {{ $booking->horse->fleet_number ? "(".$booking->horse->fleet_number.")" : "" }}
                                            @elseif ($booking->vehicle)
                                                {{ $booking->vehicle ? $booking->vehicle->registration_number : "" }} {{ $booking->vehicle->fleet_number ? "(".$booking->vehicle->fleet_number.")" : "" }}
                                            @elseif ($booking->trailer)
                                            {{ $booking->trailer ? $booking->trailer->registration_number : "" }} {{ $booking->trailer->fleet_number ? "(".$booking->trailer->fleet_number.")" : "" }}
                                            @endif
                                            </option>                                      
                                            @endforeach
                                        </select>
                                        @error('booking_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>
                        @else  
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="Product">Vendors<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="vendor_id" class="form-control" required>
                                        <option value="">Select Selected Vendor</option>
                                        @foreach ($vendors as $vendor)
                                        <option value="{{$vendor->id}}">{{$vendor->name}}</option>
                                    @endforeach
                                    </select>
                                    <small>  <a href="#" data-toggle="modal" data-target="#vendorModal" ><i class="fa fa-plus-square-o"></i> New Vendor</a></small> 
                                    <br>
                                    <small style="color: green">Select the prefered vendor of choice for the purchase order.</small>
                                    @error('vendor_id') <span class="text-danger error">{{ $message }}</span>@enderror
                                
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="name">Date<span class="required" style="color: red">*</span></label>
                                    <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Purchase Order Date" required >
                                    @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        @endif
                        @endif
                      
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Currencies<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="selectedCurrency" class="form-control" required>
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
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="Product">Expense Category<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="selectedAccount" class="form-control" required>
                                        <option value="">Select Category</option>
                                        @foreach ($expense_accounts as $account)
                                          <option value="{{$account->id}}">{{$account->name}}</option>
                                        @endforeach
                                    </select>
                                    <small>  <a href="{{ route('accounts.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Category</a></small> 
                                    @error('selectedAccount') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                           </div>
                      
                           <div class="form-group">
                            <label for="name">Additional Notes</label>
                           <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="2" placeholder="Enter purchase order notes"></textarea>
                            @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                          
                        
                        <h5 class="underline mt-n">Select products</h5>
                        @foreach ($purchase_order_products as $key => $value)
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="title">Products<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="selectedCurrentProduct.{{$key}}" class="form-control" required>
                                        <option value="">Select Product</option>
                                        @foreach ($products as $product)
                                        <option value="{{$product->id}}"> {{$product->name}} {{$product->brand ? $product->brand->name : ""}} {{$product->identification_number}} ({{$product->product_number}})</option>
                                        @endforeach
                                    </select>
                                    <small>  <a href="{{ route('products.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Product</a></small> 
                                    @error('selectedCurrentProduct.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label for="Product">Qty<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" min="1" class="form-control" wire:model.debounce.300ms="current_qty.{{$key}}"  required>
                                    @error('current_qty.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="Product">Rate<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" min="0.01" class="form-control" wire:model.debounce.300ms="current_amount.{{$key}}"   required>
                                    @error('current_amount.'.$key) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-3">
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
                                    <a href="#" wire:click="removeShow({{ $value->id }})"  ><i class="fa fa-trash color-danger"></i></a>
                                </div>
                            </div>   
                           
                        </div>
                        @endforeach
                  
    
                            @foreach ($inputs as $key => $value)
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="title">Products<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="selectedProduct.{{$value}}" class="form-control" required>
                                            <option value="">Select Product</option>
                                            @foreach ($products as $product)
                                            <option value="{{$product->id}}">{{$product->name}} {{$product->brand ? $product->brand->name : ""}} {{$product->identification_number}} ({{$product->product_number}})</option>
                                            @endforeach
                                        </select>
                                        @error('selectedProduct.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="Product">Qty<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="any" min="1" class="form-control" wire:model.debounce.300ms="qty.{{$value}}"  required>
                                        @error('qty.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="Product">Rate<span class="required" style="color: red">*</span></label>
                                        <input type="number" step="any" min="0.01" class="form-control" wire:model.debounce.300ms="amount.{{$value}}"   required>
                                        @error('amount.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
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
                                <!-- /.col-md-6 -->
                            </div>
                            @endforeach
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Product</button>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal fade" id="removeModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                   <center> <strong>Are you sure you want to delete this Purchase Order Item</strong> </center>
                </div>
                <form wire:submit.prevent="removePurchaseItem()" >
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


    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="vendorModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Vendor <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="storeVendor()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Name<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Name of a business or person." required />
                        @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                  
                   
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" wire:model.debounce.300ms="email" placeholder="Enter Email"  />
                                @error('email') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="phonenumber">Phonenumber</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="phonenumber" placeholder="Enter Phonenumber"  />
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
                                        <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                    @endforeach
                               </select>
                                @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="country">Country</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="country" placeholder="Enter Country"  />
                                @error('country') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
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
                                <input type="text" class="form-control" wire:model.debounce.300ms="suburb" placeholder="Enter Suburb" />
                                @error('suburb') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="street_address">Street Address</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="street_address" placeholder="Enter Street Address" />
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

