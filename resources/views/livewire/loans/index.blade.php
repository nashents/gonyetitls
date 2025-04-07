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
                                <a href="" data-toggle="modal" data-target="#loanModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Loan</a>
                            </div>

                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search loans...">
                                </div>
                            </div>

                            <table class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Loan#
                                    </th>
                                    <th class="th-sm">Movement
                                    </th>
                                    <th class="th-sm">Employee
                                    </th>
                                    <th class="th-sm">Vendor
                                    </th>
                                    <th class="th-sm">Account
                                    </th>
                                    <th class="th-sm">Date
                                    </th>
                                    <th class="th-sm">PBP
                                    </th>
                                    <th class="th-sm">Total
                                    </th>
                                    <th class="th-sm">Instal
                                    </th>
                                    <th class="th-sm">Bal
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Auth
                                    </th>
                                    <th class="th-sm">Actions
                                    </th>

                                  </tr>
                                </thead>
                                @if (isset($loans))
                                <tbody>
                                    @forelse ($loans as $loan)
                                  <tr>
                                    <td>{{$loan->loan_number}}</td>
                                    <td>
                                        <center>
                                            @if ($loan->movement == "In")
                                                <i class="fa fa-arrow-down" style="color: green"></i>
                                            @elseif($loan->movement == "Out") 
                                                <i class="fa fa-arrow-up" style="color: blue"></i>
                                            @endif
                                        </center>
                                    </td>
                                    <td>{{$loan->employee ? $loan->employee->name : ""}} {{$loan->employee ? $loan->employee->surname : ""}} </td>
                                    <td>{{$loan->vendor ? $loan->vendor->name : ""}} </td>
                                    <td>{{$loan->account ? $loan->account->name : ""}}</td>
                                    <td>{{$loan->start_date}}</td>
                                    <td>
                                        {{$loan->currency ? $loan->currency->name : ""}}  {{$loan->currency ? $loan->currency->symbol : ""}}{{number_format($loan->amount,2)}} @ {{number_format($loan->interest ? $loan->interest : 0,2)}}% interest {{$loan->period ? "Payback Period: ".$loan->period." Months" : ""}}     
                                    </td>
                                    <td>
                                        @if ($loan->total)
                                            {{$loan->currency ? $loan->currency->symbol : ""}}{{number_format($loan->total,2)}}        
                                        @endif
                                    </td>
                                    <td> 
                                        @if ($loan->payment_per_month)
                                            {{$loan->currency ? $loan->currency->symbol : ""}}{{number_format($loan->payment_per_month,2)}}        
                                        @endif
                                    </td>
                                    <td> 
                                        @if ($loan->balance)
                                            {{$loan->currency ? $loan->currency->symbol : ""}}{{number_format($loan->balance,2)}}        
                                        @endif
                                    </td>
                                    <td><span class="label label-{{($loan->status == 'Paid') ? 'success' : (($loan->status == 'Partial') ? 'warning' : 'danger') }}">{{ $loan->status }}</span></td>
                                    <td><span class="badge bg-{{($loan->authorization == 'approved') ? 'success' : (($loan->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($loan->authorization == 'approved') ? 'approved' : (($loan->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('loans.show',$loan->id) }}" ><i class="fa fa-eye color-default"></i> View</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#loanEditModal" wire:click.prevent="edit({{$loan->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#loanDeleteModal{{$loan->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('loans.delete')
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="13">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Loans Found ....
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
                                    @if (isset($loans))
                                        {{ $loans->links() }} 
                                    @endif 
                                </ul>
                            </nav>    

                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>
                <!-- /.col-md-6 -->


                <!-- /.col-md-6 -->


                <!-- /.col-md-6 -->

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>


    <!-- Modal -->
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="loanModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Apply Loan <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()">
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group" style="margin-left: 22px">
                            <label for="name">Loan Movement?</label>
                            <label class="radio-inline">
                                <input type="radio" wire:model.debounce.300ms="movement" value="Out" name="optradio" >Loan Out
                              </label>
                              <label class="radio-inline">
                                <input type="radio" wire:model.debounce.300ms="movement" value="In" name="optradio">Loan In
                              </label>
                        </div>
                    </div>
                    @if (isset($movement) && $movement == "In")
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="loan_type">Vendors<span class="required" style="color: red">*</span></label>
                              <select wire:model.debounce.300ms="vendor_id" class="form-control" required >
                                  <option value="">Select Vendor</option>
                                  @foreach ($vendors as $vendor)
                                      <option value="{{$vendor->id}}">{{$vendor->name}}</option>
                                  @endforeach
                              </select>
                              <small> <a href="{{ route('vendors.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Vendor</a></small> 
                                @error('vendor_id') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="loan_type">Liability Accounts</label>
                                <select wire:model.debounce.300ms="account_id" class="form-control"  >
                                    <option value="">Select Liability Account</option>
                                    @foreach ($liability_accounts as $account)
                                        <option value="{{$account->id}}">{{$account->name}}</option>
                                    @endforeach
                                </select>
                                <small> <a href="{{ route('accounts.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Account</a></small> 
                            @error('account_id') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                               <!-- /.col-md-6 -->
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="loan_type">Currencies<span class="required" style="color: red">*</span></label>
                              <select wire:model.debounce.300ms="currency_id" class="form-control" required >
                                  <option value="" selected>Select Currency</option>
                                  @foreach ($currencies as $currency)
                                  <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>                                      
                                  @endforeach
                              </select>
                                @error('currency_id') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="loan_days">Amount<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount" placeholder="$" required/>
                                @error('amount') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <!-- /.col-md-6 -->
                    </div>
                    @elseif(isset($movement) && $movement == "Out")
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="loan_type">Employees<span class="required" style="color: red">*</span></label>
                              <select wire:model.debounce.300ms="employee_id" class="form-control" required >
                                  <option value="">Select Employee</option>
                                  @foreach ($employees as $employee)
                                      <option value="{{$employee->id}}">{{$employee->name}} {{$employee->surname}}</option>
                                  @endforeach
                              </select>
                              <small> <a href="{{ route('employees.create') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Employee</a></small> 
                                @error('employee_id') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="loan_type">Asset Accounts</label>
                                <select wire:model.debounce.300ms="account_id" class="form-control" >
                                    <option value="">Select Asset Account</option>
                                    @foreach ($asset_accounts as $account)
                                        <option value="{{$account->id}}">{{$account->name}} {{$account->account_reference}}</option>
                                    @endforeach
                                </select>
                                <small> <a href="{{ route('accounts.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Account</a></small> 
                            @error('account_id') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                               <!-- /.col-md-6 -->
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="loan_type">Loan Type<span class="required" style="color: red">*</span></label>
                              <select wire:model.debounce.300ms="loan_type_id" class="form-control" required >
                                  <option value="" selected>Select Loan Type</option>
                                  @foreach ($loan_types as $loan_type)
                                      <option value="{{$loan_type->id}}">{{$loan_type->name}}</option>
                                  @endforeach
                              </select>
                              <small>  <a href="{{ route('loan_types.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Loan Type</a></small>
                                @error('loan_type_id') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="loan_type">Currencies<span class="required" style="color: red">*</span></label>
                              <select wire:model.debounce.300ms="currency_id" class="form-control" required >
                                  <option value="" selected>Select Currency</option>
                                  @foreach ($currencies as $currency)
                                  <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>                                      
                                  @endforeach
                              </select>
                                @error('currency_id') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="loan_days">Amount<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount" placeholder="$" required/>
                                @error('amount') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <!-- /.col-md-6 -->
                    </div>
                    @endif
                  
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="date"  required />
                                @error('date') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="to">Period<span class="required" style="color: red">*</span></label>
                                <input type="number" min="1" class="form-control" wire:model.debounce.300ms="period" placeholder="Period in month(s)"  required />
                                @error('period') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Interest</label>
                                <input type="number" class="form-control" wire:model.debounce.300ms="interest" placeholder="%" />
                                @error('interest') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
           

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Total<span class="required" style="color: red">*</span></label>
                                <input type="number" class="form-control" wire:model.debounce.300ms="total" placeholder="$" required />
                                @error('total') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Monthly Payment<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="payment_per_month" placeholder="$" required />
                                @error('payment_per_month') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="name">Purpose of loan</label>
                               <textarea class="form-control" wire:model.debounce.300ms="purpose"  cols="30" rows="5"></textarea>
                                @error('purpose') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <!-- /.col-md-6 -->
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Apply</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="loanEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Loan Application <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()">
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group" style="margin-left: 22px">
                            <label for="name">Loan Movement?</label>
                            <label class="radio-inline">
                                <input type="radio" wire:model.debounce.300ms="movement" value="Out" name="optradio" >Loan Out
                              </label>
                              <label class="radio-inline">
                                <input type="radio" wire:model.debounce.300ms="movement" value="In" name="optradio">Loan In
                              </label>
                        </div>
                    </div>
                    @if (isset($movement) && $movement == "In")
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="loan_type">Vendors<span class="required" style="color: red">*</span></label>
                              <select wire:model.debounce.300ms="vendor_id" class="form-control" required >
                                  <option value="">Select Vendor</option>
                                  @foreach ($vendors as $vendor)
                                      <option value="{{$vendor->id}}">{{$vendor->name}}</option>
                                  @endforeach
                              </select>
                              <small> <a href="{{ route('vendors.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Vendor</a></small> 
                                @error('vendor_id') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="loan_type">Liability Accounts</label>
                                <select wire:model.debounce.300ms="account_id" class="form-control"  >
                                    <option value="">Select Liability Account</option>
                                    @foreach ($liability_accounts as $account)
                                        <option value="{{$account->id}}">{{$account->name}}</option>
                                    @endforeach
                                </select>
                                <small> <a href="{{ route('accounts.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Account</a></small> 
                            @error('account_id') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                               <!-- /.col-md-6 -->
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="loan_type">Currencies<span class="required" style="color: red">*</span></label>
                              <select wire:model.debounce.300ms="currency_id" class="form-control" required >
                                  <option value="" selected>Select Currency</option>
                                  @foreach ($currencies as $currency)
                                  <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>                                      
                                  @endforeach
                              </select>
                                @error('currency_id') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="loan_days">Amount<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount" placeholder="$" required/>
                                @error('amount') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <!-- /.col-md-6 -->
                    </div>
                    @elseif(isset($movement) && $movement == "Out")
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="loan_type">Employees<span class="required" style="color: red">*</span></label>
                              <select wire:model.debounce.300ms="employee_id" class="form-control" required >
                                  <option value="">Select Employee</option>
                                  @foreach ($employees as $employee)
                                      <option value="{{$employee->id}}">{{$employee->name}} {{$employee->surname}}</option>
                                  @endforeach
                              </select>
                              <small> <a href="{{ route('employees.create') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Employee</a></small> 
                                @error('employee_id') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="loan_type">Asset Accounts</label>
                                <select wire:model.debounce.300ms="account_id" class="form-control" >
                                    <option value="">Select Asset Account</option>
                                    @foreach ($asset_accounts as $account)
                                        <option value="{{$account->id}}">{{$account->name}} {{$account->account_reference}}</option>
                                    @endforeach
                                </select>
                                <small> <a href="{{ route('accounts.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Account</a></small> 
                            @error('account_id') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                               <!-- /.col-md-6 -->
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="loan_type">Loan Type<span class="required" style="color: red">*</span></label>
                              <select wire:model.debounce.300ms="loan_type_id" class="form-control" required >
                                  <option value="" selected>Select Loan Type</option>
                                  @foreach ($loan_types as $loan_type)
                                      <option value="{{$loan_type->id}}">{{$loan_type->name}}</option>
                                  @endforeach
                              </select>
                              <small>  <a href="{{ route('loan_types.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Loan Type</a></small>
                                @error('loan_type_id') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="loan_type">Currencies<span class="required" style="color: red">*</span></label>
                              <select wire:model.debounce.300ms="currency_id" class="form-control" required >
                                  <option value="" selected>Select Currency</option>
                                  @foreach ($currencies as $currency)
                                  <option value="{{ $currency->id }}">{{ $currency->name }} ({{ $currency->symbol }}) {{ $currency->fullname }}</option>                                      
                                  @endforeach
                              </select>
                                @error('currency_id') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="loan_days">Amount<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount" placeholder="$" required/>
                                @error('amount') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <!-- /.col-md-6 -->
                    </div>
                    @endif
                  
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Date<span class="required" style="color: red">*</span></label>
                                <input type="date" class="form-control" wire:model.debounce.300ms="date"  required />
                                @error('date') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="to">Period<span class="required" style="color: red">*</span></label>
                                <input type="number" min="1" class="form-control" wire:model.debounce.300ms="period" placeholder="Period in month(s)"  required />
                                @error('period') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Interest</label>
                                <input type="number" class="form-control" wire:model.debounce.300ms="interest" placeholder="%" />
                                @error('interest') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
           

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Total<span class="required" style="color: red">*</span></label>
                                <input type="number" class="form-control" wire:model.debounce.300ms="total" placeholder="$" required />
                                @error('total') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Monthly Payment<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="payment_per_month" placeholder="$" required />
                                @error('payment_per_month') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="name">Purpose of loan</label>
                               <textarea class="form-control" wire:model.debounce.300ms="purpose"  cols="30" rows="5"></textarea>
                                @error('purpose') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <!-- /.col-md-6 -->
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
