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
                              
                            </div>

                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="col-md-3" style="float: right; padding-right:0px">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search products...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Product#
                                    </th>
                                    <th class="th-sm">Name
                                    </th>
                                    <th class="th-sm">Identification/Part#
                                    </th>
                                    <th class="th-sm">Item(s) in Inventory
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
                                    <td>{{$product->name}} {{$product->model}} {{$product->brand ? "(".$product->brand->name.")" : ""}}</td>
                                    <td>{{$product->identification_number}}</td>
                                    <td>
                                        @if ($department == "tyre")
                                            {{$product->tyres->where('status',1)->count()}}
                                        @elseif($department == "inventory")
                                            {{$product->inventories->where('status',1)->where('balance','>',0)->count()}}
                                        @elseif($department == "asset")
                                            {{$product->assets->where('status',1)->where('balance','>',0)->count()}}
                                        @endif
                                        
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
                                    <td colspan="6">
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





</div>

