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

                            <table id="inventory_dispatchesTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Inventory#
                                    </th>
                                    <th class="th-sm">Product
                                    </th>
                                    <th class="th-sm">Date
                                    </th>
                                    <th class="th-sm">Part#
                                    </th>
                                    <th class="th-sm">Serial#
                                    </th>
                                    <th class="th-sm">Ticket#
                                    </th>
                                    <th class="th-sm">Dispatched To
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($inventory_dispatches->count()>0)
                                <tbody>
                                    @foreach ($inventory_dispatches as $inventory_dispatch)
                                  <tr>
                                    <td>{{$inventory_dispatch->inventory ? $inventory_dispatch->inventory->inventory_number : ""}}</td>
                                    <td>{{$inventory_dispatch->inventory->product->brand ? $inventory_dispatch->inventory->product->brand->name : ""}} {{$inventory_dispatch->inventory->product ? $inventory_dispatch->inventory->product->name : ""}}</td>
                                    <td>{{$inventory_dispatch->issue_date}}</td>
                                    <td>{{$inventory_dispatch->inventory ? $inventory_dispatch->inventory->part_number : ""}}</td>
                                    <td>{{$inventory_dispatch->inventory ? $inventory_dispatch->inventory->serial_number : ""}}</td>
                                    <td>{{$inventory_dispatch->ticket ? $inventory_dispatch->ticket->ticket_number : ""}}</td>
                                    <td>
                                        @if ($inventory_dispatch->horse)
                                          Horse | {{$inventory_dispatch->horse->horse_make ? $inventory_dispatch->horse->horse_make->name : ""}} {{$inventory_dispatch->horse->horse_model ? $inventory_dispatch->horse->horse_model->name : ""}} {{$inventory_dispatch->horse->registration_number}}
                                        @elseif ($inventory_dispatch->vehicle)
                                          Vehicle | {{$inventory_dispatch->vehicle->vehicle_make ? $inventory_dispatch->vehicle->vehicle_make->name : ""}} {{$inventory_dispatch->vehicle->vehicle_model ? $inventory_dispatch->vehicle->vehicle_model->name : ""}} {{$inventory_dispatch->vehicle->registration_number}}
                                        @elseif ($inventory_dispatch->trailer)
                                          Trailer | {{$inventory_dispatch->trailer->trailer_make}} {{$inventory_dispatch->trailer->trailer_model}} {{$inventory_dispatch->trailer->registration_number}}
                                        @endif
                                    </td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('inventory_dispatches.show',$inventory_dispatch->id )}}"  ><i class="fa fa-eye color-default"></i> View</a></li>
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

