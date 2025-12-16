<div>
    {{-- <blockquote class="blockquote-reverse mt-20"> --}}
        @if (Auth::user()->employee)
        @if ($trip->authorization == "approved")
        <a href="" data-toggle="modal" data-target="#tripExpenseModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Trip Expense</a>
        @endif
        <br>
        <br>
        @endif
        <x-loading/>
        <table id="tripExpensesTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead >
                <th class="th-sm">AddedBy
                </th>
                <th class="th-sm">Type
                </th>
                <th class="th-sm">Name
                </th>
                <th class="th-sm">MOP
                </th>
                <th class="th-sm">Category
                </th>
                <th class="th-sm">Currency
                </th>
                <th class="th-sm">Amount
                </th>
                <th class="th-sm">Conversion
                </th>
                @if (Auth::user()->category == "employee" || Auth::user()->category == "admin")
                <th class="th-sm">Actions
                </th>
                @endif
              </tr>
            </thead>

            <tbody>
                @forelse ($trip_expenses as $trip_expense)
              <tr>
                <td>
                    {{$trip_expense->user ? $trip_expense->user->name : ""}} {{$trip_expense->user ? $trip_expense->user->surname : ""}}
                    <br>
                    <small><strong>AddedOn: </strong> {{ date('d M, Y', strtotime($trip_expense->created_at)) }}</small>
                </td>
                <td>
                    @if ($trip_expense->expense)
                        TripExpense
                    @elseif($trip_expense->allowance)
                        Driver Allowance
                    @endif
                </td>
                <td>
                    @if ($trip_expense->expense)
                    {{$trip_expense->expense ? $trip_expense->expense->name : ""}}
                    @elseif($trip_expense->allowance)
                    {{$trip_expense->allowance ? $trip_expense->allowance->name : ""}}
                    @endif

                </td>
                <td>{{$trip_expense->payment_method ? $trip_expense->payment_method->name : ""}}</td>
                <td>{{$trip_expense->category}}</td>
                <td>{{ $trip_expense->currency ? $trip_expense->currency->name : ""}}</td>
                <td>
                    @if ($trip_expense->amount)
                    {{ $trip_expense->currency ? $trip_expense->currency->symbol : "" }}{{number_format($trip_expense->amount,2)}}
                    @endif
                </td>
                <td>
                    @if (isset($trip_expense->exchange_rate))
                        Currency conversion: {{ Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->name : "" }} {{ Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->symbol : "" }}{{ number_format($trip_expense->exchange_amount,2)}} at {{ $trip_expense->exchange_rate}} 
                    @endif
                </td>
                @if (Auth::user()->category == "employee" || Auth::user()->category == "admin")
                <td class="w-10 line-height-35 table-dropdown">
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-bars"></i>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            @if ($trip_expense->user_id == Auth::user()->id)
                                @if (!$trip_expense->fuel)
                                    <li><a href="#" wire:click="edit({{$trip_expense->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                @endif
                                    <li><a href="#" wire:click="showDelete({{$trip_expense->id}})"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                            @endif
                            
                        </ul>
                    </div>
                    @include('trips.expenses.delete')

            </td>
            @endif
            </tr>
            @empty
            <tr>
                <td colspan="9">
                    <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                        No Trip Expenses Captured....
                    </div>
                   
                </td>
              </tr>  
            @endforelse
      
            </tbody>
            <br>
            <thead >
                <th class="th-sm">Currency
                </th>
                <th class="th-sm">Total
                </th>
              </tr>
            </thead>
            <tbody>
                @foreach ($currencies as $currency)
                @php
                    $total = App\Models\TripExpense::where('currency_id',$currency->id)
                                                    ->where('trip_id',$trip->id)->sum('amount');
                @endphp
                @if ($total > 0)
                <tr>
                    <td>{{ $currency->name }}</td>
                    <td>{{ $currency->symbol }}{{ $total }}</td>
                    </tr>
                @endif
             
              @endforeach
      
            </tbody>

          </table>
    {{-- </blockquote> --}}
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="tripExpenseModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog  mw-100 w-50" role="expense">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Trip Expense(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="form-group" >
                        <label for="name">Type</label>
                        <label class="radio-inline">
                            <input type="radio" wire:model.debounce.300ms="trip_expense_type.0" value="expense" name="optradio_0" >Trip Expense
                          </label>
                          <label class="radio-inline">
                            <input type="radio" wire:model.debounce.300ms="trip_expense_type.0" value="allowance" name="optradio_0">Driver Allowance
                          </label>
                    </div>
                    <div class="row">
                        
                        @if (isset($trip_expense_type[0]) && $trip_expense_type[0] === 'expense')
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Expense(s)<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="selectedExpense.0" class="form-control" required>
                                   <option value="">Select Expense</option>
                                   @foreach ($expenses as $expense)
                                       <option value="{{$expense->id}}">{{$expense->name}}</option>
                                   @endforeach
                               </select>
                                @error('selectedExpense.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                <small>  <a href="{{ route('expenses.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Expense</a></small> 
                                  <a href="#" wire:click.prevent="refresh('expenses')" class="float-end">
                                        <i class="fa fa-refresh" aria-hidden="true"></i>
                                    </a>
                            </div>
                        </div>
                        @elseif (isset($trip_expense_type[0]) && $trip_expense_type[0] === 'allowance')
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Allowance(s)<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="selectedAllowance.0" class="form-control" required>
                                   <option value="">Select Allowance</option>
                                   @foreach ($allowances as $allowance)
                                       <option value="{{$allowance->id}}">{{$allowance->name}}</option>
                                   @endforeach
                               </select>
                                @error('selectedAllowance.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                <small>  <a href="{{ route('allowances.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Allowance</a></small>
                                <a href="#" wire:click.prevent="refresh('allowances')" class="float-end">
                                        <i class="fa fa-refresh" aria-hidden="true"></i>
                                    </a> 
                            </div>
                        </div>
                        @endif
                        <div class="col-md-6">
                            <div class="form-group">    
                                <label for="title">Categories<span class="required" style="color: red">*</span></label>                              
                                <select class="form-control" wire:model.debounce.300ms="category.0"  required>
                                    <option value="">Select Category</option>
                                <option value="Customer">Customer</option>
                                <option value="Self">Self</option>
                                <option value="Transporter">Transporter</option>
                                </select>
                                @error('category.0') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="payment_method_id">Payment Methods</label>
                                <select wire:model.debounce.300ms="payment_method_id.0" class="form-control" >
                                    <option value="">Select Payment Method</option>
                                    @foreach ($payment_methods as $payment_method)
                                    <option value="{{ $payment_method->id }}">{{ $payment_method->name }}</option>
                                    @endforeach
                                </select>
                                @error('payment_method_id.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="selectedCurrency">Currencies<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="selectedCurrency.0" class="form-control" required>
                                    <option value="">Select Currency</option>
                                    @foreach ($currencies as $currency)
                                    <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                    @endforeach
                                </select>
                                @error('selectedCurrency.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                            @if (isset($selectedCurrency[0]))
                                @if (Auth::user()->employee->company)
                                    @if ($selectedCurrency[0] != Auth::user()->employee->company->currency_id)
                                    <div class="form-group">
                                        <label for="customer">Conversion Rate</label>
                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate.0"  placeholder="Exchange Rate {{isset($selected_currency[0]) ? "From ".$selected_currency[0]->name : ""}} {{Auth::user()->employee->company->currency ? "To ".Auth::user()->employee->company->currency->name : ""}}" >
                                        @error('exchange_rate.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                        <small style="color: green">{{isset($selected_currency[0]) ? " 1 ".$selected_currency[0]->name." is how much in" : ""}} {{Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->name." ?" : ""}}</small>
                                        <small>{{isset($exchange_amount[0]) ? "The converted amount is: ".$exchange_amount[0] : ""}}</small>
                                    </div> 
                                    @endif
                                @endif
                            @endif
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="amount">Amount<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" class="form-control"  wire:model.debounce.300ms="amount.0" placeholder="Enter Amount" required/>
                                @error('amount.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                @foreach ($inputs as $key => $value)
                    <div class="form-group" >
                        <label for="name">Type</label>
                        <label class="radio-inline">
                            <input type="radio" wire:model.debounce.300ms="trip_expense_type.{{$value}}" value="expense" name="optradio_{{ $value }}" >Trip Expense
                        </label>
                        <label class="radio-inline">
                            <input type="radio" wire:model.debounce.300ms="trip_expense_type.{{$value}}" value="allowance" name="optradio_{{ $value }}">Driver Allowance
                        </label>
                    </div>
                    <div class="row">
                        @if (isset($trip_expense_type[$value]) && $trip_expense_type[$value] === 'expense')
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Expense(s)<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="selectedExpense.{{$value}}" class="form-control" required>
                                        <option value="">Select Expense</option>
                                        @foreach ($expenses as $expense)
                                            <option value="{{$expense->id}}">{{$expense->name}}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedExpense.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    <small>  <a href="{{ route('expenses.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Expense</a></small> <a href="#" wire:click.prevent="refresh('expenses')" class="float-end"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                </div>
                            </div>
                        @elseif (isset($trip_expense_type[$value]) && $trip_expense_type[$value] === 'allowance')
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Allowance(s)<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="selectedAllowance.{{$value}}" class="form-control" required>
                                        <option value="">Select Allowance</option>
                                        @foreach ($allowances as $allowance)
                                            <option value="{{$allowance->id}}">{{$allowance->name}}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedAllowance.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    <small>  <a href="{{ route('allowances.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Allowance</a></small> <a href="#" wire:click.prevent="refresh('allowances')" class="float-end"><i class="fa fa-refresh" aria-hidden="true"></i></a>
                                </div>
                            </div>
                        @endif
                        <div class="col-md-6">
                            <div class="form-group">    
                                <label for="title">Categories<span class="required" style="color: red">*</span></label>                              
                                <select class="form-control" wire:model.debounce.300ms="category.{{$value}}"  required>
                                    <option value="">Select Category</option>
                                    <option value="Customer">Customer</option>
                                    <option value="Self">Self</option>
                                    <option value="Transporter">Transporter</option>
                                </select>
                                @error('category.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="payment_method_id">Payment Methods</label>
                           <select wire:model.debounce.300ms="payment_method_id.{{$value}}" class="form-control">
                               <option value="">Select Payment Method</option>
                               @foreach ($payment_methods as $payment_method)
                               <option value="{{ $payment_method->id }}">{{ $payment_method->name }}</option>
                               @endforeach
                           </select>
                            @error('payment_method_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="selectedCurrency">Currencies<span class="required" style="color: red">*</span></label>
                       <select wire:model.debounce.300ms="selectedCurrency.{{$value}}" class="form-control" required>
                           <option value="">Select Currency</option>
                           @foreach ($currencies as $currency)
                           <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                           @endforeach
                       </select>
                        @error('selectedCurrency.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                    @if (isset($selectedCurrency[$value]))
                        @if (Auth::user()->employee->company)
                            @if ($selectedCurrency[$value] != Auth::user()->employee->company->currency_id)
                            <div class="form-group">
                                <label for="customer">Conversion Rate</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate.{{$value}}"  placeholder="Exchange Rate {{isset($selected_currency[$value]) ? "From ".$selected_currency[$value]->name : ""}} {{Auth::user()->employee->company->currency ? "To ".Auth::user()->employee->company->currency->name : ""}}" >
                                @error('exchange_rate.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                <small style="color: green">{{isset($selected_currency[$value]) ? " 1 ".$selected_currency[$value]->name." is how much in" : ""}} {{Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->name." ?" : ""}}</small>
                                <small>{{isset($exchange_amount[$value]) ? "The converted amount is: ".$exchange_amount[$value] : ""}}</small>
                            </div> 
                            @endif
                        @endif
                    @endif
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="amount">Amount<span class="required" style="color: red">*</span></label>
                        <input type="number" step="any" class="form-control"  wire:model.debounce.300ms="amount.{{$value}}" placeholder="Enter Amount" required/>
                        @error('amount.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="form-group">
                        <button class="btn btn-danger btn-rounded btn-sm" style="margin-top:23px"  wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                    </div>
                </div>
                    </div>
                @endforeach

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <button class="btn btn-success btn-rounded btn-sm" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Expense</button>
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="tripExpenseEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog  mw-100 w-50" role="expense">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Trip Expense(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="form-group" >
                        <label for="name">Type</label>
                        <label class="radio-inline">
                            <input type="radio" wire:model.debounce.300ms="trip_expense_type" value="expense" name="optradio" >Trip Expense
                          </label>
                          <label class="radio-inline">
                            <input type="radio" wire:model.debounce.300ms="trip_expense_type" value="allowance" name="optradio">Driver Allowance
                          </label>
                    </div>
                    <div class="row">
                        
                        @if (isset($trip_expense_type) && $trip_expense_type == "expense")
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Expense(s)<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="selectedExpense" class="form-control" required>
                                   <option value="">Select Expense</option>
                                   @foreach ($expenses as $expense)
                                       <option value="{{$expense->id}}">{{$expense->name}}</option>
                                   @endforeach
                               </select>
                                @error('selectedExpense') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                <small>  <a href="{{ route('expenses.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Expense</a></small> 
                                <a href="#" wire:click.prevent="refresh('expenses')" class="float-end">
                                        <i class="fa fa-refresh" aria-hidden="true"></i>
                                    </a>
                            </div>
                        </div>
                        @elseif (isset($trip_expense_type) && $trip_expense_type == "allowance")   
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Allowance(s)<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="selectedAllowance" class="form-control" required>
                                   <option value="">Select Allowance</option>
                                   @foreach ($allowances as $allowance)
                                       <option value="{{$allowance->id}}">{{$allowance->name}}</option>
                                   @endforeach
                               </select>
                                @error('selectedAllowance') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                <small>  <a href="{{ route('allowances.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Allowance</a></small> 
                                <a href="#" wire:click.prevent="refresh('allowances')" class="float-end">
                                        <i class="fa fa-refresh" aria-hidden="true"></i>
                                    </a>
                            </div>
                        </div>
                        @endif
                   
                    <div class="col-md-6">
                        <div class="form-group">    
                            <label for="title">Categories<span class="required" style="color: red">*</span></label>                              
                            <select class="form-control" wire:model.debounce.300ms="category"  required>
                                <option value="">Select Category</option>
                               {{-- <option value="Customer">Customer</option> --}}
                               <option value="Self">Self</option>
                               {{-- <option value="Transporter">Transporter</option> --}}
                            </select>
                            @error('category') <span class="text-danger error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                    </div>
                    <div class="row">
                          <div class="col-md-4">
                        <div class="form-group">
                            <label for="payment_method_id">Payment Methods</label>
                           <select wire:model.debounce.300ms="payment_method_id" class="form-control" >
                               <option value="">Select Payment Method</option>
                               @foreach ($payment_methods as $payment_method)
                               <option value="{{ $payment_method->id }}">{{ $payment_method->name }}</option>
                               @endforeach
                           </select>
                            @error('payment_method_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="selectedCurrency">Currencies<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="selectedCurrency" class="form-control" required>
                                   <option value="">Select Currency</option>
                                   @foreach ($currencies as $currency)
                                   <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                   @endforeach
                               </select>
                                @error('selectedCurrency') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                            @if (isset($edit))
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
                            @endif
                           
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="amount">Amount<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" class="form-control"  wire:model.debounce.300ms="amount" placeholder="Enter Amount" required/>
                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

    <div data-backdrop="static" data-keyboard="false" class="modal fade" id="expenseDeleteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="expense">
            <div class="modal-content bg-danger">
                <div class="modal-body">
                   <center> <strong>Are you sure you want to delete this Trip Expense?</strong> </center>
                </div>
                <form  method="POST" >
                    <input type="hidden" name="_method" value="DELETE">
                <div class="modal-footer no-border">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn bg-white btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button wire:click.prevent="deleteExpense()" class="btn bg-black btn-wide btn-rounded" ><i class="fa fa-trash"></i>Delete</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>

</div>




@section('extra-js')
{{-- <script>
    $(document).ready( function () {
        $('#tripExpensesTable').DataTable();
    } );
    </script> --}}
@endsection