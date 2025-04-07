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
                                <a href="{{route('sales.create')}}"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Sale</a>
                            </div>
                            
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">
                            <div class="panel-title">
                                <div class="row">
                                
                                <div class="col-lg-3">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                        Filter By
                                        </span>
                                        <select wire:model.debounce.300ms="sale_filter" class="form-control" aria-label="..." >
                                            <option value="created_at">Sale Created At</option>
                                            <option value="date">Sale Date</option>
                                        </select>
                                    </div>
                                    <!-- /input-group -->
                                </div>
                             
                                <div class="col-lg-2" style="margin-right: 7px">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                  From
                                  </span>
                                  <input type="date" wire:model.debounce.300ms="from"  class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>
                                <div class="col-lg-2" style="margin-left: 7px">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                  To
                                  </span>
                                  <input type="date" wire:model.debounce.300ms="to"  class="form-control" aria-label="...">
                                    </div>
                                    <!-- /input-group -->
                                </div>

                            </div>
                            
                            </div>
                            
                            <div class="col-md-3" style="float: right; padding-right:0px; ">
                                <div class="form-group">
                                    <input type="text" wire:model.debounce.300ms="search" class="form-control" placeholder="Search sales...">
                                </div>
                            </div>
                            <table  class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Sale#
                                    </th>
                                    <th class="th-sm">Customer
                                    </th>
                                    <th class="th-sm">Date
                                    </th>
                                    <th class="th-sm">Item(s)
                                    </th>
                                    <th class="th-sm">Status
                                    </th>  
                                    <th class="th-sm">Currency
                                    </th>
                                    <th class="th-sm">Total
                                    </th>
                                    <th class="th-sm">Paid
                                    </th>
                                    <th class="th-sm">Due
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if (isset($sales))
                                <tbody>
                                    @forelse ($sales as $sale)
                                  <tr>
                                    <td>{{$sale->sale_number}} </td>
                                    <td>
                                        {{$sale->customer ? $sale->customer->name : ""}}
                                    </td>
                                    <td>{{$sale->date}}</td>
                                    <td>
                                        @foreach ($sale->sale_items as $item)
                                            @if ($item->inventory)
                                                {{$item->inventory->product ? $item->inventory->product->name : ""}} {{$item->inventory->product ? $item->inventory->product->description : ""}} <br>       
                                            @endif
                                        @endforeach
                                    </td>
                                    <td><span class="label label-{{($sale->status == 'Paid') ? 'success' : (($sale->status == 'Partial') ? 'warning' : 'danger') }}">{{ $sale->status }}</span></td>
                                    <td>
                                        {{$sale->currency ? $sale->currency->name : ""}}
                                    </td>
                                    <td>
                                        @if ($sale->total)
                                        {{$sale->currency ? $sale->currency->symbol : ""}}{{number_format($sale->total,2)}}
                                        @endif
                                    </td>
                                    <td>
                                        @if (isset($sale->payments))
                                             {{$sale->currency ? $sale->currency->symbol : ""}}{{number_format($sale->payments->sum('amount'),2)}}
                                        @else
                                             {{$sale->currency ? $sale->currency->symbol : ""}}{{number_format($sale->sale_payments->sum('amount'),2)}}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($sale->balance)
                                        {{$sale->currency ? $sale->currency->symbol : ""}}{{number_format($sale->balance,2)}}
                                        @else
                                        {{$sale->currency ? $sale->currency->symbol : ""}}{{number_format(0,2)}}
                                        @endif
                                    </td>  
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                {{-- <li><a href="{{route('sales.show',$sale->id)}}"  ><i class="fas fa-eye color-default"></i> View</a></li> --}}
                                                @php
                                                    $receipt = App\Models\Receipt::where('sale_id',$sale->id)->first()
                                                @endphp
                                                @if (isset($receipt))
                                                    <li><a href="{{route('receipts.preview',$receipt->id)}}"  ><i class="fas fa-file-sale color-primary"></i> Receipt</a></li>
                                                @endif
                                                <li><a href="#" data-toggle="modal" data-target="#saleDeleteModal{{ $sale->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('sales.delete')
                                </td>
                                  </tr>
                                  @empty
                                  <tr>
                                    <td colspan="10">
                                        <div style="text-align:center; text-color:grey; padding-top:5px; padding-bottom:5px; font-size:17px">
                                            No Sales Found ....
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
                                    @if (isset($sales))
                                        {{ $sales->links() }} 
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

