<div>
    {{-- <blockquote class="blockquote-reverse mt-20"> --}}
      
        <table id="bill_expensesTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead >
                <th class="th-sm">Item
                </th>
                <th class="th-sm">Expense Account
                </th>
                <th class="th-sm">Description
                </th>
                <th class="th-sm">Qty
                </th>
                <th class="th-sm">Price
                </th>
                <th class="th-sm">Subtotal(Excl)
                </th>
                <th class="th-sm">Tax Amt
                </th>
                <th class="th-sm">Subtotal(Incl)
                </th>
              
              </tr>
            </thead>
            <tbody>
                @if ($bill_expenses->count()>0)
                @foreach ($bill_expenses as $bill_expense)
              <tr>
                <td>{{ $bill_expense->product ? $bill_expense->product->name : ""}}</td>
                <td>
                    @if ($bill_expense->account)
                        {{$bill_expense->account ? $bill_expense->account->name : ""}}
                    @endif
                  </td>
                  <td>{{ $bill_expense->description}}</td>
                  <td>{{ $bill_expense->qty}}</td>
                  <td>
                    @if ($bill_expense->amount)
                         {{ $bill_expense->currency ? $bill_expense->currency->symbol : ""}}{{ number_format($bill_expense->amount,2)}}
                    @endif
                  </td>
                  <td>
                    @if ($bill_expense->subtotal)
                    {{ $bill_expense->currency ? $bill_expense->currency->symbol : ""}}{{ number_format($bill_expense->subtotal,2)}}
                    @endif
                  </td>
                  <td>
                    @if ($bill_expense->tax_amount)
                    {{ $bill_expense->currency ? $bill_expense->currency->symbol : ""}}{{ number_format($bill_expense->tax_amount,2)}}
                    @endif
                  </td>
                  <td>
                    @if ($bill_expense->subtotal_incl)
                    {{ $bill_expense->currency ? $bill_expense->currency->symbol : ""}}{{ number_format($bill_expense->subtotal_incl,2)}}
                    @endif
                  </td>
              
               
            </tr>
              @endforeach
            </tbody>
            @else
                <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
             @endif
          </table>

          <div data-backdrop="static" data-keyboard="false" class="modal fade" id="bill_expenseDeleteModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content bg-danger">
                    <div class="modal-body">
                       <center> <strong>Are you sure you want to delete this Bill Expense</strong> </center>
                    </div>
                    <form wire:submit.prevent="delete()"  >
                       
                        <input type="hidden" name="_method" value="DELETE">
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
        
         
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="bill_expenseModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="border">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Expense(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
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
                                @error('selectedProduct') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
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
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Description</label>
                               <textarea wire:model.debounce.300ms="description" class="form-control" cols="30" rows="1" placeholder="Enter Description"></textarea>
                                @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="name">Qty<span class="required" style="color: red">*</span></label>
                                <input type="number" class="form-control" wire:model.debounce.300ms="qty"  required />
                                @error('qty') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="name">Price<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="amount" required />
                                @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="bill_expenseEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="border">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Bill Expense <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
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
                                    @error('selectedProduct') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                        </div>
                        <div class="col-md-4">
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
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="name">Description</label>
                               <textarea wire:model.debounce.300ms="description" class="form-control" cols="30" rows="1" placeholder="Enter Description"></textarea>
                                @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="">Qty</label>
                            <input type="number"  wire:model.debounce.300ms="qty" class="form-control" required/>
                            @error('qty') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                     
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="">Price</label>
                            <input type="number" step="any" wire:model.debounce.300ms="amount" class="form-control" required/>
                            @error('amount') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
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
   
</div>
