<div>
    <div class="row mt-30">
        <x-loading/>

        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1">

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab"><strong>Product Details</strong> </a></li>
                <li role="presentation"><a href="#attributes" aria-controls="attributes" role="tab" data-toggle="tab">Product Attributes</a></li>
                <li role="presentation"><a href="#inventory" aria-controls="inventory" role="tab" data-toggle="tab">Inventory</a></li>

            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">
                        <tbody class="text-center line-height-35 ">
                            <tr>
                                <th class="w-10 text-center line-height-35">Product Number</th>
                                <td class="w-20 line-height-35">{{$product->product_number}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Product Image</th>
                                <td class="w-20 line-height-35"> <img src="{{asset('images/uploads/'.$product->filename)}}" alt="" style="height:25%; width:25%;"></td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Category</th>
                                <td class="w-20 line-height-35">{{$product->category ? $product->category->name : ""}} {{$product->category_value ? $product->category_value->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Department</th>
                                <td class="w-20 line-height-35">{{ucfirst($product->department)}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Brand</th>
                                <td class="w-20 line-height-35">{{$product->brand ? $product->brand->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Name</th>
                                <td class="w-20 line-height-35">{{$product->name}}</td>
                            </tr>
                            @if ($product->model)
                            <tr>
                                <th class="w-10 text-center line-height-35">Model</th>
                                <td class="w-20 line-height-35">{{$product->model}}</td>
                            </tr>
                            @endif
                            @if ($product->buy == True)
                            @php
                                $expense_account = App\Models\Account::find($product->expense_account_id);
                            @endphp
                            <tr>
                                <th class="w-10 text-center line-height-35">Buying Price</th>
                                <td class="w-20 line-height-35">{{$product->price}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Expense Account</th>
                                <td class="w-20 line-height-35">{{$expense_account ? $expense_account->name : ""}}</td>
                            </tr>
                            @endif
                            @if ($product->sell == True)
                            @php
                                $income_account = App\Models\Account::find($product->account_id);
                            @endphp
                            <tr>
                                <th class="w-10 text-center line-height-35">Selling Price</th>
                                <td class="w-20 line-height-35">{{$product->sell_price}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Income Account</th>
                                <td class="w-20 line-height-35">{{$income_account ? $income_account->name : ""}}</td>
                            </tr>
                            @endif
                            @php
                            $account = App\Models\Account::find($product->tax_id);
                            @endphp
                            @if (isset($account))
                            <tr>
                                <th class="w-10 text-center line-height-35">Tax</th>
                                <td class="w-20 line-height-35">{{$account->name}} ({{$account->rate ? $account->rate."%" : ""}})</td>
                            </tr>
                            @endif
                           
                            @if ($product->product_attributes->count()>0)
                                <tr>
                                    <th class="w-10 text-center line-height-35">Attributes</th>
                                    <td class="w-20 line-height-35">[
                                        @foreach ($product->product_attributes as $product_attribute)
                                             {{$product_attribute->attribute ? $product_attribute->attribute->name : ""}} - {{$product_attribute->attribute_value ? $product_attribute->attribute_value->name : ""}},
                                        @endforeach]
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <th class="w-10 text-center line-height-35">Manufacturer</th>
                                <td class="w-20 line-height-35">{{$product->manufacturer}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Specifications</th>
                                <td class="w-20 line-height-35">{{$product->description}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Status</th>
                                <td class="w-20 line-height-35"><span class="badge bg-{{$product->status == 1 ? "success" : "danger"}}">{{$product->status == 1 ? "Active" : "Inactive"}}</span></td>
                            </tr>

                        </tbody>
                    </table>
                  
                </div>
               
                <div role="tabpanel" class="tab-pane" id="attributes">
                    @livewire('products.product-attributes', ['id' => $product->id])
                </div>
                <div role="tabpanel" class="tab-pane" id="inventory">
                    @livewire('products.items', ['id' => $product->id,'department' => $product->department])
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="btn-group pull-right mt-10" >
                           <a onclick="goBack()" class="btn bg-gray btn-wide btn-rounded"><i class="fa fa-arrow-left"></i>Back</a>
                            {{-- <button type="submit" wire:click="store({{$inspection->id}})" class="btn bg-success btn-wide btn-rounded" > <i class="fa fa-save"></i>Save</button> --}}
                        </div>
                    </div>
                    </div>



                <!-- /.section-title -->
            </div>
        </div>
        <!-- /.col-md-9 -->
    </div>


</div>
