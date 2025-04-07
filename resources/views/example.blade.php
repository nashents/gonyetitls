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
                                <div class="panel-title" style="float: right">
                                    <a href="{{route('trips.export.excel')}}"  class="btn btn-default"><i class="fa fa-download"></i>Excel</a>
                                    <a href="{{route('trips.export.csv')}}"  class="btn btn-default"><i class="fa fa-download"></i>CSV</a>
                                    <a href="{{route('trips.export.pdf')}}"  class="btn btn-default"><i class="fa fa-download"></i>PDF</a>
                                    {{-- <a href="" data-toggle="modal" data-target="#tripsImportModal" class="btn btn-default"><i class="fa fa-upload"></i>Import</a> --}}
                                </div>
                            </div>
                            <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                                <table id="tripsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%" style=" width:100%; height:100%;">
                                    <thead >
                                        <th class="th-sm">Trip Number
                                        </th>
                                        <th class="th-sm">Created By
                                        </th>
                                        <th class="th-sm">Start Date
                                        </th>
                                        <th class="th-sm">Customer
                                        </th>
                                        <th class="th-sm">Horse
                                        </th>
                                        <th class="th-sm">Trailer(s)
                                        </th>
                                        <th class="th-sm">Driver
                                        </th>
                                        <th class="th-sm">Loading Point
                                        </th>
                                        <th class="th-sm">Offloading Point
                                        </th>
                                        <th class="th-sm">Payment Status
                                        </th>
                                        <th class="th-sm">Trip Status
                                        </th>
                                        <th class="th-sm">Authorization
                                        </th>
                                        <th class="th-sm">FOS
                                        </th>
                                        <th class="th-sm">PODS
                                        </th>
                                        <th class="th-sm">Actions
                                        </th>

                                      </tr>
                                    </thead>
                                    @if ($trips->count()>0)
                                    <tbody>
                                        @foreach ($trips as $trip)
                                        @if ($trip->trip_status == "Offloaded")
                                      <tr style="background-color: #4CAF50">
                                        @php
                                            $from = App\Models\Destination::find($trip->from);
                                            $to = App\Models\Destination::find($trip->to);
                                            $date = Carbon\Carbon::parse($trip->start_date);
                                        @endphp
                                        <td>{{ucfirst($trip->trip_number)}}</td>
                                         <td>{{ucfirst($trip->user ? $trip->user->name : "")}} {{ucfirst($trip->user ? $trip->user->surname : "")}}</td>
                                        <td>{{$date->format("d/m/Y")}}</td>
                                        <td>{{ucfirst($trip->customer ? $trip->customer->name : "undefined")}}</td>
                                        @if ($trip->horse)
                                        <td>{{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "undefined")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "undefined")}} ({{ucfirst($trip->horse ? $trip->horse->registration_number : "undefined")}})</td>
                                        @else
                                            <td>undefined</td>
                                        @endif

                                        <td>[
                                            @foreach ($trip->trailers as $trailer)
                                                {{$trailer->make}} {{$trailer->model}} ({{$trailer->registration_number}}),
                                            @endforeach
                                            ]
                                        </td>
                                        <td>
                                            @if ($trip->driver->employee)
                                             {{$trip->driver->employee->name}} {{$trip->driver->employee->surname}}
                                            @else
                                            undefined
                                            @endif
                                             </td>
                                        <td>{{$trip->loading_point ? $trip->loading_point->name : "undefined"}}</td>
                                        <td>{{$trip->offloading_point ? $trip->offloading_point->name : "undefined"}}</td>
                                        @if ($trip->payment_status == "Paid")
                                        <td><span class="label label-success label-wide">{{$trip->payment_status}}</span></td>
                                        @elseif($trip->payment_status == "ToPay")
                                        <td><span class="label label-warning label-wide">{{$trip->payment_status}}</span></td>
                                        @elseif($trip->payment_status == "Free")
                                        <td><span class="label label-info label-wide">{{$trip->payment_status}}</span></td>
                                        @endif
                                        @if ($trip->trip_status == "Offloaded")
                                        <td ><span class="label label-success label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Scheduled")
                                        <td class="table-warning" ><span class="label label-warning label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Loaded")
                                        <td class="table-info"><span class="label label-info label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "InTransit")
                                        <td class="table-secondary"><span class="label label-accent label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "OnHold")
                                        <td class="table-danger"><span class="label label-danger label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Offloading Point")
                                        <td class="table-primary"><span class="label label-primary label-wide">{{$trip->trip_status}}</span></td>
                                        @endif
                                        <td><span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                        <td> <span class="label label-{{$trip->fuel ? 'success' : 'warning'}}">{{$trip->fuel ? "submitted" : "pending"}}</span></td>
                                         <td> <span class="label label-{{isset($trip->delivery_note->offloaded) ? "success" : "warning"}}"> {{isset($trip->delivery_note->offloaded) ? "submitted" : "pending"}}</span></td>
                                        <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i>View</a></li>
                                                    <li><a href="{{route('trips.trip_sheet', $trip->id)}}"><i class="fas fa-list color-default"></i>Trip Sheet</a></li>
                                                    <li><a href="{{route('invoices.create', $trip->id)}}"><i class="fas fa-list color-default"></i>Generate Invoice</a></li>
                                                    <li><a href="{{route('trips.edit', $trip->id)}}"><i class="fas fa-edit color-success"></i> Edit</a></li>
                                                    <li><a href="#" data-toggle="modal" data-target="#tripDeleteModal{{$trip->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>

                                                </ul>
                                            </div>
                                            @include('trips.delete')

                                    </td>
                                      </tr>
                                      @elseif($trip->trip_status == "Scheduled")
                                      <tr style="background-color: #FFC107" >
                                        @php
                                            $from = App\Models\Destination::find($trip->from);
                                            $to = App\Models\Destination::find($trip->to);
                                            $date = Carbon\Carbon::parse($trip->start_date);
                                        @endphp
                                        <td>{{ucfirst($trip->trip_number)}}</td>
                                         <td>{{ucfirst($trip->user ? $trip->user->name : "")}} {{ucfirst($trip->user ? $trip->user->surname : "")}}</td>
                                        <td>{{$date->format("d/m/Y")}}</td>
                                        <td>{{ucfirst($trip->customer ? $trip->customer->name : "undefined")}}</td>
                                        @if ($trip->horse)
                                        <td>{{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "undefined")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "undefined")}} ({{ucfirst($trip->horse ? $trip->horse->registration_number : "undefined")}})</td>
                                        @else
                                            <td>undefined</td>
                                        @endif

                                        <td>[
                                            @foreach ($trip->trailers as $trailer)
                                                {{$trailer->make}} {{$trailer->model}} ({{$trailer->registration_number}}),
                                            @endforeach
                                            ]
                                        </td>

                                        <td>
                                            @if ($trip->driver->employee)
                                            {{$trip->driver->employee->name}} {{$trip->driver->employee->surname}}
                                            @else
                                                undefined
                                            @endif
                                        </td>
                                        <td>{{$trip->loading_point ? $trip->loading_point->name : "undefined"}}</td>
                                        <td>{{$trip->offloading_point ? $trip->offloading_point->name : "undefined"}}</td>
                                        @if ($trip->payment_status == "Paid")
                                        <td><span class="label label-success label-wide">{{$trip->payment_status}}</span></td>
                                        @elseif($trip->payment_status == "ToPay")
                                        <td><span class="label label-warning label-wide">{{$trip->payment_status}}</span></td>
                                        @elseif($trip->payment_status == "Free")
                                        <td><span class="label label-info label-wide">{{$trip->payment_status}}</span></td>
                                        @endif
                                        @if ($trip->trip_status == "Offloaded")
                                        <td ><span class="label label-success label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Scheduled")
                                        <td class="table-warning" ><span class="label label-warning label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Loaded")
                                        <td class="table-info"><span class="label label-info label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "InTransit")
                                        <td class="table-secondary"><span class="label label-accent label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "OnHold")
                                        <td class="table-danger"><span class="label label-danger label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Offloading Point")
                                        <td class="table-primary"><span class="label label-primary label-wide">{{$trip->trip_status}}</span></td>
                                        @endif
                                        <td><span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                         <td> <span class="label label-{{$trip->fuel ? 'success' : 'warning'}}">{{$trip->fuel ? "submitted" : "pending"}}</span></td>
                                         <td> <span class="label label-{{isset($trip->delivery_note->offloaded) ? "success" : "warning"}}"> {{isset($trip->delivery_note->offloaded) ? "submitted" : "pending"}}</span></td>
                                        <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i>View</a></li>
                                                    <li><a href="{{route('trips.edit', $trip->id)}}"><i class="fas fa-edit color-success"></i> Edit</a></li>
                                                    <li><a href="#" data-toggle="modal" data-target="#tripDeleteModal{{$trip->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>

                                                </ul>
                                            </div>
                                            @include('trips.delete')

                                    </td>
                                      </tr>
                                      @elseif($trip->trip_status == "Loaded")
                                      <tr  style="background-color: #2196F3" >
                                        @php
                                            $from = App\Models\Destination::find($trip->from);
                                            $to = App\Models\Destination::find($trip->to);
                                            $date = Carbon\Carbon::parse($trip->start_date);
                                        @endphp
                                        <td>{{ucfirst($trip->trip_number)}}</td>
                                         <td>{{ucfirst($trip->user ? $trip->user->name : "")}} {{ucfirst($trip->user ? $trip->user->surname : "")}}</td>
                                        <td>{{$date->format("d/m/Y")}}</td>
                                        <td>{{ucfirst($trip->customer ? $trip->customer->name : "undefined")}}</td>
                                        @if ($trip->horse)
                                        <td>{{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "undefined")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "undefined")}} ({{ucfirst($trip->horse ? $trip->horse->registration_number : "undefined")}})</td>
                                        @else
                                            <td>undefined</td>
                                        @endif

                                        <td>[
                                            @foreach ($trip->trailers as $trailer)
                                                {{$trailer->make}} {{$trailer->model}} ({{$trailer->registration_number}}),
                                            @endforeach
                                            ]
                                        </td>
                                        <td>
                                            @if ($trip->driver->employee)
                                            {{$trip->driver->employee->name}} {{$trip->driver->employee->surname}}
                                            @else
                                                undefined
                                            @endif
                                        </td>
                                        <td>{{$trip->loading_point ? $trip->loading_point->name : "undefined"}}</td>
                                        <td>{{$trip->offloading_point ? $trip->offloading_point->name : "undefined"}}</td>
                                        @if ($trip->payment_status == "Paid")
                                        <td><span class="label label-success label-wide">{{$trip->payment_status}}</span></td>
                                        @elseif($trip->payment_status == "ToPay")
                                        <td><span class="label label-warning label-wide">{{$trip->payment_status}}</span></td>
                                        @elseif($trip->payment_status == "Free")
                                        <td><span class="label label-info label-wide">{{$trip->payment_status}}</span></td>
                                        @endif
                                        @if ($trip->trip_status == "Offloaded")
                                        <td ><span class="label label-success label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Scheduled")
                                        <td class="table-warning" ><span class="label label-warning label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Loaded")
                                        <td class="table-info"><span class="label label-info label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "InTransit")
                                        <td class="table-secondary"><span class="label label-accent label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "OnHold")
                                        <td class="table-danger"><span class="label label-danger label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Offloading Point")
                                        <td class="table-primary"><span class="label label-primary label-wide">{{$trip->trip_status}}</span></td>
                                        @endif
                                        <td><span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                         <td> <span class="label label-{{$trip->fuel ? 'success' : 'warning'}}">{{$trip->fuel ? "submitted" : "pending"}}</span></td>
                                         <td> <span class="label label-{{isset($trip->delivery_note->offloaded) ? "success" : "warning"}}"> {{isset($trip->delivery_note->offloaded) ? "submitted" : "pending"}}</span></td>
                                        <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i>View</a></li>
                                                    <li><a href="{{route('trips.edit', $trip->id)}}"><i class="fas fa-edit color-success"></i> Edit</a></li>
                                                    <li><a href="#" data-toggle="modal" data-target="#tripDeleteModal{{$trip->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>

                                                </ul>
                                            </div>
                                            @include('trips.delete')

                                    </td>
                                      </tr>
                                      @elseif($trip->trip_status == "InTransit")
                                      <tr  style="background-color: #82B1FF">
                                        @php
                                            $from = App\Models\Destination::find($trip->from);
                                            $to = App\Models\Destination::find($trip->to);
                                            $date = Carbon\Carbon::parse($trip->start_date);
                                        @endphp
                                        <td>{{ucfirst($trip->trip_number)}}</td>
                                         <td>{{ucfirst($trip->user ? $trip->user->name : "")}} {{ucfirst($trip->user ? $trip->user->surname : "")}}</td>
                                        <td>{{$date->format("d/m/Y")}}</td>
                                        <td>{{ucfirst($trip->customer ? $trip->customer->name : "undefined")}}</td>
                                        @if ($trip->horse)
                                        <td>{{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "undefined")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "undefined")}} ({{ucfirst($trip->horse ? $trip->horse->registration_number : "undefined")}})</td>
                                        @else
                                            <td>undefined</td>
                                        @endif

                                        <td>[
                                            @foreach ($trip->trailers as $trailer)
                                                {{$trailer->make}} {{$trailer->model}} ({{$trailer->registration_number}}),
                                            @endforeach
                                            ]
                                        </td>
                                        <td>
                                            @if ($trip->driver->employee)
                                            {{$trip->driver->employee->name}} {{$trip->driver->employee->surname}}
                                            @else
                                                undefined
                                            @endif
                                        </td>
                                        <td>{{$trip->loading_point ? $trip->loading_point->name : "undefined"}}</td>
                                        <td>{{$trip->offloading_point ? $trip->offloading_point->name : "undefined"}}</td>
                                        @if ($trip->payment_status == "Paid")
                                        <td><span class="label label-success label-wide">{{$trip->payment_status}}</span></td>
                                        @elseif($trip->payment_status == "ToPay")
                                        <td><span class="label label-warning label-wide">{{$trip->payment_status}}</span></td>
                                        @elseif($trip->payment_status == "Free")
                                        <td><span class="label label-info label-wide">{{$trip->payment_status}}</span></td>
                                        @endif
                                        @if ($trip->trip_status == "Offloaded")
                                        <td ><span class="label label-success label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Scheduled")
                                        <td class="table-warning" ><span class="label label-warning label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Loaded")
                                        <td class="table-info"><span class="label label-info label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "InTransit")
                                        <td class="table-secondary"><span class="label label-accent label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "OnHold")
                                        <td class="table-danger"><span class="label label-danger label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Offloading Point")
                                        <td class="table-primary"><span class="label label-primary label-wide">{{$trip->trip_status}}</span></td>
                                        @endif
                                        <td><span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                         <td> <span class="label label-{{$trip->fuel ? 'success' : 'warning'}}">{{$trip->fuel ? "submitted" : "pending"}}</span></td>
                                         <td> <span class="label label-{{isset($trip->delivery_note->offloaded) ? "success" : "warning"}}"> {{isset($trip->delivery_note->offloaded) ? "submitted" : "pending"}}</span></td>
                                        <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i>View</a></li>
                                                    <li><a href="{{route('trips.edit', $trip->id)}}"><i class="fas fa-edit color-success"></i> Edit</a></li>
                                                    <li><a href="#" data-toggle="modal" data-target="#tripDeleteModal{{$trip->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>

                                                </ul>
                                            </div>
                                            @include('trips.delete')

                                    </td>
                                      </tr>
                                      @elseif($trip->trip_status == "OnHold")
                                      <tr  style="background-color: #FF5252">
                                        @php
                                            $from = App\Models\Destination::find($trip->from);
                                            $to = App\Models\Destination::find($trip->to);
                                            $date = Carbon\Carbon::parse($trip->start_date);
                                        @endphp
                                        <td>{{ucfirst($trip->trip_number)}}</td>
                                         <td>{{ucfirst($trip->user ? $trip->user->name : "")}} {{ucfirst($trip->user ? $trip->user->surname : "")}}</td>
                                        <td>{{$date->format("d/m/Y")}}</td>
                                        <td>{{ucfirst($trip->customer ? $trip->customer->name : "undefined")}}</td>
                                        @if ($trip->horse)
                                        <td>{{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "undefined")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "undefined")}} ({{ucfirst($trip->horse ? $trip->horse->registration_number : "undefined")}})</td>
                                        @else
                                            <td>undefined</td>
                                        @endif

                                        <td>[
                                            @foreach ($trip->trailers as $trailer)
                                                {{$trailer->make}} {{$trailer->model}} ({{$trailer->registration_number}}),
                                            @endforeach
                                            ]
                                        </td>
                                        <td>
                                            @if ($trip->driver->employee)
                                            {{$trip->driver->employee->name}} {{$trip->driver->employee->surname}}
                                            @else
                                                undefined
                                            @endif
                                        </td>
                                        <td>{{$trip->loading_point ? $trip->loading_point->name : "undefined"}}</td>
                                        <td>{{$trip->offloading_point ? $trip->offloading_point->name : "undefined"}}</td>
                                        @if ($trip->payment_status == "Paid")
                                        <td><span class="label label-success label-wide">{{$trip->payment_status}}</span></td>
                                        @elseif($trip->payment_status == "ToPay")
                                        <td><span class="label label-warning label-wide">{{$trip->payment_status}}</span></td>
                                        @elseif($trip->payment_status == "Free")
                                        <td><span class="label label-info label-wide">{{$trip->payment_status}}</span></td>
                                        @endif
                                        @if ($trip->trip_status == "Offloaded")
                                        <td ><span class="label label-success label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Scheduled")
                                        <td class="table-warning" ><span class="label label-warning label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Loaded")
                                        <td class="table-info"><span class="label label-info label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "InTransit")
                                        <td class="table-secondary"><span class="label label-accent label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "OnHold")
                                        <td class="table-danger"><span class="label label-danger label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Offloading Point")
                                        <td class="table-primary"><span class="label label-primary label-wide">{{$trip->trip_status}}</span></td>
                                        @endif
                                        <td><span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                         <td> <span class="label label-{{$trip->fuel ? 'success' : 'warning'}}">{{$trip->fuel ? "submitted" : "pending"}}</span></td>
                                         <td> <span class="label label-{{isset($trip->delivery_note->offloaded) ? "success" : "warning"}}"> {{isset($trip->delivery_note->offloaded) ? "submitted" : "pending"}}</span></td>
                                        <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i>View</a></li>
                                                    <li><a href="{{route('trips.edit', $trip->id)}}"><i class="fas fa-edit color-success"></i> Edit</a></li>
                                                    <li><a href="#" data-toggle="modal" data-target="#tripDeleteModal{{$trip->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>

                                                </ul>
                                            </div>
                                            @include('trips.delete')

                                    </td>
                                      </tr>
                                      @elseif($trip->trip_status == "Offloading Point")
                                      <tr  style="background-color: #1976D2">
                                        @php
                                            $from = App\Models\Destination::find($trip->from);
                                            $to = App\Models\Destination::find($trip->to);
                                            $date = Carbon\Carbon::parse($trip->start_date);
                                        @endphp
                                        <td>{{ucfirst($trip->trip_number)}}</td>
                                         <td>{{ucfirst($trip->user ? $trip->user->name : "")}} {{ucfirst($trip->user ? $trip->user->surname : "")}}</td>
                                        <td>{{$date->format("d/m/Y")}}</td>
                                        <td>{{ucfirst($trip->customer ? $trip->customer->name : "undefined")}}</td>
                                       @if ($trip->horse)
                                       <td>{{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "undefined")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "undefined")}} ({{ucfirst($trip->horse ? $trip->horse->registration_number : "undefined")}})</td>
                                       @else
                                           <td>undefined</td>
                                       @endif

                                        <td>[
                                            @foreach ($trip->trailers as $trailer)
                                                {{$trailer->make}} {{$trailer->model}} ({{$trailer->registration_number}}),
                                            @endforeach
                                            ]
                                        </td>
                                        <td>
                                            @if ($trip->driver->employee)
                                            {{$trip->driver->employee->name}} {{$trip->driver->employee->surname}}
                                            @else
                                                undefined
                                            @endif
                                        </td>
                                        <td>{{$trip->loading_point ? $trip->loading_point->name : "undefined"}}</td>
                                        <td>{{$trip->offloading_point ? $trip->offloading_point->name : "undefined"}}</td>
                                        @if ($trip->payment_status == "Paid")
                                        <td><span class="label label-success label-wide">{{$trip->payment_status}}</span></td>
                                        @elseif($trip->payment_status == "ToPay")
                                        <td><span class="label label-warning label-wide">{{$trip->payment_status}}</span></td>
                                        @elseif($trip->payment_status == "Free")
                                        <td><span class="label label-info label-wide">{{$trip->payment_status}}</span></td>
                                        @endif
                                        @if ($trip->trip_status == "Offloaded")
                                        <td ><span class="label label-success label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Scheduled")
                                        <td class="table-warning" ><span class="label label-warning label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Loaded")
                                        <td class="table-info"><span class="label label-info label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "InTransit")
                                        <td class="table-secondary"><span class="label label-accent label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "OnHold")
                                        <td class="table-danger"><span class="label label-danger label-wide">{{$trip->trip_status}}</span></td>
                                        @elseif($trip->trip_status == "Offloading Point")
                                        <td class="table-primary"><span class="label label-primary label-wide">{{$trip->trip_status}}</span></td>
                                        @endif
                                        <td><span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                         <td> <span class="label label-{{$trip->fuel ? 'success' : 'warning'}}">{{$trip->fuel ? "submitted" : "pending"}}</span></td>
                                         <td> <span class="label label-{{isset($trip->delivery_note->offloaded) ? "success" : "warning"}}"> {{isset($trip->delivery_note->offloaded) ? "submitted" : "pending"}}</span></td>
                                        <td class="w-10 line-height-35 table-dropdown">
                                            <div class="dropdown">
                                                <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fa fa-bars"></i>
                                                    <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i>View</a></li>
                                                    <li><a href="{{route('trips.edit', $trip->id)}}"><i class="fas fa-edit color-success"></i> Edit</a></li>
                                                    <li><a href="#" data-toggle="modal" data-target="#tripDeleteModal{{$trip->id}}"><i class="fa fa-trash color-danger"></i>Delete</a></li>

                                                </ul>
                                            </div>
                                            @include('trips.delete')

                                    </td>
                                      </tr>
                                      @endif
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


        <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="tripDocumentsModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modal4Label"><i class="fas fa-copy"></i> Trip Document(s) <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                    </div>
                    <form wire:submit.prevent="updateDocuments()" >
                    <div class="modal-body">
                        <h5 class="underline mt-n">Upload Trip Documents (<i> eg Delivery Note</i>)</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Document Title</label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="title.0" placeholder="Enter File Title eg Identity Card">
                                    @error('title.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="file">File</label>
                                     <small style="color:red;">Accepted file types: doc, docx, pdf, xls </small>
                                    <input type="file" class="form-control" wire:model.debounce.300ms="file.0"  placeholder="Upload File ">
                                    @error('file.0') <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <!-- /.col-md-6 -->
                        </div>
                        @foreach ($inputs as $key => $value)
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="title">Document Title</label>
                                    <input type="text" class="form-control" wire:model.debounce.300ms="title.{{$value}}" placeholder="Enter File Title eg Identity Card">
                                    @error('title.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="file">File</label>
                                    <small style="color:red;">Accepted file types: doc, docx, pdf, xls </small>
                                    <input type="file" class="form-control" wire:model.debounce.300ms="file.{{$value}}"  placeholder="Upload File ">
                                    @error('file.'.$value) <span class="text-danger error">{{ $message }}</span>@enderror
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <label for=""></label>
                                    <button class="btn btn-danger btn-rounded xs" style="margin-top:23px"  wire:click.prevent="remove({{$key}})"> <i class="fa fa-times"></i></button>
                                </div>
                            </div>
                            <!-- /.col-md-6 -->
                        </div>
                        @endforeach
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <button class="btn btn-success btn-rounded" style="float: right" wire:click.prevent="add({{$i}})"> <i class="fa fa-plus"></i> Document</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-gray btn-wide btn-rounded" data-dismiss="modal"><i class="fa fa-times"></i>Close</button>
                            <button type="submit" class="btn bg-success btn-wide btn-rounded"><i class="fa fa-refresh"></i>Update</button>
                        </div>
                        <!-- /.btn-group -->
                    </div>
                </form>
                </div>
            </div>
        </div>
<!-- Modal -->


    </div>
