<div>
    {{-- <blockquote class="blockquote-reverse mt-20"> --}}
        <x-loading/>
        {{-- @if ($purchase->authorization != "approved")
        <a href="" data-toggle="modal" data-target="#productModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Product</a>
       @endif --}}
        <br>
        <br>
        <table id="productsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
            <thead >
                <th class="th-sm">Purchase Order#
                </th>
                <th class="th-sm">Product
                </th>
                <th class="th-sm">Qty
                </th>
                <th class="th-sm">Rate
                </th>
                <th class="th-sm">Subtotal (Excl)
                </th>
                <th class="th-sm">Tax Amt
                </th>
                <th class="th-sm">Subtotal (Incl)
                </th>
                {{-- <th class="th-sm">Actions
                </th> --}}
              </tr>
            </thead>
            <tbody>
                @foreach ($purchase_products as $purchase_product)
              <tr>
                <td>{{$purchase_product->purchase ? $purchase_product->purchase->purchase_number : ""}}</td>
                <td>{{$purchase_product->product->brand ? $purchase_product->product->brand->name : ""}} {{$purchase_product->product ? $purchase_product->product->name : ""}}</td>
                <td>{{$purchase_product->qty}}</td>
                <td>{{$purchase_product->purchase->currency ? $purchase_product->purchase->currency->symbol : ""}}{{number_format($purchase_product->amount ? $purchase_product->amount : 0,2)}}</td>
                <td>{{$purchase_product->purchase->currency ? $purchase_product->purchase->currency->symbol : ""}}{{number_format($purchase_product->subtotal ? $purchase_product->subtotal : 0,2)}}</td>
                <td>{{$purchase_product->purchase->currency ? $purchase_product->purchase->currency->symbol : ""}}{{number_format($purchase_product->tax_amount ? $purchase_product->tax_amount : 0,2)}}</td>
                <td>{{$purchase_product->purchase->currency ? $purchase_product->purchase->currency->symbol : ""}}{{number_format($purchase_product->subtotal_incl ? $purchase_product->subtotal_incl : 0,2)}}</td>
               
                {{-- <td class="w-10 line-height-35 table-dropdown">
                    <div class="dropdown">
                        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-bars"></i>
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            @if ($purchase_product->purchase->authorization != "approved")
                            <li><a href="#" wire:click="edit({{$purchase_product->id}})"><i class="fa fa-edit color-success"></i> Edit</a></li>
                            @endif
                        </ul>
                    </div>
                    @include('purchase_products.delete')

            </td> --}}
            </tr>
              @endforeach
            </tbody>
          </table>
    {{-- </blockquote> --}}
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="productModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="product">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-plus"></i> Add Product(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="store()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="title">Products<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="selectedProduct.0" class="form-control" required>
                                    <option value="">Select Product</option>
                                    @foreach ($products as $product)
                                    <option value="{{$product->id}}">{{$product->product_number}} {{$product->brand ? $product->brand->name : ""}} {{$product->name}} {{$product->identification_number}}</option>
                                    @endforeach
                                </select>
                                <small>  <a href="{{ route('products.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Product</a></small> 
                                @error('selectedProduct.0') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="Product">Qty<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" min="1" class="form-control" wire:model.debounce.300ms="qty.0"  required>
                                @error('qty.0') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="Product">Rate<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" min="0.01" class="form-control" wire:model.debounce.300ms="amount.0"   required>
                                @error('amount.0') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="subheading">Taxes</label>
                                <select wire:model.debounce.300ms="selectedTax.{{$value}}"  class="form-control">
                                    <option value="">Select Tax</option>
                                        @foreach ($tax_accounts as $tax)
                                           <option value="{{$tax->id}}">{{$tax->abbreviation}} {{$tax->rate ? $tax->rate."%" : ""}}</option> 
                                        @endforeach
                                    </select>
                                    <small><a href="{{ route('taxes.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small> 
                                @error('selectedTax.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                        </div>      
                       
                    </div>

                        @foreach ($inputs as $key => $value)
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="title">Products<span class="required" style="color: red">*</span></label>
                                    <select wire:model.debounce.300ms="selectedProduct.{{$value}}" class="form-control" required>
                                        <option value="">Select Product</option>
                                        @foreach ($products as $product)
                                        <option value="{{$product->id}}">{{$product->product_number}} {{$product->brand ? $product->brand->name : ""}} {{$product->name}} {{$product->identification_number}}</option>
                                        @endforeach
                                    </select>
                                    @error('selectedProduct.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="Product">Qty<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" min="1" class="form-control" wire:model.debounce.300ms="qty.{{$value}}"  required>
                                    @error('qty.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="Product">Rate<span class="required" style="color: red">*</span></label>
                                    <input type="number" step="any" min="0.01" class="form-control" wire:model.debounce.300ms="amount.{{$value}}"   required>
                                    @error('amount.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="subheading">Taxes</label>
                                    <select wire:model.debounce.300ms="selectedTax.{{$value}}"  class="form-control">
                                        <option value="">Select Tax</option>
                                            @foreach ($tax_accounts as $tax)
                                               <option value="{{$tax->id}}">{{$tax->abbreviation}} {{$tax->rate ? $tax->rate."%" : ""}}</option> 
                                            @endforeach
                                        </select>
                                        <small><a href="{{ route('taxes.index') }}" target="_blank"><i class="fa fa-plus-square-o"></i> New Tax</a></small> 
                                    @error('selectedTax.'.$value) <span class="error" style="color:red">{{ $message }}</span> @enderror
                                </div>
                            </div>      
                          
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label for=""></label>
                                    <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                                </div>
                            </div>
                            <!-- /.col-md-6 -->
                        </div>
                        @endforeach
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Product</button>
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

    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="productEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="product">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-edit"></i> Edit Product <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Categories<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="selectedCategory" class="form-control" required>
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $category)
                                    <option value="{{$category->id}}">{{$category->name}}</option>
                                    @endforeach
                                </select>
                                @error('selectedCategory') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Sub Categories</label>
                                <select wire:model.debounce.300ms="selectedCategoryValue" class="form-control">
                                    <option value="">Select Sub Category</option>
                                    @if (!is_null($selectedCategory))
                                        @foreach ($category_values as $category_value)
                                            <option value="{{$category_value->id}}">{{$category_value->name}}</option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('selectedCategoryValue') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                   

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="title">Products<span class="required" style="color: red">*</span></label>
                                <select wire:model.debounce.300ms="product_id" class="form-control" required>
                                    <option value="">Select Product</option>
                                    @foreach ($products as $product)
                                    <option value="{{$product->id}}">{{$product->name}} {{$product->brand ? $product->brand->name : ""}} ({{$product->product_number}})</option>
                                    @endforeach
                                </select>
                                @error('product_id') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="Product">Rate<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" min="0.01" class="form-control" wire:model.debounce.300ms="rate"  placeholder="Enter Price " required>
                                @error('rate') <span class="text-danger error">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="Product">Qty<span class="required" style="color: red">*</span></label>
                                <input type="number" step="any" min="1" class="form-control" wire:model.debounce.300ms="qty"  placeholder="Enter Qty " required>
                                @error('qty') <span class="text-danger error">{{ $message }}</span>@enderror
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
    @section('extra-js')
    <script>
        $(document).ready( function () {
            $('#productsTable').DataTable();
        } );
        </script>
        
    @endsection
</div>
