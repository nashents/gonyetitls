<div>
    <section class="section">
        <x-loading/>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-10 col-md-offset-1">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">
                                <h5>Create Purchase Order</h5>
                            </div>
                        </div>
                        <div class="panel-body">
                            <form wire:submit.prevent="store()" >

                                    <div class="form-group">
                                        <label for="name">Purchase Order Number</label>
                                        <input type="text" class="form-control" wire:model.debounce.300ms="purchase_number" placeholder="Enter Name" required >
                                        @error('purchase_number') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="name">Date</label>
                                        <input type="date" class="form-control" wire:model.debounce.300ms="date" placeholder="Enter Purchase Order Date" required >
                                        @error('date') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="name">Quantity</label>
                                        <input type="text" class="form-control" wire:model.debounce.300ms="quantity" placeholder="Enter Products Quantity" required >
                                        @error('quantity') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="name">Value</label>
                                        <input type="text" class="form-control" wire:model.debounce.300ms="value" placeholder="Enter Name" required >
                                        @error('value') <span class="error" style="color:red">{{ $message }}</span> @enderror
                                    </div>
                                    <h5 class="underline mt-n">Purchase Order Products</h5>

                                    <div class="form-group">
                                        <label for="title">Categories</label>
                                        <select wire:model.debounce.300ms="selectedCategory" class="form-control">
                                            <option value="">Select Category</option>
                                            @foreach ($categories as $product)
                                            <option value="{{$category->id}}">{{$category->name}}</option>
                                            @endforeach
                                        </select>
                                        @error('selectedCategory') <span class="text-danger error">{{ $message }}</span>@enderror
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="title">Products</label>
                                                <select wire:model.debounce.300ms="product_id.0" class="form-control">
                                                    <option value="">Select Product</option>
                                                    @foreach ($products as $product)
                                                    <option value="{{$product->id}}">{{$product->name}} ({{$product->product_number}})</option>
                                                    @endforeach
                                                </select>
                                                @error('product_id.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="Product">Rate</label>
                                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="rate.0"  placeholder="Enter Product Price ">
                                                @error('rate.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="Product">Quantity</label>
                                                <input type="number" step="any" class="form-control" wire:model.debounce.300ms="qty.0"  placeholder="Enter Prodcut Qty ">
                                                @error('qty.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                            </div>
                                        </div>
                                    </div>

                                        @foreach ($inputs as $key => $value)
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="title">Products</label>
                                                    <select wire:model.debounce.300ms="product_id.{{$value}}" class="form-control">
                                                        <option value="">Select Product</option>
                                                        @foreach ($products as $product)
                                                        <option value="{{$product->id}}">{{$product->name}} ({{$product->product_number}})</option>
                                                        @endforeach
                                                    </select>
                                                    @error('product_id.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="Product">Rate</label>
                                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="rate.{{$value}}"  placeholder="Enter Product Price ">
                                                    @error('rate.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label for="Product">Quantity</label>
                                                    <input type="number" step="any" class="form-control" wire:model.debounce.300ms="qty.{{$value}}"  placeholder="Enter Prodcut Qty ">
                                                    @error('qty.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
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

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="btn-group pull-right mt-10" >
                                           <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                                            <button type="submit" class="btn bg-success btn-wide btn-rounded" > <i class="fa fa-save"></i>Create</button>
                                        </div>
                                    </div>
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


</div>
