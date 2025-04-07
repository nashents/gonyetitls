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
                                {{-- <a href="" data-toggle="modal" data-target="#decisionModal" class="btn btn-default"><i class="fa fa-plus-square-o"></i>Fuel Request</a> --}}

                            </div>

                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="fuel_requestsTable" class="table table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                <thead>
                                  <tr>

                                    <th class="th-sm">Request #
                                    </th>
                                    <th class="th-sm">Type
                                    </th>
                                    <th class="th-sm">Emp #
                                    </th>
                                    <th class="th-sm">Employee
                                    </th>
                                    <th class="th-sm">Fuel Type
                                    </th>
                                    <th class="th-sm">Quantity
                                    </th>
                                    <th class="th-sm">Date
                                    </th>
                                    <th class="th-sm">Status
                                    </th>
                                    <th class="th-sm">Action
                                    </th>
                                  </tr>
                                </thead>
                                @if ($fuel_requests->count()>0)
                                <tbody>
                                    @foreach ($fuel_requests as $fuel_request)
                                  <tr>
                                    <td>{{ucfirst($fuel_request->request_number)}}</td>
                                    <td>{{ucfirst($fuel_request->request_type)}}</td>
                                    <td>{{ucfirst($fuel_request->employee->employee_number)}}</td>
                                    <td>{{ucfirst($fuel_request->employee->name)}} {{ucfirst($fuel_request->employee->surname)}}</td>
                                    <td>{{$fuel_request->fuel_type}}</td>
                                    <td>{{$fuel_request->quantity}}</td>
                                    <td>{{$fuel_request->date}}</td>
                                    @if ($fuel_request->status == "pending")
                                    <td><span class="label label-warning label-rounded">{{$fuel_request->status}}</span></td>
                                    @elseif($fuel_request->status == "approved")
                                    <td><span class="label label-success label-rounded">{{$fuel_request->status}}</span></td>
                                    @elseif($fuel_request->status == "rejected")
                                    <td><span class="label label-danger label-rounded">{{$fuel_request->status}}</span></td>
                                    @endif

                                    <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#"  wire:click="decision({{$fuel_request->id}})" ><i class="fa fa-edit color-success"></i>Approve / Reject</a></li>
                                            </ul>
                                        </div>
                                        @include('fuel_requests.delete')
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

