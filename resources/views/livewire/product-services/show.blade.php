<div>
    <div class="row mt-30">
        <x-loading/>

        <!-- /.col-md-3 -->

        <div class="col-md-10 col-md-offset-1">

            <ul class="nav nav-tabs nav-justified" role="tablist">
                <li role="presentation" class="active"><a href="#basic" aria-controls="basic" role="tab" data-toggle="tab"><strong>Product Details</strong> </a></li>
            </ul>
            <div class="tab-content bg-white p-15">
                <div role="tabpanel" class="tab-pane active" id="basic">
                    <table class="table table-striped">
                        <tbody class="text-center line-height-35 ">
                            <tr>
                                <th class="w-10 text-center line-height-35">Name</th>
                                <td class="w-20 line-height-35">{{$product->name}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Description</th>
                                <td class="w-20 line-height-35">{{$product->description}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Buy </th>
                                <td class="w-20 line-height-35">{{$product->category_value ? $product->category_value->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Brand</th>
                                <td class="w-20 line-height-35">{{$product->brand ? $product->brand->name : ""}}</td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Division</th>
                                <td class="w-20 line-height-35">{{ucfirst($product->department)}} </td>
                            </tr>
                            <tr>
                                <th class="w-10 text-center line-height-35">Name</th>
                                <td class="w-20 line-height-35">{{$product->name}}</td>
                            </tr>
                            @if ($product->department == "asset")
                            <tr>
                                <th class="w-10 text-center line-height-35">Model</th>
                                <td class="w-20 line-height-35">{{$product->model}}</td>
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
