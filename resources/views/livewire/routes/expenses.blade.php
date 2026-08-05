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
                                <a href="#" data-toggle="modal" data-target="#expenseModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i> Expense</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search expenses...">
                                </div>
                            </div>
                            <table id="expensesTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Expense
                                    </th>
                                    <th class="th-sm">Category
                                    </th>
                                    <th class="th-sm">MOP
                                    </th>
                                    <th class="th-sm">Currency
                                    </th>
                                    <th class="th-sm">Amount
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($route_expenses))
                                <tbody>
                                    @forelse ($route_expenses as $route_expense)
                                  <tr>
                                    <td>{{$route_expense->expense ? $route_expense->expense->name : ""}} @if ($route_expense->source == 'fuel') <span class="badge bg-info" title="Auto-generated from the route's fuel fields">Auto</span> @endif</td>
                                    <td>{{$route_expense->category}}</td>
                                    <td>{{$route_expense->payment_method ? $route_expense->currency->name : ""}}</td>
                                    <td>{{$route_expense->currency ? $route_expense->currency->name : ""}}</td>
                                    <td>{{number_format($route_expense->amount ? $route_expense->amount : 0,2)}}</td>
                                    <td><span class="badge bg-{{$route_expense->status == 1 ? "success" : "danger"}}">{{$route_expense->status == 1 ? "Active" : "Inactive"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        @if ($route_expense->source == 'fuel')
                                            <small class="text-muted">Edit via Route</small>
                                        @else
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#"  wire:click="edit({{$route_expense->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#"  wire:click="removeExpense({{$route_expense->id}})" ><i class="fa fa-trash color-danger"></i> Delete</a></li>
                                            </ul>
                                        </div>
                                        @endif

                                </td>
                                  </tr>
                                  @empty
                                        <tr>
                                            <td colspan="6">
                                                <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                                    No Expenses Found ....
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
                                        @if (isset($route_expenses))
                                            {{ $route_expenses->links() }} 
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

    <div data-backdrop="static" data-keyboard="false" class="modal fade" id="expenseDeleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content bg-danger">
            <div class="modal-body">
               <center> <strong>Are you sure you want to delete this Route Expense?</strong> </center> 
            </div>
            <form wire:submit.prevent="delete()">
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="expenseModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog  mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Expense <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="from">Expenses<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="expense_id.0" class="form-control" required >
                                        <option value="">Select Expense</option>
                                        @foreach ($expenses as $expense)
                                                <option value="{{ $expense->id }}">{{ $expense->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('expense_id.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="from">Categories<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="category.0" class="form-control" required >
                                        <option value="">Select Category</option>
                                        <option value="Customer">Customer</option>
                                        <option value="Self">Self</option>
                                        <option value="Transporter">Transporter</option>
                                    </select>
                                    @error('category.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="payment_method_id">Payment Methods</label>
                                    <select wire:model.debounce.300ms="payment_method_id.0" class="form-control">
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
                                    <label for="from">Currencies</label>
                                    <select wire:model.debounce.300ms="currency_id.0" class="form-control">
                                        <option value="">Select Currency</option>
                                        @foreach ($currencies as $currency)
                                                <option value="{{ $currency->id }}">{{ $currency->name}}</option>
                                        @endforeach
                                    </select>
                                    @error('currency_id.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="name">Amount</label>
                                    <input type="number" min="1" class="form-control" wire:model.debounce.300ms="amount.0" placeholder="Enter expense mmount for the route"/>
                                    @error('amount.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                
                        @foreach ($inputs as $key => $value)
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="from">Expenses<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="expense_id.{{$value}}" class="form-control" required >
                                            <option value="">Select Expense</option>
                                            @foreach ($expenses as $expense)
                                                    <option value="{{ $expense->id }}">{{ $expense->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('expense_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="from">Categories<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="category.{{$value}}" class="form-control" required >
                                            <option value="">Select Category</option>
                                            <option value="Customer">Customer</option>
                                            <option value="Self">Self</option>
                                            <option value="Transporter">Transporter</option>
                                        </select>
                                        @error('category.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="from">Currencies</label>
                                        <select wire:model.debounce.300ms="currency_id.{{$value}}" class="form-control">
                                            <option value="">Select Currency</option>
                                            @foreach ($currencies as $currency)
                                                    <option value="{{ $currency->id }}">{{ $currency->name}}</option>
                                            @endforeach
                                        </select>
                                        @error('currency_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="name">Amount</label>
                                        <input type="number" min="1" class="form-control" wire:model.debounce.300ms="amount.{{$value}}" placeholder="Enter expense mmount for the route"/>
                                        @error('amount.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                 <div class="col-md-1" style="margin-top:27px;" >
                                    <button class="btn btn-danger btn-rounded btn-sm" wire:click.prevent="remove({{ $key }})">
                                        <i class="fa fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group text-end mt-10" style="float: right">
                                    <button class="btn btn-success btn-rounded btn-sm" wire:click.prevent="add({{ $i }})">
                                        <i class="fa fa-plus"></i> Expense
                                    </button>
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
        <div class="modal-dialog  mw-100 w-50" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Expense <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >

                <div class="modal-body">
           <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="from">Expenses<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="expense_id" class="form-control" required >
                                    <option value="">Select Expense</option>
                                    @foreach ($expenses as $expense)
                                            <option value="{{ $expense->id }}">{{ $expense->name }}</option>
                                    @endforeach
                                </select>
                                @error('expense_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="from">Categories<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="category" class="form-control" required >
                                    <option value="">Select Category</option>
                                    <option value="Customer">Customer</option>
                                    <option value="Self">Self</option>
                                    <option value="Transporter">Transporter</option>
                                </select>
                                @error('category') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="from">Currencies</label>
                                <select wire:model.debounce.300ms="currency_id" class="form-control">
                                    <option value="">Select Currency</option>
                                    @foreach ($currencies as $currency)
                                            <option value="{{ $currency->id }}">{{ $currency->name}}</option>
                                    @endforeach
                                </select>
                                @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Amount</label>
                                <input type="number" min="1" class="form-control" wire:model.debounce.300ms="amount" placeholder="Enter expense mmount for the route"/>
                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="from">Status</label>
                                <select wire:model.debounce.300ms="status" class="form-control">
                                    <option value="">Select Option</option>
                                    <option value="1">Active</option>
                                    <option value="0">InActive</option>
                                </select>
                                @error('status') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

