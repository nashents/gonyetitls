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
                                <a href="" data-toggle="modal" data-target="#product_serviceModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Item</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Name
                                    </th>
                                    <th class="th-sm">Description
                                    </th>
                                    <th class="th-sm">Price
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($products->count()>0)
                                <tbody>
                                    @forelse ($products as $product)
                                  <tr>
                                    <td>{{ucfirst($product->name)}}</td>
                                    <td>{{$product->description}}</td>
                                    <td>
                                        @if ($product->price)
                                        {{Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->symbol : ""}}{{number_format($product->price,2)}}        
                                        @endif
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                {{-- <li><a href="{{route('product_services.show',$product->id)}}"  ><i class="fa fa-eye color-default"></i> View</a></li> --}}
                                                <li><a href="#"  wire:click="edit({{$product->id}})" ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#productDeleteModal{{ $product->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('products.delete')
                                </td>
                                  </tr>
                                  @endforeach
                                </tbody>
                                @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                 @endif
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="product_serviceModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> New Product / Service<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    @if ($response)
                    <div class="row">
                       <div class="col-md-12">
                        <p style="color:red">{{$response}}</p>
                       </div>
                        
                    </div>
                    @endif
                   
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="comment">Item Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Item Name" required>
                                @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="comment">Description</label>
                            <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="4"></textarea>
                                @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-10">
                                <label for=""></label>
                                <input type="checkbox" wire:model.debounce.300ms="sell" class="line-style" />
                                <label for="one" class="radio-label">Sell this?</label>
                                @error('sell') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-10">
                                <input type="checkbox" wire:model.debounce.300ms="buy" class="line-style" />
                                <label for="one" class="radio-label">Buy this?</label>
                                @error('buy') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                       
                        <div class="col-md-6">
                             @if (!is_null($sell) && $sell == True)
                            <div class="form-group">
                                <label for="subheading">Income Accounts<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="income_account_id" class="form-control" required>
                                    <option value="">Select Income Account</option>
                                        @foreach ($income_accounts as $account)
                                        <option value="{{$account->id}}">{{$account->name}} </option> 
                                        @endforeach
                                    </select>
                                @error('income_account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                             @endif
                        </div>
                       
                       
                        <div class="col-md-6">
                             @if (!is_null($buy) && $buy == True)
                            <div class="form-group">
                                <label for="subheading">Expense Accounts<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="expense_account_id" class="form-control" required>
                                    <option value="">Select Expense Account</option>
                                        @foreach ($expense_accounts as $account)
                                        <option value="{{$account->id}}">{{$account->name}} </option> 
                                        @endforeach
                                    </select>
                                @error('expense_account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                             @endif
                        </div>
                       
                       
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="comment">Price</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="price" placeholder="Enter Price" >
                                @error('price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="subheading">Sales Tax</label>
                                <select wire:model.debounce.300ms="tax_account_id" class="form-control">
                                    <option value="">Select Tax</option>
                                        @foreach ($tax_accounts as $tax)
                                        <option value="{{$tax->id}}">{{$tax->abbreviation}} {{$tax->rate ? $tax->rate."%" : ""}}</option> 
                                        @endforeach
                                    </select>
                                    <small><a href="{{ route('taxes.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small> 
                                @error('tax_account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="product_serviceEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-edit"></i> Edit Product / Service<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    @if ($response)
                    <div class="row">
                       <div class="col-md-12">
                        <p style="color:red">{{$response}}</p>
                       </div>
                        
                    </div>
                    @endif
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="comment">Item Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Item Name" required>
                                @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="comment">Description</label>
                            <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="4"></textarea>
                                @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-10">
                                <label for=""></label>
                                <input type="checkbox" wire:model.debounce.300ms="sell" class="line-style" />
                                <label for="one" class="radio-label">Sell this?</label>
                                @error('sell') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-10">
                                <input type="checkbox" wire:model.debounce.300ms="buy" class="line-style" />
                                <label for="one" class="radio-label">Buy this?</label>
                                @error('buy') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                       
                        <div class="col-md-6">
                             @if (!is_null($sell) && $sell == True)
                            <div class="form-group">
                                <label for="subheading">Income Accounts<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="income_account_id" class="form-control" required>
                                    <option value="">Select Income Account</option>
                                        @foreach ($income_accounts as $account)
                                        <option value="{{$account->id}}">{{$account->name}} </option> 
                                        @endforeach
                                    </select>
                                @error('income_account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                            @endif
                        </div>
                      
                      
                        <div class="col-md-6">
                              @if (!is_null($buy) && $buy == True)
                            <div class="form-group">
                                <label for="subheading">Expense Accounts<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="expense_account_id" class="form-control" required>
                                    <option value="">Select Expense Account</option>
                                        @foreach ($expense_accounts as $account)
                                        <option value="{{$account->id}}">{{$account->name}} </option> 
                                        @endforeach
                                    </select>
                                @error('expense_account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                               @endif
                        </div>
                     
                       
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="comment">Price</label>
                                <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="price" placeholder="Enter Price" >
                                @error('price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="subheading">Sales Tax</label>
                                <select wire:model.debounce.300ms="tax_account_id" class="form-control">
                                    <option value="">Select Tax</option>
                                        @foreach ($tax_accounts as $tax)
                                        <option value="{{$tax->id}}">{{$tax->abbreviation}} {{$tax->rate ? $tax->rate."%" : ""}}</option> 
                                        @endforeach
                                    </select>
                                    <small><a href="{{ route('taxes.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small> 
                                @error('tax_account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
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

