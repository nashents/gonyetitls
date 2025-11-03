<div>
    {{-- @php
        $departments = Auth::user()->employee->departments;
        foreach($departments as $department){
            $department_names[] = $department->name;
        }
        $roles = Auth::user()->roles;
        foreach($roles as $role){
            $role_names[] = $role->name;
        }
        $wsdepartment = App\Models\Department::where('name','Workshop')->first();
        if (isset($wsdepartment)) {
            $wsdepartment_head = App\Models\DepartmentHead::where('department_id',$wsdepartment->id)->where('employee_id',Auth::user()->employee->id)->first();
        }
        $stdepartment = App\Models\Department::where('name','Workshop')->first();
        if (isset($stdepartment)) {
            $stdepartment_head = App\Models\DepartmentHead::where('department_id',$stdepartment->id)->where('employee_id',Auth::user()->employee->id)->first();
        }
        $fndepartment = App\Models\Department::where('name','Finance')->first();
        if (isset($fndepartment)) {
            $fndepartment_head = App\Models\DepartmentHead::where('department_id',$fndepartment->id)->where('employee_id',Auth::user()->employee->id)->first();
        }
    @endphp
    @if (isset($fndepartment_head) ||  (in_array('Admin', $role_names) && in_array('Finance', $department_names)) || in_array('Super Admin', $role_names))
        <a href="" data-toggle="modal" data-target="#ticket_expenseModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Expense</a>
    @endif
        <br>
        <br>
        <br> --}}
    <table id="expensesTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
        <thead >
         <tr>
            <th class="th-sm">Vendor
            </th>
            <th class="th-sm">Item
            </th>
            <th class="th-sm">Ccy
            </th>
            <th class="th-sm">Qty
            </th>
            <th class="th-sm">Amt
            </th>
            <th class="th-sm">Subtotal
            </th>
            <th class="th-sm">Tax Amt
            </th>
            <th class="th-sm">Total
            </th>
            <th class="th-sm">Actions
            </th>
          </tr>
        </thead>
        @if ($ticket_expenses->count()>0)
        <tbody>
           @foreach ($ticket_expenses as  $ticket_expense)
            <tr>
                <td>
                    {{$ticket_expense->vendor ? $ticket_expense->vendor->name : ""}}
                </td>  
                <td>
                    {{$ticket_expense->product ? $ticket_expense->product->product_number : ""}} {{$ticket_expense->product ? $ticket_expense->product->name : ""}}
                </td>  
                <td>{{$ticket_expense->currency ? $ticket_expense->currency->name : ""}}</td>
                <td>{{$ticket_expense->qty}}</td>
                <td>{{$ticket_expense->currency ? $ticket_expense->currency->symbol : ""}}{{number_format($ticket_expense->amount,2)}}</td>
                <td>{{$ticket_expense->currency ? $ticket_expense->currency->symbol : ""}}{{number_format($ticket_expense->subtotal,2)}}</td> 
                <td>{{$ticket_expense->currency ? $ticket_expense->currency->symbol : ""}}{{number_format($ticket_expense->tax_amount ? $ticket_expense->tax_amount : 0,2)}}</td> 
                <td>{{$ticket_expense->currency ? $ticket_expense->currency->symbol : ""}}{{number_format($ticket_expense->subtotal_incl,2)}}</td> 
                <td class="w-10 line-height-35 table-dropdown">
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-bars"></i>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            @if ($ticket_expense->bill)
                                @if ($ticket_expense->bill->status == "Unpaid")
                                    <li><a href="#"  wire:click="edit({{$ticket_expense->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                    <li><a href="#" data-toggle="modal" data-target="#ticket_expenseDeleteModal{{ $ticket_expense->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                @endif
                            @endif
                            
                           
                           
                        </ul>
                    </div>
                    @include('ticket_expenses.delete')
                   
            </td> 
            </tr>
            @endforeach
        </tbody>
        @else
        <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
     @endif
      </table>

      <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="ticket_expenseModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-90" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Expenses / Services <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="country">Vendor(s)<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="vendor_id" class="form-control" required>
                                   <option value="">Select Vendor</option>
                                    @foreach ($vendors as $vendor)
                                        <option value="{{$vendor->id}}">{{$vendor->name}} {{$vendor->vendor_number}}</option> 
                                    @endforeach
                               </select>
                                @error('vendor_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                <small> <a href="{{ route('vendors.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Vendor</a></small> 
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="country">Currencies<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="currency_id" class="form-control" required>
                                   <option value="">Select Currency</option>
                                 @foreach ($currencies as $currency)
                                 <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option> 
                                 @endforeach
                              
                               </select>
                                @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                <small>  <a href="{{ route('currencies.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Currency</a></small> 
                            </div>
                            @if (!is_null($currency_id))
                            @if ($company)
                                @if ($currency_id != $company->currency_id)
                                <div class="form-group">
                                    <label for="customer">Conversion Rate</label>
                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate {{$selected_currency ? "From ".$selected_currency->name : ""}} {{$company->currency ? "To ".$company->currency->name : ""}}" >
                                    @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                    <small style="color: green">{{$selected_currency ? " 1 ".$selected_currency->name." is how much in" : ""}} {{$company->currency ? $company->currency->name." ?" : ""}}</small>
                                    <small>{{$exchange_amount ? "The fuel converted amount is: ".$exchange_amount : ""}}</small> 
                                </div> 
                                @endif
                            @endif
                        @endif
                            
                        </div>
                     
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="name">Bill Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:change="billDate()" wire:model.debounce.300ms="bill_date" placeholder="Enter Bill Date" required >
                                @error('bill_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="name">Due Date</label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="due_date" placeholder="Enter Due Date"  >
                                @error('due_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="subheading">Notes</label>
                                <textarea class="form-control" wire:model.debounce.300ms="notes" cols="30" rows="3"></textarea>
                                @error('notes') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                            </div>

                    </div>
                   
             
                    <div class="row">
                        <div class="col-md-3">
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
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="subheading">Expense Accounts<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="selectedAccount" class="form-control" required>
                                    <option value="">Select Expense Account</option>
                                @foreach ($accounts as $account)
                                    <option value="{{$account->id}}">{{$account->name}}</option> 
                                @endforeach
                            
                                </select>
                                @error('selectedAccount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="name">Description</label>
                               <textarea wire:model.debounce.300ms="description" class="form-control" cols="30" rows="1" placeholder="Enter Description"></textarea>
                                @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                <input type="number" class="form-control" wire:model.debounce.300ms="qty"  required />
                                @error('qty') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label for="name">Price<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount" required />
                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="col-md-2">
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="ticket_expenseEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog mw-100 w-90" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Expenses / Services <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="country">Vendor(s)<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="vendor_id" class="form-control" required>
                                   <option value="">Select Vendor</option>
                                    @foreach ($vendors as $vendor)
                                        <option value="{{$vendor->id}}">{{$vendor->name}} {{$vendor->vendor_number}}</option> 
                                    @endforeach
                               </select>
                                @error('vendor_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                <small> <a href="{{ route('vendors.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Vendor</a></small> 
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="country">Currencies<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="currency_id" class="form-control" required>
                                   <option value="">Select Currency</option>
                                 @foreach ($currencies as $currency)
                                 <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option> 
                                 @endforeach
                              
                               </select>
                                @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                <small>  <a href="{{ route('currencies.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Currency</a></small> 
                            </div>
               
                            @if (!is_null($currency_id))
                            @if (Auth::user()->employee->company)
                                @if ($currency_id != Auth::user()->employee->company->currency_id)
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
                                            <label for="customer">Bill in {{ Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->name : "" }}<span class="required" style="color: red">*</span></label>
                                            <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_amount" placeholder="Converted Bill" required>
                                            @error('exchange_amount') <span class="text-danger error">{{ $message }}</span>@enderror
                                        </div> 
                                    </div>
                                </div>
                                @endif
                            @endif
                        @endif 
                        </div>
                     
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="name">Bill Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:change="billDate()" wire:model.debounce.300ms="bill_date" placeholder="Enter Bill Date" required >
                                @error('bill_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="name">Due Date</label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="due_date" placeholder="Enter Due Date"  >
                                @error('due_date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="subheading">Notes</label>
                                <textarea class="form-control" wire:model.debounce.300ms="notes" cols="30" rows="3"></textarea>
                                @error('notes') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                            </div>

                    </div>
                   
             
                    <div class="row">
                        <div class="col-md-3">
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
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="subheading">Expense Accounts<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="selectedAccount" class="form-control" required>
                                    <option value="">Select Expense Account</option>
                                @foreach ($accounts as $account)
                                    <option value="{{$account->id}}">{{$account->name}}</option> 
                                @endforeach
                            
                                </select>
                                @error('selectedAccount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="name">Description</label>
                               <textarea wire:model.debounce.300ms="description" class="form-control" cols="30" rows="1" placeholder="Enter Description"></textarea>
                                @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                <input type="number" class="form-control" wire:model.debounce.300ms="qty"  required />
                                @error('qty') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-1">
                            <div class="form-group">
                                <label for="name">Price<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount" required />
                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="col-md-2">
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="product_serviceModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> New Product / Service<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="storeItem()" >
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
                                <input type="checkbox" wire:model.debounce.300ms="sell"   class="line-style" />
                                <label for="one" class="radio-label">Sell this?</label>
                                @error('sell') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-10">
                                <input type="checkbox" wire:model.debounce.300ms="buy"   class="line-style" disabled />
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
                                @foreach ($accounts as $account)
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
