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

                            <div class="panel-title">
                                <a href="" data-toggle="modal" data-target="#accountModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Account</a>
                            </div>
                        </div>
                        <div class="panel-body p-20" style="overflow-x:auto; width:100%; height:100%;">
                            {{-- <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search accounts...">
                                </div>
                            </div>
                            <br>
                            <br>
                            <br> --}}
                            <ul class="nav nav-tabs nav-justified" role="tablist">
                                @php
                                    $account_type_group = App\Models\AccountTypeGroup::orderBy('name','asc')->first();
                                    $account_type_groups = App\Models\AccountTypeGroup::where('id','!=', $account_type_group->id)->orderBy('name','asc')->get();
                                @endphp
                                <li role="presentation" class="{{$active_group == $account_type_group->id ? "active" : "" }}"><a href="#account{{$account_type_group->id}}" aria-controls="account{{$account_type_group->id}}" role="tab" data-toggle="tab"> <strong>{{$account_type_group->name}} ({{$account_type_group->accounts->count()}})</strong></a></li>
                                @foreach ($account_type_groups as $group)
                                <li role="presentation" class="{{$active_group == $group->id ? "active" : "" }}"><a href="#account{{$group->id}}" wire:click="setActive({{$group->id}})" aria-controls="account{{$group->id}}" role="tab" data-toggle="tab"><strong>{{$group->name}} ({{$group->accounts->count()}})</strong></a></li>
                                @endforeach
                                
                            </ul>
                            <div class="tab-content bg-white p-15">
                                <div role="tabpanel" class="tab-pane {{$active_group == $account_type_group->id ? "active" : "" }}" id="account{{$account_type_group->id}}">
                                    @foreach ($account_type_group->account_types as $account_type)
                                        <table class="table table-striped">
                                            <h6 style="background-color: lightgray;" ><strong style="margin-left: 5px;">{{$account_type->name}}</strong></h6>
                                            @if ($account_type->accounts->count()>0)
                                            <thead>
                                                <tr>
                                                    <th class="w-10 text-center">Ref</th>
                                                    <th class="w-10 text-center">GL Code</th>
                                                    <th class="w-20">Account Name</th>
                                                    <th class="w-20">Description</th>
                                                    <th class="w-10 text-center">Actions</th>
                                                </tr>
                                            </thead>
                                            @foreach ($account_type->accounts as $account)
                                                <tr>
                                                    <th class="w-10 text-center line-height-35">{{$account->account_reference}}</th>
                                                    <td class="w-10 text-center line-height-35">
                                                        @if ($account->code)
                                                            <span class="label label-default">{{$account->code}}</span>
                                                        @else
                                                            <a href="#" wire:click.prevent="edit({{$account->id}})"><i class="fa fa-plus-square-o"></i> Add code</a>
                                                        @endif
                                                    </td>
                                                    <td class="w-20 line-height-35">{{$account->name}}
                                                        @if ($account->balance)
                                                        {{$account->currency ? $account->currency->name : ""}} {{$account->currency ? $account->currency->symbol : ""}}{{number_format($account->balance,2)}}
                                                        @endif
                                                        @php
                                                            $transaction = App\Models\Payment::where('account_id',$account->id)->orderBy('date','desc')->first();
                                                        @endphp
                                                        @if (isset($transaction))
                                                        <br>
                                                        <small>Last transaction {{Carbon\Carbon::parse($transaction->date)->format('d F Y')}}</small>
                                                        @endif
                                                    </td>
                                                    <td class="w-20 line-height-35">{{$account->description}}</td>
                                                    <td class="w-10 line-height-35 table-dropdown">
                                                        <div class="dropdown">
                                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <i class="fa fa-bars"></i>
                                                                <span class="caret"></span>
                                                            </button>
                                                            <ul class="dropdown-menu">
                                                                <li><a href="{{ route('accounts.show',$account->id) }}"  ><i class="fas fa-eye color-default" ></i> View</a></li>
                                                                <li><a href="#"  wire:click="showTransaction({{$account->id}})" ><i class="fas fa-credit-card color-primary" ></i> Transact</a></li>
                                                                @if (!$account->user_id == Null)
                                                                     <li><a href="#"  wire:click="edit({{$account->id}})" ><i class="fas fa-edit color-success" ></i> Edit</a></li>
                                                                     {{-- <li><a href="#" data-toggle="modal" data-target="#accountDeleteModal{{ $account->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li> --}}
                                                                @endif
                                                              
                                                              
                                                            </ul>
                                                        </div>
                                                        @include('accounts.delete')
                                                </td>
                                                </tr> 
                                            @endforeach
                                            <tr>
                                                <th></th>
                                                <td></td>
                                                <td class="w-20 line-height-35" ><a href="#" wire:click.prevent="showAccount({{$account_type->id}},{{$account_type_group->id}})"><i class="fa fa-plus-square-o"></i> Add a new account</a></td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                            @else
                                            <tr>
                                                <th></th>
                                                <td></td>
                                                <td class="w-50 line-height-35" ><small>You haven't added any {{$account_type->name}} accounts yet.</small></td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                            <tr>
                                                <th></th>
                                                <td></td>
                                                <td class="w-20 line-height-35" ><a href="#" wire:click.prevent="showAccount({{$account_type->id}},{{$account_type_group->id}})"><i class="fa fa-plus-square-o"></i> Add a new account</a></td>
                                                <td></td>
                                                <td></td>
                                            </tr>

                                            @endif
                                           
                                          
                                        </table>
                                        <hr>
                                    @endforeach
                                </div>
                                @foreach ($account_type_groups as $group)
                                    <div role="tabpanel" class="tab-pane {{$active_group == $group->id ? "active" : "" }}" id="account{{$group->id}}">
                                        @foreach ($group->account_types as $account_type)
                                            <table class="table table-striped">
                                                <h6 style="background-color: lightgray;" ><strong style="margin-left: 5px;">{{$account_type->name}}</strong></h6>
                                                <thead>
                                                    <tr>
                                                        <th class="w-10 text-center">Ref</th>
                                                        <th class="w-10 text-center">GL Code</th>
                                                        <th class="w-20">Account Name</th>
                                                        <th class="w-20">Description</th>
                                                        <th class="w-10 text-center">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="text-center line-height-35 ">
                                                    @if ($account_type->accounts->count()>0)
                                                    @foreach ($account_type->accounts as $account)
                                                        <tr>
                                                            <th class="w-10 text-center line-height-35">{{$account->account_reference}}</th>
                                                            <td class="w-10 text-center line-height-35">
                                                                @if ($account->code)
                                                                    <span class="label label-default">{{$account->code}}</span>
                                                                @else
                                                                    <a href="#" wire:click.prevent="edit({{$account->id}})"><i class="fa fa-plus-square-o"></i> Add code</a>
                                                                @endif
                                                            </td>
                                                            <td class="w-20 line-height-35">{{$account->name}}</td>
                                                            <td class="w-20 line-height-35">{{$account->description}}</td>
                                                            <td class="w-10 line-height-35 table-dropdown">
                                                                <div class="dropdown">
                                                                    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                        <i class="fa fa-bars"></i>
                                                                        <span class="caret"></span>
                                                                    </button>
                                                                    <ul class="dropdown-menu">
                                                                        <li><a href="{{ route('accounts.show',$account->id) }}"  ><i class="fas fa-eye color-default" ></i> View</a></li>
                                                                        <li><a href="#"  wire:click="showTransaction({{$account->id}})" ><i class="fas fa-credit-card color-primary" ></i> Transact</a></li>
                                                                        @if (!$account->user_id == Null)
                                                                        <li><a href="#"  wire:click="edit({{$account->id}})" ><i class="fas fa-edit color-success" ></i> Edit</a></li>
                                                                        <li><a href="#" data-toggle="modal" data-target="#accountDeleteModal{{ $account->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                                                        @endif
                                                                       
                                                                    </ul>
                                                                </div>
                                                                @include('accounts.delete')
                                                        </td>
                                                        </tr> 
                                                    @endforeach
                                                    <tr>
                                                        <th></th>
                                                        <td></td>
                                                        <td class="w-20 line-height-35" ><a href="#" wire:click.prevent="showAccount({{$account_type->id}},{{$group->id}})"><i class="fa fa-plus-square-o"></i> Add a new account</a></td>
                                                        <td></td>
                                                        <td></td>
                                                    </tr>
                                                    @else
                                                    <tr>
                                                        <th></th>
                                                        <td></td>
                                                        <td class="w-50 line-height-35" ><small>You haven't added any {{$account_type->name}} accounts yet.</small></td>
                                                        <td></td>
                                                        <td></td>
                                                    </tr>
                                                    <tr>
                                                        <th></th>
                                                        <td></td>
                                                        <td class="w-20 line-height-35" ><a href="#" wire:click.prevent="showAccount({{$account_type->id}},{{$group->id}})"><i class="fa fa-plus-square-o"></i> Add a new account</a></td>
                                                        <td></td>
                                                        <td></td>
                                                    </tr>
                                                    @endif
                                                   
                                                    
                                                </tbody>
                                            </table>
                                            <hr>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="transactionModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Record a transaction <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="storeTransaction()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Date" required />
                                @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Description</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="description">
                                @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Accounts<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="account_id" class="form-control" required>
                                        <option value="">Select Account</option>
                                        @foreach ($transaction_account_types as $account_type)
                                            <optgroup label="{{ $account_type->name }}">
                                                @foreach ($account_type->accounts as $account)
                                                    <option value="{{ $account->id }}">{{ $account->name }} {{ $account->currency ? $account->currency->name : "" }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                    </select>
                                @error('account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                            @if (!is_null($selectedCurrency))
                            @if (Auth::user()->employee->company)
                                @if ($selectedCurrency != Auth::user()->employee->company->currency_id)
                                <div class="form-group">
                                    <label for="customer">Conversion Rate<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="exchange_rate"  placeholder="Exchange Rate" required>
                                    @error('exchange_rate') <span class="text-danger error">{{ $message }}</span>@enderror
                                    <small>{{$exchange_amount ? "The converted amount is: ".$exchange_amount : ""}}</small>
                                </div> 
                                @endif
                            @endif
                        @endif 
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Transaction Type<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="transaction_type_id" class="form-control" required>
                                        <option value="">Select Type</option>
                                        @foreach ($transaction_types as $transaction_type)
                                            <option value="{{$transaction_type->id}}">{{$transaction_type->name}}</option>
                                        @endforeach
                                      
                                    </select>
                                @error('transaction_type_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>

                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Amount<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount" placeholder="Enter Amount" required />
                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Category</label>
                                    <select wire:model.debounce.300ms="transaction_category" class="form-control">
                                        <option value="">Select Category Type</option>
                                        @foreach ($account_types as $account_type)
                                        <optgroup label="{{ $account_type->name }}">
                                            @foreach ($account_type->accounts as $account)
                                                <option value="{{ $account->name }}">{{ $account->name }} {{ $account->currency ? $account->currency->name : "" }}</option>
                                            @endforeach
                                        </optgroup>
                                        @endforeach
                                    </select>
                                    @error('transaction_category') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="mb-10">
                        <input type="checkbox" wire:model.debounce.300ms="customer"   class="line-style" />
                        <label for="one" class="radio-label">Add Customer</label>
                        <input type="checkbox" wire:model.debounce.300ms="vendor"   class="line-style" />
                        <label for="one" class="radio-label">Add Vendor</label>
                        @error('vendor') <span class="text-danger error">{{ $message }}</span>@enderror
                    </div>
                 
                    <div class="row">
                        @if ($customer == TRUE)
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Customers</label>
                                    <select wire:model.debounce.300ms="customer_id" class="form-control">
                                        <option value="">Select Customer</option>
                                        @foreach ($customers as $customer)
                                             <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                        @endforeach
                                     
                                    </select>
                                @error('customer_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @endif
                        @if ($vendor == TRUE)
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Vendors</label>
                                    <select wire:model.debounce.300ms="vendor_id" class="form-control">
                                        <option value="">Select Vendor</option>
                                        @foreach ($vendors as $vendor)
                                             <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                                        @endforeach
                                     
                                    </select>
                                @error('vendor_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @endif
                    </div>
                   
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Notes</label>
                               <textarea class="form-control" wire:model.debounce.300ms="notes" cols="30" rows="3" placeholder="Enter Notes"></textarea>
                                @error('notes') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Receipt</label>
                               <input type="file" class="form-control" wire:model.debounce.300ms="receipt">
                                @error('receipt') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="accountModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Account <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Account Types<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="account_type_id" class="form-control" required>
                                        <option value="">Select Account Type</option>
                                        @foreach ($account_types as $account_type)
                                             <option value="{{ $account_type->id }}">{{ $account_type->name }}</option>
                                        @endforeach
                                    </select>
                                @error('account_type_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Account Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Account Name" required />
                                @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Account Currency</label>
                                    <select wire:model.debounce.300ms="selectedCurrency" class="form-control">
                                        <option value="">Select Currency</option>
                                        @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                        @endforeach
                                    </select>
                                @error('selectedCurrency') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Account Reference</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="account_reference" placeholder="Enter Account Ref"  />
                                @error('account_reference') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>

                           </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">GL Code</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="code" placeholder="Enter GL Code" />
                                @error('code') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        @php $selectedTypeName = $account_types->firstWhere('id', $account_type_id)?->name; @endphp
                        @if($selectedTypeName === 'Cash & Bank')
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">&nbsp;</label>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" wire:model="create_bank_account"> Create the associated bank account record
                                    </label>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>

                    @if($selectedTypeName === 'Cash & Bank' && $create_bank_account)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Bank Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="new_bank_name" placeholder="e.g. FBC" required />
                                @error('new_bank_name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Account Holder Name</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="new_bank_account_name" placeholder="Enter Account Holder Name" />
                                @error('new_bank_account_name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Account Number</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="new_bank_account_number" placeholder="Enter Account Number" />
                                @error('new_bank_account_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Branch</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="new_bank_branch" placeholder="Enter Branch" />
                                @error('new_bank_branch') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Branch Code</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="new_bank_branch_code" placeholder="Enter Branch Code" />
                                @error('new_bank_branch_code') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">SWIFT Code</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="new_bank_swift_code" placeholder="Enter SWIFT Code" />
                                @error('new_bank_swift_code') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Linked Account - Bank Account</label>
                                    <select wire:model.debounce.300ms="bank_account_id" class="form-control">
                                        <option value="">Select Bank Account</option>
                                        @foreach ($bank_accounts as $bank_account)
                                             <option value="{{ $bank_account->id }}">{{ $bank_account->name }} {{ $bank_account->account_number }} {{ $bank_account->branch }}</option>
                                        @endforeach
                                    </select>
                                    <small>  <a href="{{ route('bank_accounts.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Bank Account</a></small>
                                @error('bank_account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Description</label>
                               <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="3"></textarea>
                                @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="accountEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Account <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Account Types<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="account_type_id" class="form-control" required @if($lock_type_currency) disabled @endif>
                                            <option value="">Select Account Type</option>
                                            @foreach ($account_types as $account_type)
                                                 <option value="{{ $account_type->id }}">{{ $account_type->name }}</option>
                                            @endforeach
                                        </select>
                                    @if($lock_type_currency)
                                        <small class="text-muted">Cash &amp; Bank account type can't be changed after creation.</small>
                                    @endif
                                    @error('account_type_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Account Name<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Account Name" required />
                                    @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Account Currency</label>
                                        <select wire:model.debounce.300ms="selectedCurrency" class="form-control" @if($lock_type_currency) disabled @endif>
                                            <option value="">Select Currency</option>
                                            @foreach ($currencies as $currency)
                                            <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>
                                            @endforeach
                                        </select>
                                    @if($lock_type_currency)
                                        <small class="text-muted">Currency can't be changed after creation for Cash &amp; Bank accounts.</small>
                                    @endif
                                    @error('selectedCurrency') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                           <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Account Reference</label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="account_reference" placeholder="Enter Account Ref"  />
                                @error('account_reference') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>

                           </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">GL Code</label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="code" placeholder="Enter GL Code" />
                                    @error('code') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Linked Account - Bank Account</label>
                                        <select wire:model.debounce.300ms="bank_account_id" class="form-control">
                                            <option value="">Select Bank Account</option>
                                            @foreach ($bank_accounts as $bank_account)
                                                 <option value="{{ $bank_account->id }}">{{ $bank_account->name }} {{ $bank_account->account_number }} {{ $bank_account->branch }}</option>
                                            @endforeach
                                        </select>
                                        <small>  <a href="{{ route('bank_accounts.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Bank Account</a></small>
                                    @error('bank_account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Description</label>
                                   <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="3"></textarea>
                                    @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
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



</div>

