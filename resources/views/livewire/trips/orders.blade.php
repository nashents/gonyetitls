<div>
    <style>
        .modal-lg {
        max-width: 80%;
    }
    </style>
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
                                    <a href="{{route('trips.create')}}"  class="btn btn-default"><i class="fa fa-plus-square-o"></i>Trip</a>

                                </div>
                                <div class="panel-title" style="float: right">
                                    <a href="{{route('trips.export.excel')}}"  class="btn btn-default"><i class="fa fa-download"></i>Excel</a>
                                    <a href="{{route('trips.export.csv')}}"  class="btn btn-default"><i class="fa fa-download"></i>CSV</a>
                                    <a href="{{route('trips.export.pdf')}}"  class="btn btn-default"><i class="fa fa-download"></i>PDF</a>
                                    {{-- <a href="" data-toggle="modal" data-target="#tripsImportModal" class="btn btn-default"><i class="fa fa-upload"></i>Import</a> --}}
                                </div>
                            </div>
                            <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                                <table id="tripsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%">
                                    <thead >
                                        <th class="th-sm">Trip#
                                        </th>
                                        <th class="th-sm">Customer
                                        </th>
                                        <th class="th-sm">Horse
                                        </th>
                                        <th class="th-sm">Driver
                                        </th>
                                        <th class="th-sm">Type
                                        </th>
                                        <th class="th-sm">From
                                        </th>
                                        <th class="th-sm">To
                                        </th>
                                        <th class="th-sm">Date
                                        </th>
                                        <th class="th-sm">Payment Status
                                        </th>
                                        <th class="th-sm">Trip Status
                                        </th>
                                        <th class="th-sm">Actions
                                        </th>

                                      </tr>
                                    </thead>
                                    @if ($trips->count()>0)
                                    <tbody>
                                        @foreach ($trips as $trip)
                                      <tr>
                                        @php
                                            $from = App\Models\Destination::find($trip->from);
                                            $to = App\Models\Destination::find($trip->to);
                                        @endphp
                                        <td>{{ucfirst($trip->trip_number)}}</td>
                                        <td>{{ucfirst($trip->customer->name)}}</td>
                                        <td>{{ucfirst($trip->horse->horse_make->name)}} {{ucfirst($trip->horse->horse_model->name)}} ({{ucfirst($trip->horse->registration_number)}})</td>
                                         @if ($trip->driver->employee)
                                        {{$trip->driver->employee->name}} {{$trip->driver->employee->surname}}
                                        @else
                                        
                                        @endif
                                        <td>{{$trip->trip_type->name}}</td>
                                        <td>{{$from->country->name}} {{$from->city}}</td>
                                        <td>{{$to->country->name}} {{$to->city}} </td>
                                        <td>{{$trip->start_date}}</td>
                                        @if ($trip->payment_status == "Paid")
                                        <td><span class="label label-success label-wide">{{$trip->payment_status}}</span></td>
                                        @elseif($trip->payment_status == "ToPay")
                                        <td><span class="label label-warning label-wide">{{$trip->payment_status}}</span></td>
                                        @endif
                                        @if ($trip->trip_status == "Completed")
                                        <td><span class="label label-success label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Yet to start")
                                        <td><span class="label label-warning label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Cancelled")
                                        <td><span class="label label-danger label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "On Going")
                                        <td><span class="label label-info label-wide">{{$trip->trip_status}}</span></td>
                                        @endif
                                        <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{route('trips.show', $trip->id)}}"><i class="fa fa-eye color-default"></i>View</a></li>
                                                    <li><a href="{{route('trips.edit', $trip->id)}}"><i class="fa fa-edit color-success"></i> Edit</a></li>
                                                    <li><a href="#" data-toggle="modal" data-target="#tripDeleteModal{{$trip->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>
                                                </ul>
                                            </div>
                                            @include('trips.delete')

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

          <!-- Modal -->


    </div>
