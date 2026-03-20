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
                                @if ($department == "tyre")
                                <a href="{{route('tyre_products.create')}}" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Product</a>
                                @elseif ($department == "inventory")
                                <a href="{{route('inventory_products.create')}}" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Product</a>
                                @elseif ($department == "asset")
                                <a href="{{route('products.create')}}" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Product</a>
                                @endif
                                  <a href="" data-toggle="modal" data-target="#importModal" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-upload"></i>Import Products (Pastel)</a>
                                <a href="#" wire:click="exportProductsExcel()"  class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>Excel</a>
                                <a href="#" wire:click="exportProductsCSV()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>CSV</a>
                                <a href="#" wire:click="exportProductsPDF()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-download"></i>PDF</a>
                                <a href="#" wire:click="exportStockValuationExcel()" class="btn btn-default border-primary btn-rounded btn-wide"><i class="fa fa-line-chart"></i>Stock Valution</a>
                            </div>
                        </div>
                       
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search products...">
                                </div>
                            </div>
                            <small style="color: green">
                                Average Lead Time(ALT): is the days difference from purchase order submission to the supplier to the receiving of items in store. <br>
                                Total Value: is the average cost of the items in store across multiple currency converted to the base currency.
                            </small>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Code
                                    </th>
                                    <th class="th-sm">Name
                                    </th>
                                    <th class="th-sm">ID/Part#
                                    </th>
                                    <th class="th-sm">Item(s) in Inventory
                                    </th>
                                    <th class="th-sm">UOM
                                    </th>
                                    <th class="th-sm">Total Value
                                    </th>
                                    <th class="th-sm">ALT(Days)
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($products))
                                <tbody>
                                    @forelse ($products as $product)
                                  <tr>
                                    <td>{{$product->product_number}}</td>
                                    <td>
                                        {{$product->name}} {{$product->model}} {{$product->brand ? "(".$product->brand->name.")" : ""}}
                                        <small>
                                            <br>
                                            <strong>Min Stock Level</strong> {{number_format($product->min ? $product->min : 0)}}
                                            <br>
                                            <strong>Max Stock Level</strong> {{number_format($product->max ? $product->max : 0)}}
                                        </small>
                                    </td>
                                    <td>{{$product->identification_number}}</td>
                                    <td>
                                        @if ($department == "tyre")
                                            {{$product->tyres->where('status',1)->count()}}
                                        @elseif($department == "inventory")
                                            {{$product->inventories->where('status',1)->where('qty','>',0)->where('balance','>',0)->sum('balance')}}
                                        @elseif($department == "asset")
                                            {{$product->assets->where('status',1)->where('qty','>',0)->where('balance','>',0)->sum('balance')}}
                                        @endif
                                    </td>
                                    <td>{{$product->unit_of_measure}}</td>
                                    <td>
                                        @php
                                            $totalValue = $this->calculateTotalValue($product->id);
                                        @endphp

                                        @if ($totalValue)
                                            {{ $base_currency->name }} {{ $base_currency->symbol }}{{ number_format($totalValue, 2) }}
                                        @else
                                            {{ $base_currency->name }} {{ $base_currency->symbol }}0.00
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $days = $this->calculateAvgLeadTime($product->id);
                                        @endphp
                                         {{$days ? $days: ""}}
                                    </td>
                                   
                                    <td><span class="badge bg-{{$product->status == 1 ? "success" : "danger"}}">{{$product->status == 1 ? "Active" : "Inactive"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('products.show',$product->id )}}"  ><i class="fa fa-eye color-default"></i> View</a></li>
                                                @if ($department == "tyre")
                                                <li><a href="{{route('tyre_products.edit',$product->id )}}"  ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                @elseif ($department == "inventory")
                                                <li><a href="{{route('inventory_products.edit',$product->id )}}"  ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                @elseif ($department == "asset")
                                                <li><a href="{{route('products.edit',$product->id )}}"  ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                @endif
                                               
                                                <li><a href="#" data-toggle="modal" data-target="#productDeleteModal{{ $product->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('products.delete')
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="9">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Products Found ....
                                        </div>
                                       
                                    </td>
                                  </tr>  
                                    @endforelse
                                </tbody>
                                @else
                                    <img style="padding-left: 35%; padding-top:7%; width:100% height:100%" src="{{asset('images/nodata.png')}}" alt="">
                                 @endif
                              </table>
                             {{ $products->links() }} 

                            <!-- /.col-md-12 -->
                        </div>
                    </div>
                </div>

            </div>
            <!-- /.row -->

        </div>
        <!-- /.container-fluid -->
    </section>



      <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="importModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fa fa-upload"></i>Import Product(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="importProducts()" method="POST" enctype="multipart/form-data">
                  
                <div class="modal-body">
                   <div class="form-group">
                        <label for="name">Upload Products Excel File<span class="required" style="color: red">*</span></label>
                        <input type="file" class="form-control" wire:model.debounce.300ms="importFile"placeholder="Upload Products File" required>
                        @error('importFile') <span class="error" style="color:red">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                        <button  type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-upload"></i>Upload</button>
                    </div>
                    <!-- /.btn-group -->
                </div>
            </form>
            </div>
        </div>
    </div>


</div>

