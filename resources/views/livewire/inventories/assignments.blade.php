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
                                <a href="{{route('inventories.create')}}"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Inventory</a>
                            </div>

                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="inventoriesTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Inventory#
                                    </th>
                                    <th class="th-sm">Product
                                    </th>
                                    <th class="th-sm">Horse
                                    </th>
                                    <th class="th-sm">Trailer
                                    </th>
                                    <th class="th-sm">Vehicle
                                    </th>
                                    <th class="th-sm">Purchase Date
                                    </th>

                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($inventories->count()>0)
                                <tbody>
                                    @foreach ($inventories as $inventory)
                                  <tr>
                                    <td>{{$inventory->inventory_number}}</td>
                                    <td>{{$inventory->product ? $inventory->product->name : "undefined"}}</td>
                                    <td>{{$inventory->vendor ? $inventory->vendor->name: "undefined"}}</td>
                                    <td>{{$inventory->qty}}</td>
                                    <td>@foreach ($inventory->inventory_serials as $inventory_serial)
                                        {{$inventory_serial->part_number}},
                                        {{$inventory_serial->serial_number}}
                                    @endforeach</td>
                                    <td>{{$inventory->purchase_date}}</td>
                                    <td><span class="badge bg-{{$inventory->status == 1 ? "success" : "danger"}}">{{$inventory->status == 1 ? "Active" : "Inactive"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('inventories.edit',$inventory->id )}}"  ><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                <li><a href="#" data-toggle="modal" data-target="#inventoryDeleteModal{{ $inventory->id }}" ><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                            </ul>
                                        </div>
                                        @include('inventories.delete')
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

