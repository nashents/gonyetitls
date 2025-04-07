<div>
    <section class="section">
        <x-loading/>
        <div class="allocation-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div>
                                @include('includes.messages')
                            </div>
                            <div class="panel-title">
                                <a href="" data-toggle="modal" data-target="#allocationModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Allocation</a>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="allocationsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>
                                    <th class="th-sm">Allocation #
                                    </th>
                                    <th class="th-sm">Allocation Type
                                    </th>
                                    <th class="th-sm">Employee
                                    </th>
                                    <th class="th-sm">Vehicle
                                    </th>
                                    <th class="th-sm">Container
                                    </th>
                                    <th class="th-sm">Fuel Type
                                    </th>
                                    <th class="th-sm">Quantity(Litres)
                                    </th>
                                    <th class="th-sm">Rate
                                    </th>
                                    <th class="th-sm">Amount($)
                                    </th>
                                    <th class="th-sm">Balance(Litres)
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($allocations->count()>0)
                                <tbody>
                                    @foreach ($allocations as $allocation)
                                  <tr>
                                    <td>{{$allocation->allocation_number}}</td>
                                    <td>{{$allocation->allocation_type}}</td>
                                    <td>{{$allocation->employee->name}} {{$allocation->employee->surname}} ({{$allocation->employee->employee_number}})</td>
                                    <td>{{$allocation->vehicle->make}} {{$allocation->vehicle->model}} ({{$allocation->vehicle->registration_number}})</td>
                                    <td>{{$allocation->container->name}}</td>
                                    <td>{{$allocation->fuel_type}}</td>
                                    <td>{{$allocation->quantity}}l</td>
                                    <td>${{$allocation->rate}}</td>
                                    <td>${{$allocation->amount}}</td>
                                    <td>{{$allocation->balance}}l</td>
                                    <td><span class="label label-{{$allocation->status == 1 ? "success" : "danger"}} label-rounded">{{$allocation->status == 1 ? "Active" : "Expired"}}</span></td>
                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#"  wire:click="edit({{$allocation->id}})" ><i class="fa fa-refresh color-success"></i> Restore</a></li>
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
        <!-- /.allocation-fluid -->
    </section>





</div>

