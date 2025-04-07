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
                                <a href="" data-toggle="modal" data-target="#bank_accountModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Bank Account</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="bank_accountsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead >
                                    <th class="th-sm">Bank
                                    </th>
                                    <th class="th-sm">Name
                                    </th>
                                    <th class="th-sm">Acc#
                                    </th>
                                    <th class="th-sm">Ccy
                                    </th>
                                    <th class="th-sm">Branch
                                    </th>
                                    <th class="th-sm">Swift Code
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Actions
                                    </th>
                                  </tr>
                                </thead>
                                <tbody>
                                    @foreach ($bank_accounts as $bank_account)
                                  <tr>
                                    <td>{{$bank_account->name}}</td>
                                    <td>{{$bank_account->account_name}}</td>
                                    <td>{{$bank_account->account_number}}</td>
                                    <td>{{$bank_account->currency ? $bank_account->currency->name : ""}}</td>
                                    <td>
                                        {{$bank_account->branch}}
                                        <br>
                                        {{$bank_account->branch_code}}
                                    </td>
                                    <td>{{$bank_account->swift_code}}</td>
                                    <td><span class="badge bg-{{$bank_account->status == 1 ? "success" : "danger"}}">{{$bank_account->status == 1 ? "Active" : "Inactive"}}</span></td>
                    
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#" wire:click="edit({{$bank_account->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#bank_accountDeleteModal{{$bank_account->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('bank_accounts.delete')
                    
                                </td>
                                </tr>
                                  @endforeach
                                </tbody>
                              </table>

                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="bank_accountModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="bank_account">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Bank Account(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="title">Bank<span class="required" style="color: red">*</span></label>
                            <input type="text" class="form-control"  wire:model.debounce.300ms="name.0" placeholder="Enter Bank Name" required />
                            @error('name.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="file">Account Name<span class="required" style="color: red">*</span></label>
                            <input type="text" class="form-control"  wire:model.debounce.300ms="account_name.0" placeholder="Enter Account Name" required/>
                            @error('account_name.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                   
                   
                </div>
                <div class="row">
                   
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="file">Account Number<span class="required" style="color: red">*</span></label>
                            <input type="text" class="form-control"  wire:model.debounce.300ms="account_number.0" placeholder="Enter Account Number" required/>
                            @error('account_number.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="title">Account Currency<span class="required" style="color: red">*</span></label>
                            <select class="form-control" wire:model.debounce.300ms="currency_id.0" required>
                                <option value="">Select Currency</option>
                              @foreach ($currencies as $currency)
                                  <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                              @endforeach
                            </select>
                            @error('currency_id.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="expiry_date">Account Branch<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control"  wire:model.debounce.300ms="branch.0" placeholder="Enter Branch" required />
                                @error('branch.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="expiry_date">Branch Code</label>
                                <input type="text" class="form-control"  wire:model.debounce.300ms="branch_code.0" placeholder="Enter Branch Code" />
                                @error('branch_code.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="expiry_date">Swift Code</label>
                                <input type="text" class="form-control"  wire:model.debounce.300ms="swift_code.0" placeholder="Enter Swift Code" />
                                @error('swift_code.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    
                       
                        @foreach ($inputs as $key => $value)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Bank<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control"  wire:model.debounce.300ms="name.{{ $value }}" placeholder="Enter Bank Name " required/>
                                    @error('name.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="file">Account Name<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control"  wire:model.debounce.300ms="account_name.{{ $value }}" placeholder="Enter Account Name" required/>
                                    @error('account_name.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            
                         
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="file">Account Number<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control"  wire:model.debounce.300ms="account_number.{{ $value }}" placeholder="Enter Account Number" required />
                                    @error('account_number.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Account Currency<span class="required" style="color: red">*</span></label>
                                    <select class="form-control" wire:model.debounce.300ms="currency_id.{{ $value }}" required>
                                        <option value="">Select Currency</option>
                                      @foreach ($currencies as $currency)
                                          <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                      @endforeach
                                    </select>
                                    @error('currency_id.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="expiry_date">Account Branch<span class="required" style="color: red">*</span></label>
                                        <input type="text" class="form-control"  wire:model.debounce.300ms="branch.{{ $value }}" placeholder="Enter Branch" required />
                                        @error('branch.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="expiry_date">Branch Code</label>
                                        <input type="text" class="form-control"  wire:model.debounce.300ms="branch_code.{{ $value }}" placeholder="Enter Branch Code" >
                                        @error('branch_code.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="expiry_date">Swift Code</label>
                                        <input type="text" class="form-control"  wire:model.debounce.300ms="swift_code.{{ $value }}" placeholder="Enter Swift Code" >
                                        @error('swift_code.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                                    </div>
                                </div>
                            </div>
                            
                        @endforeach
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Bank Account</button>
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="bank_accountEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="bank_account">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Bank Account <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Bank<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control"  wire:model.debounce.300ms="name" placeholder="Enter Bank Name" required />
                                    @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                                    <select class="form-control" wire:model.debounce.300ms="currency_id" required>
                                        <option value="">Select Currency</option>
                                      @foreach ($currencies as $currency)
                                          <option value="{{ $currency->id }}">{{ $currency->name }}</option>
                                      @endforeach
                                    </select>
                                    @error('currency_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="expiry_date">Account Branch<span class="required" style="color: red">*</span></label>
                                        <input type="text" class="form-control"  wire:model.debounce.300ms="branch" placeholder="Enter Branch" required />
                                        @error('branch.0') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                           
                           
                           
                            <div class="form-group">
                                <label for="title">Status</label>
                                <select class="form-control" wire:model.debounce.300ms="status">
                                    <option value="">Select Status</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                                @error('status') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-save"></i>Update</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>



</div>

