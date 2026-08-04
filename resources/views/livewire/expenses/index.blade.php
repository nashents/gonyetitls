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
                                <a href="" data-toggle="modal" data-target="#expenseModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Expense</a>
                                <a href="" data-toggle="modal" data-target="#expenseImportModal" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-upload"></i>Import</a>
                                <a href="#" wire:click="exportExpensesExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                <a href="#" wire:click="exportExpensesCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                <a href="#" wire:click="exportExpensesPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search expenses...">
                                </div>
                            </div>
                            <table class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Account Type
                                    </th>
                                    <th class="th-sm">Account
                                    </th>
                                    <th class="th-sm">Type
                                    </th>
                                    <th class="th-sm">Name
                                    </th>
                                    <th class="th-sm">MOP
                                    </th>
                                    <th class="th-sm">Currency
                                    </th>
                                    <th class="th-sm">Amount
                                    </th>
                                    <th class="th-sm">Tax
                                    </th>
                                    <th class="th-sm">Item Type
                                    </th>
                                    <th class="th-sm">Frequency
                                    </th>
                                    <th class="th-sm">Description
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($expenses->count()>0)
                                <tbody>
                                    @foreach ($expenses as $expense)
                                  <tr>

                                    <td>
                                        @if ($expense->account)
                                        {{ucfirst($expense->account->account_type ? $expense->account->account_type->name : "")}}
                                        @endif
                                    </td>
                                    <td>{{ucfirst($expense->account ? $expense->account->name : "")}}</td>
                                    <td>{{ucfirst($expense->type)}}</td>
                                    <td>{{ucfirst($expense->name)}}</td>
                                    <td>{{$expense->payment_method ? $expense->payment_method->name : ""}}</td>
                                    <td>{{$expense->currency ? $expense->currency->name : ""}}</td>
                                    <td>
                                        @if ($expense->amount)
                                        {{$expense->currency ? $expense->currency->symbol : ""}}{{number_format($expense->amount,2)}}
                                        @endif
                                    </td>
                                    <td>{{$expense->tax ? $expense->tax->abbreviation : ""}}</td>
                                    <td>{{$expense->item_type}}</td>
                                    <td>{{$expense->frequency}}</td>
                                    <td>{{$expense->description}}</td>
                                    <td><span class="badge bg-{{$expense->status == 1 ? "success" : "danger"}}">{{$expense->status == 1 ? "Active" : "Inactive"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{ route('expenses.show', $expense->id) }}"  ><i class="fa fa-eye color-default"></i> View</a></li>
                                                @if ($expense->user_id != Null)
                                                <li><a href="#"  wire:click="edit({{$expense->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#expenseDeleteModal{{ $expense->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                                @endif
                                               
                                            </ul>
                                        </div>
                                        @include('expenses.delete')
                                </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                                @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                 @endif
                              </table>
                              <nav class="text-center" style="float: right">
                                <ul class="pagination rounded-corners">
                                    @if (isset($expenses))
                                        {{ $expenses->links() }} 
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

        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="expenseImportModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-upload"></i>Import Expense(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="importExpenses()" enctype="multipart/form-data">
                  
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Upload Expenses Excel File<span class="required" style="color: red">*</span></label>
                        <input type="file" class="form-control" wire:model.debounce.300ms="importFile" placeholder="Upload Loading Points File" required>
                        @error('importFile') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button  type="submit"  class="btn bg-success btn-wide btn-rounded"><i class="fa fa-upload"></i>Upload</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="expenseModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Expense <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Accounts<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="account_id" class="form-control" required>
                                <option value="">Select Account</option>
                                @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                @endforeach
                               </select>
                               <small>  <a href="{{ route('accounts.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Expense Account</a></small> <br> 
                                @error('account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Type<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="type" class="form-control" required>
                                <option value="">Select Expense Type</option>
                                <option value="Direct">Direct Expense</option>
                                <option value="Indirect">InDirect Expense</option>
                               </select>
                                @error('type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                   
                    <div class="row">
                        <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Name<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Name" required />
                                    @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Payment Methods</label>
                               <select wire:model.debounce.300ms="payment_method_id" class="form-control">
                                <option value="">Select Payment Method</option>
                                @foreach ($payment_methods as $payment_method)
                                    <option value="{{ $payment_method->id }}">{{ $payment_method->name }}</option>
                                @endforeach
                               </select>
                               <small>  <a href="{{ route('payment_methods.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Payment Method</a></small> <br> 
                               @error('payment_method_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                   
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Currency</label>
                               <select wire:model.debounce.300ms="currency_id" class="form-control">
                                <option value="">Select Currency</option>
                                @foreach ($currencies as $currency)
                                    <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                @endforeach
                               </select>
                                @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Amount</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount" placeholder="Enter Amount" />
                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Tax</label>
                               <select wire:model.debounce.300ms="tax_id" class="form-control">
                                <option value="">Select Tax</option>
                                @foreach ($taxes as $tax)
                                    <option value="{{ $tax->id }}">{{ $tax->abbreviation }}</option>
                                @endforeach
                               </select>
                               <small><a href="{{ route('taxes.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small>
                                @error('tax_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Frequency</label>
                               <select wire:model.debounce.300ms="frequency" class="form-control">
                                <option value="">Select Frequency</option>
                                <option value="Once Off">Once Off</option>
                                <option value="Weekly">Weekly</option>
                                <option value="Monthly">Monthly</option>
                                <option value="Quarterly">Quarterly</option>
                                <option value="Yearly">Yearly</option>
                               </select>
                                @error('frequency') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Description</label>
                                    <textarea class="form-control" wire:model.debounce.300ms="description"  cols="30" rows="7"></textarea>
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="expenseEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Expense <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Expense Accounts<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="account_id" class="form-control" required>
                                <option value="">Select Account</option>
                                @foreach ($accounts as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                @endforeach
                               </select>
                                @error('account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Type<span class="required" style="color: red">*</span></label>
                               <select wire:model.debounce.300ms="type" class="form-control" required>
                                <option value="">Select Expense Type</option>
                                <option value="Direct">Direct Expense</option>
                                <option value="Indirect">InDirect Expense</option>
                               </select>
                                @error('type') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                
                     <div class="row">
                        <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Name<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Name" required />
                                    @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Payment Methods</label>
                               <select wire:model.debounce.300ms="payment_method_id" class="form-control">
                                <option value="">Select Payment Method</option>
                                @foreach ($payment_methods as $payment_method)
                                    <option value="{{ $payment_method->id }}">{{ $payment_method->name }}</option>
                                @endforeach
                               </select>
                               <small>  <a href="{{ route('payment_methods.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Payment Method</a></small> <br> 
                               @error('payment_method_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Currency</label>
                               <select wire:model.debounce.300ms="currency_id" class="form-control">
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
                                <label for="name">Amount</label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount" placeholder="Enter Amount" />
                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Frequency</label>
                               <select wire:model.debounce.300ms="frequency" class="form-control">
                                <option value="">Select Frequency</option>
                                <option value="Once Off">Once Off</option>
                                <option value="Weekly">Weekly</option>
                                <option value="Monthly">Monthly</option>
                                <option value="Quarterly">Quarterly</option>
                                <option value="Yearly">Yearly</option>
                               </select>
                                @error('frequency') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Tax</label>
                               <select wire:model.debounce.300ms="tax_id" class="form-control">
                                <option value="">Select Tax</option>
                                @foreach ($taxes as $tax)
                                    <option value="{{ $tax->id }}">{{ $tax->abbreviation }}</option>
                                @endforeach
                               </select>
                               <small><a href="{{ route('taxes.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small>
                                @error('tax_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Status</label>
                               <select wire:model.debounce.300ms="status" class="form-control">
                                <option value="">Select Status</option>
                                <option value="1">Active</option>
                                <option value="0">InActive</option>
                               </select>
                                @error('status') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Description</label>
                                    <textarea class="form-control" wire:model.debounce.300ms="description"  cols="30" rows="7"></textarea>
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

