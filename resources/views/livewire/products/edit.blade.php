<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Edit Product</h5>
                            </div>
                        </div>
                        <div class="panel-body">
                        <form wire:submit.prevent="update()" >
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="country">Department<span class="required" style="color: red">*</span></label>
                                       <select wire:model.debounce.300ms="department" class="form-control" required>
                                           <option value="">Select Department</option>
                                           <option value="asset">Assets</option>
                                           <option value="inventory">Inventory</option>
                                           <option value="tyre">Tyres</option>
                                       </select>
                                        @error('department') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="country">Categories<span class="required" style="color: red">*</span></label>
                                       <select wire:model.debounce.300ms="selectedCategory" class="form-control" required>
                                           <option value="">Select Category</option>
                                         @foreach ($categories as $category)
                                            <option value="{{$category->id}}">{{$category->name}}</option>
                                         @endforeach
                                       </select>
                                       <small>  <a href="#" data-toggle="modal" data-target="#categoryModal" ><i class="fa fa-plus-square-o"></i> New Category</a></small> 
                                        @error('selectedCategory') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="country">Sub Categories</label>
                                       <select wire:model.debounce.300ms="selectedCategoryValue" class="form-control">
                                           <option value="">Select Sub Category</option>
                                         @foreach ($category_values as $value)
                                            <option value="{{$value->id}}">{{$value->name}}</option>
                                         @endforeach
                                       </select>
                                       <small><a href="#" data-toggle="modal" data-target="#categoryValueModal" ><i class="fa fa-plus-square-o"></i> New Sub Category</a></small> 
                                        @error('selectedCategoryValue') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="brand">Brands</label>
                                       <select wire:model.debounce.300ms="brand_id" class="form-control">
                                           <option value="">Select Brand</option>
                                         @foreach ($brands as $brand)
                                            <option value="{{$brand->id}}">{{$brand->name}}</option>
                                         @endforeach
                                       </select>
                                       <small><a href="#" data-toggle="modal" data-target="#brandModal" ><i class="fa fa-plus-square-o"></i> New Brand</a></small> 
                                        @error('brand_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Name<span class="required" style="color: red">*</span></label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Product Name, Model etc" required>
                                    @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="name">Identification#</label>
                                        <input type="text" class="form-control" wire:model.debounce.300ms="identification_number" placeholder="Product ID# eg Model#/Part#" >
                                        @error('identification_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                 <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="name">Unit Of Measure<span class="required" style="color: red">*</span></label>
                                        <select class="form-control" wire:model.debounce.300ms="unit_of_measure" required>
                                            <option value="">Select Unit Of Measure</option>
                                            <option value="Cubic">Cubic</option>
                                            <option value="Each">Each</option>
                                            <option value="Item(s)">Item(s)</option>
                                            <option value="Kg(s)">Kg(s)</option>
                                            <option value="Litres">Litres</option>
                                            <option value="Metre(s)">Metre(s)</option>
                                            <option value="Piece(s)">Piece(s)</option>
                                            <option value="Ton(s)">Ton(s)</option>
                                        </select>
                                        @error('unit_of_measure') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-10">
                                        <input type="checkbox" wire:model.debounce.300ms="buy"   class="line-style" />
                                        <label for="one" class="radio-label">Buy this?</label>
                                        @error('buy') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-10">
                                        <label for=""></label>
                                        <input type="checkbox" wire:model.debounce.300ms="sell"   class="line-style"/>
                                        <label for="one" class="radio-label">Sell this?</label>
                                        @error('sell') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                
                            </div>
                            <div class="row">
                              
                                @if (!is_null($buy) && $buy == True)
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="subheading">Expense Accounts<span class="required" style="color: red">*</span></label>
                                        <select wire:model.debounce.300ms="expense_account_id" class="form-control" required>
                                            <option value="">Select Expense Account</option>
                                                @foreach ($expense_accounts as $account)
                                                <option value="{{$account->id}}">{{$account->name}} </option> 
                                                @endforeach
                                            </select>
                                            <small><a href="{{ route('accounts.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Account</a></small> 
                                        @error('expense_account_id') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="name">Buying Price</label>
                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="buy_price" placeholder="Enter Buying Price">
                                        @error('buy_price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                @endif
                                @if (!is_null($sell) && $sell == True)
                                <div class="col-md-3">
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
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="name">Selling Price</label>
                                        <input type="number" step="any" min="0" class="form-control" wire:model.debounce.300ms="sell_price" placeholder="Enter Selling Price">
                                        @error('sell_price') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                @endif
                               
                            </div>
                           
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="subheading">Sales Tax</label>
                                <select wire:model.debounce.300ms="selectedTax" class="form-control">
                                    <option value="">Select Tax</option>
                                        @foreach ($tax_accounts as $tax)
                                        <option value="{{$tax->id}}">{{$tax->abbreviation}} {{$tax->rate ? $tax->rate."%" : ""}}</option> 
                                        @endforeach
                                    </select>
                                    <small><a href="{{ route('taxes.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small> 
                                @error('selectedTax') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="manufacturer">Manufacturer</label>
                              <input type="text" class="form-control" placeholder="Enter Product Manufacturer" wire:model.debounce.300ms="manufacturer">
                                @error('manufacturer') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                            </div>
                    </div>

                            <div class="row">
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="footer">Additional Notes</label>
                                       <textarea class="form-control" wire:model.debounce.300ms="description" cols="30" rows="3" ></textarea>
                                        @error('description') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="image">Product Image</label>
                                            <small>Selected Image: <img src="{{asset('images/uploads/'.$previous_image)}}" alt="" style="width: 25%; height:25%;"></small>
                                            <br>
                                            <br>
                                          <input type="file" class="form-control" wire:model.debounce.300ms="image" placeholder="Enter Product Image">
                                            @error('image') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                        </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="brand">Status</label>
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
                                <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                                <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
                            </div>
                            <!-- /.btn-group -->
                        </div>
                    </form>
                        </div>
                    </div>
                </div>
                <!-- /.col-md-6 -->


            </div>

        </div>
        <!-- /.container-fluid -->
    </section>


    
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="brandModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Brand <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="storeBrand()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Categories<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="selectedCategory" class="form-control" required>
                                        <option value="">Select Category</option>
                                        @foreach ($categories as $category)
                                            <option value="{{$category->id}}">{{$category->name}}</option>
                                        @endforeach
                                </select>
                                <small><a href="#" data-toggle="modal" data-target="#categoryModal" ><i class="fa fa-plus-square-o"></i> New Category</a></small> 
                                @error('selectedCategory') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Sub Categories</label>
                                <select wire:model.debounce.300ms="selectedCategoryValue" class="form-control" >
                                    <option value="">Select Sub Category</option>
                                    @if (!is_null($selectedCategory))
                                    @foreach ($category_values as $value)
                                    <option value="{{$value->id}}">{{$value->name}}</option>
                                    @endforeach
                                    @endif
                                </select>
                                <small><a href="#" data-toggle="modal" data-target="#categoryValueModal" ><i class="fa fa-plus-square-o"></i> New Sub Category</a></small> 
                                @error('selectedCategoryValue') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="name">Name<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="brand_name" placeholder="Enter Brand Name" required />
                                @error('brand_name') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="categoryValueModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Sub Category <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="storeSubCategory()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Categories<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="selectedCategory" class="form-control" required>
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $category)
                                    <option value="{{$category->id}}">{{$category->name}}</option>
                                    @endforeach
                                </select>
                                <small>  <a href="#" data-toggle="modal" data-target="#categoryModal" ><i class="fa fa-plus-square-o"></i> New Category</a></small> 
                                @error('selectedCategory') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Sub Category<span class="required" style="color: red">*</span></label>
                                <input type="text" class="form-control" wire:model.debounce.300ms="sub_category_name" placeholder="Enter Sub-category" required>
                                @error('sub_category_name') <span class="error" style="color:red">{{ $message }}</span> @enderror
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


    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="categoryModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-plus"></i> Add Category  <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="storeCategory()" >
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Category<span class="required" style="color: red">*</span></label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="category_name" required>
                        @error('category_name') <span class="error" style="color:red">{{ $message }}</span> @enderror
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
