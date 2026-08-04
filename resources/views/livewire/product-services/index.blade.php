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
                                @if ($this->sageEnabled)
                                <button wire:click="pullFromSage" wire:loading.attr="disabled" class="btn btn-default border-primary"><i class="fa fa-cloud-download"></i> Pull from Sage</button>
                                @endif
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                             <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search products & services...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Name
                                    </th>
                                    <th class="th-sm">Description
                                    </th>
                                    <th class="th-sm">Price
                                    </th>
                                    <th class="th-sm">Tax
                                    </th>
                                    <th class="th-sm">Type
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($products))
                                <tbody>
                                    @forelse ($products as $product)
                                  <tr>
                                    <td>{{ucfirst($product->name)}}
                                        @if ($this->sageEnabled)
                                        <br>
                                        @php $sm = $product->sageMapping; @endphp
                                        <small class="badge bg-{{ $sm ? ($sm->sync_status === 'synced' ? 'success' : ($sm->sync_status === 'failed' ? 'danger' : ($sm->sync_status === 'requires_attention' ? 'warning' : 'secondary'))) : 'secondary' }}"
                                               title="{{ $sm->last_error ?? '' }}">Sage: {{ $sm ? ucwords(str_replace('_',' ', $sm->sync_status)) : 'Not synced' }}</small>
                                        @endif
                                    </td>
                                    <td>{{$product->description}}</td>
                                    <td>
                                        @if ($product->price)
                                        {{Auth::user()->employee->company->currency ? Auth::user()->employee->company->currency->symbol : ""}}{{number_format($product->price,2)}}
                                        @endif
                                    </td>
                                    <td>{{$product->tax ? $product->tax->abbreviation : ""}}</td>
                                    <td>{{$product->type}}</td>
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
                                  @empty
                                  <tr>
                                    <td colspan="6">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Products / Services Found ....
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
                                    @if (isset($products))
                                        {{ $products->links() }} 
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="product_serviceModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog  mw-100 w-50" role="document">
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
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="comment">Item Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Item Name" required>
                                @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="comment">Description</label>
                            <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="4"></textarea>
                                @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="subheading">Type</label>
                                <select wire:model.debounce.300ms="type" class="form-control">
                                    <option value="">Select Type</option>
                                    <option value="Inventory">Inventory Item</option>
                                    <option value="Non Inventory">Non Inventory Item</option>
                                </select>
                                @error('type') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                                <label for="subheading">Tax Categories</label>
                                <select wire:model.debounce.300ms="tax_account_id" class="form-control">
                                    <option value="">Select Tax</option>
                                        @foreach ($tax_accounts as $tax)
                                        <option value="{{$tax->id}}">{{$tax->abbreviation}}</option>
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
        <div class="modal-dialog mw-100 w-50" role="document">
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
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="comment">Item Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Item Name" required>
                                @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="comment">Description</label>
                            <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="4"></textarea>
                                @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="subheading">Type</label>
                                <select wire:model.debounce.300ms="type" class="form-control">
                                    <option value="">Select Type</option>
                                    <option value="Inventory">Inventory Item</option>
                                    <option value="Non Inventory">Non Inventory Item</option>
                                </select>
                                @error('type') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
                                <label for="subheading">Tax Categories</label>
                                <select wire:model.debounce.300ms="tax_account_id" class="form-control">
                                    <option value="">Select Tax</option>
                                        @foreach ($tax_accounts as $tax)
                                        <option value="{{$tax->id}}">{{$tax->abbreviation}}</option>
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

