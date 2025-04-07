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
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="transport_ordersTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Trip#
                                    </th>
                                    <th class="th-sm">Transporter
                                    </th>
                                    <th class="th-sm">Driver
                                    </th>
                                    <th class="th-sm">HRN
                                    </th>
                                    <th class="th-sm">Trailer(s)
                                    </th>
                                    <th class="th-sm">LP
                                    </th>
                                    <th class="th-sm">OP
                                    </th>
                                    <th class="th-sm">Cargo
                                    </th>
                                    <th class="th-sm">CheckedBy
                                    </th>
                                    <th class="th-sm">AuthorizedBy
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($transport_orders->count()>0)
                                <tbody>
                                    @foreach ($transport_orders as $transport_order)
                                  <tr>
                                    <td>{{ $transport_order->trip ? $transport_order->trip->trip_number : ""  }} {{ $transport_order->trip ? '/'.$transport_order->trip->trip_ref : ""  }}</td>
                                    <td>
                                        @if($transport_order->transporter_id)
                                        {{ $transport_order->transporter ? $transport_order->transporter->name : ""}}
                                        @else
                                        
                                        @endif
                                    
                                    </td>
                                    <td>
                                        @if ($transport_order->driver)
                                             {{$transport_order->driver->employee ? $transport_order->driver->employee->name : ""}} {{$transport_order->driver->employee ? $transport_order->driver->employee->surname : ""}}
                                        @endif
                                    </td>
                                    <td>{{$transport_order->horse ? $transport_order->horse->registration_number : ""}}</td>
                                    <td>{{$transport_order->trailer_regnumber}}</td>
                                    <td>{{$transport_order->collection_point}}</td>
                                    <td>{{$transport_order->delivery_point}}</td>
                                    <td>{{$transport_order->cargo}}</td>
                                    <td>{{$transport_order->checked_by}}</td>
                                    <td>{{$transport_order->authorized_by}}</td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('transport_orders.preview',$transport_order->id)}}"   ><i class="fas fa-file-invoice color-primary"></i> Preview</a></li>
                                            </ul>
                                        </div>

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

