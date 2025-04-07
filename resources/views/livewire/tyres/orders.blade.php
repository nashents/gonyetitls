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
                                <a href="{{route('tyres.create')}}"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Tyre Order</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="tyresTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Order#
                                    </th>
                                    <th class="th-sm">Date
                                    </th>
                                    <th class="th-sm">Vendor
                                    </th>
                                    <th class="th-sm">Condition
                                    </th>
                                    <th class="th-sm">Currency
                                    </th>
                                    <th class="th-sm">Quantity
                                    </th>
                                    <th class="th-sm">Total
                                    </th>
                                    <th class="th-sm">Description
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($tyres->count()>0)
                                <tbody>
                                    @foreach ($tyres as $tyre)
                                  <tr>
                                    <td>{{$tyre->order_number}}</td>
                                    <td>{{$tyre->date}}</td>
                                    <td>{{$tyre->vendor ? $tyre->vendor->name : "undefined"}}</td>
                                    <td>{{$tyre->tyre_condition}}</td>
                                    <td>{{$tyre->currency ? $tyre->currency->name : "undefined"}}</td>
                                    <td>{{$tyre->qty}}</td>
                                    <td>
                                        @if ($tyre->amount != "")
                                             ${{number_format($tyre->amount,2)}}
                                        @endif
                                    </td>
                                    <td>{{$tyre->description}}</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('tyres.show', $tyre->id)}}"><i class="fa fa-eye color-default"></i>View</a></li>
                                                {{-- <li><a href="{{route('tyres.edit', $tyre->id)}}"><i class="fa fa-edit color-success"></i> Edit</a></li> --}}
                                                <li><a href="#" data-toggle="modal" data-target="#tyre_orderDeleteModal{{ $tyre->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('tyre_orders.delete')
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


</div>

