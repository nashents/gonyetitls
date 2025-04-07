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

                            <div class="panel-body">
                                <div  style="float: right">
                                    <a href="#" wire:click="exportTripsExcel()"  class="btn btn-default border-primary btn-wide"><i class="fa fa-download"></i>Excel</a>
                                    <a href="#" wire:click="exportTripsCSV()" class="btn btn-default border-primary btn-wide"><i class="fa fa-download"></i>CSV</a>
                                    <a href="#" wire:click="exportTripsPDF()" class="btn btn-default border-primary btn-wide"><i class="fa fa-download"></i>PDF</a>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body p-20"style="overflow-x:auto; width:100%; height:100%;">

                            <table id="tripsTable" class="table  table-striped table-bordered table-sm table-responsive" cellspacing="0" width="100%" style=" width:100%; height:100%;">
                                <caption><strong>{{ $route->name }}</strong></caption>
                                <br>
                                <br>
                                <thead >
                                    <th class="th-sm">Trip#
                                    </th>
                                    <th class="th-sm">Customer
                                    </th>
                                    <th class="th-sm">Transporter
                                    </th>
                                    <th class="th-sm">Horse
                                    </th>
                                    <th class="th-sm">LP
                                    </th>
                                    <th class="th-sm">OP
                                    </th>
                                    <th class="th-sm">Payment
                                    </th>
                                    <th class="th-sm">Trip
                                    </th>
                                    <th class="th-sm">FOS
                                    </th>
                                    <th class="th-sm">PODS
                                    </th>
                                    <th class="th-sm">Authorization
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
                                    <td>{{ucfirst($trip->customer ? $trip->customer->name : "undefined")}}</td>
                                    <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
                                    @if ($trip->horse)
                                    <td>{{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "undefined")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "undefined")}} ({{ucfirst($trip->horse ? $trip->horse->registration_number : "undefined")}})</td>
                                    @else
                                        <td>undefined</td>
                                    @endif
                                    
                                    <td>{{$trip->loading_point ? $trip->loading_point->name : "undefined"}}</td>
                                    <td>{{$trip->offloading_point ? $trip->offloading_point->name : "undefined"}}</td>
                                    <td>
                                        @if ($trip->payments->count()>0)
                                        <a href="{{ route('payments.show',$trip->payments->first()->id) }}"><span class="label label-success"> submitted</span></a>
                                        @else 
                                        <span class="label label-warning">pending</span>
                                        @endif
                                    </td>
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
                                     <td> <span class="label label-{{isset($trip->fuel_order) ? "success" : "warning"}}"> {{isset($trip->fuel_order) ? "submitted" : "pending"}}</span></td>
                                     <td> <span class="label label-{{isset($trip->delivery_note->offloaded) ? "success" : "warning"}}"> {{isset($trip->delivery_note->offloaded) ? "submitted" : "pending"}}</span></td>
                                     <td><span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                     <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i>View</a></li>
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
                                    <td>{{ucfirst($trip->customer ? $trip->customer->name : "undefined")}}</td>
                                    <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
                                    @if ($trip->horse)
                                    <td>{{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "undefined")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "undefined")}} ({{ucfirst($trip->horse ? $trip->horse->registration_number : "undefined")}})</td>
                                    @else
                                        <td>undefined</td>
                                    @endif
                                    <td>{{$trip->loading_point ? $trip->loading_point->name : "undefined"}}</td>
                                    <td>{{$trip->offloading_point ? $trip->offloading_point->name : "undefined"}}</td>
                                    <td>
                                        @if ($trip->payments->count()>0)
                                        <a href="{{ route('payments.show',$trip->payments->first()->id) }}"><span class="label label-success"> submitted</span></a>
                                        @else 
                                        <span class="label label-warning">pending</span>
                                        @endif
                                    </td>
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
                                    <td> <span class="label label-{{isset($trip->fuel_order) ? "success" : "warning"}}"> {{isset($trip->fuel_order) ? "submitted" : "pending"}}</span></td>
                                     <td> <span class="label label-{{isset($trip->delivery_note->offloaded) ? "success" : "warning"}}"> {{isset($trip->delivery_note->offloaded) ? "submitted" : "pending"}}</span></td>
                                     <td><span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                     <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i>View</a></li>
                                               

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
                                    <td>{{ucfirst($trip->customer ? $trip->customer->name : "undefined")}}</td>
                                    <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
                                    @if ($trip->horse)
                                    <td>{{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "undefined")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "undefined")}} ({{ucfirst($trip->horse ? $trip->horse->registration_number : "undefined")}})</td>
                                    @else
                                        <td>undefined</td>
                                    @endif
                                    <td>{{$trip->loading_point ? $trip->loading_point->name : "undefined"}}</td>
                                    <td>{{$trip->offloading_point ? $trip->offloading_point->name : "undefined"}}</td>
                                    <td>
                                        @if ($trip->payments->count()>0)
                                        <a href="{{ route('payments.show',$trip->payments->first()->id) }}"><span class="label label-success"> submitted</span></a>
                                        @else 
                                        <span class="label label-warning">pending</span>
                                        @endif
                                    </td>
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
                                    <td> <span class="label label-{{isset($trip->fuel_order) ? "success" : "warning"}}"> {{isset($trip->fuel_order) ? "submitted" : "pending"}}</span></td>
                                     <td> <span class="label label-{{isset($trip->delivery_note->offloaded) ? "success" : "warning"}}"> {{isset($trip->delivery_note->offloaded) ? "submitted" : "pending"}}</span></td>
                                     <td><span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                     <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i>View</a></li>
                                               

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
                                    <td>{{ucfirst($trip->customer ? $trip->customer->name : "undefined")}}</td>
                                    <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
                                    @if ($trip->horse)
                                    <td>{{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "undefined")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "undefined")}} ({{ucfirst($trip->horse ? $trip->horse->registration_number : "undefined")}})</td>
                                    @else
                                        <td>undefined</td>
                                    @endif
                                    <td>{{$trip->loading_point ? $trip->loading_point->name : "undefined"}}</td>
                                    <td>{{$trip->offloading_point ? $trip->offloading_point->name : "undefined"}}</td>
                                    <td>
                                        @if ($trip->payments->count()>0)
                                        <a href="{{ route('payments.show',$trip->payments->first()->id) }}"><span class="label label-success"> submitted</span></a>
                                        @else 
                                        <span class="label label-warning">pending</span>
                                        @endif
                                    </td>
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
                                    <td> <span class="label label-{{isset($trip->fuel_order) ? "success" : "warning"}}"> {{isset($trip->fuel_order) ? "submitted" : "pending"}}</span></td>
                                     <td> <span class="label label-{{isset($trip->delivery_note->offloaded) ? "success" : "warning"}}"> {{isset($trip->delivery_note->offloaded) ? "submitted" : "pending"}}</span></td>
                                     <td><span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                     <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i>View</a></li>
                                               

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
                                    <td>{{ucfirst($trip->customer ? $trip->customer->name : "undefined")}}</td>
                                    <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
                                    @if ($trip->horse)
                                    <td>{{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "undefined")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "undefined")}} ({{ucfirst($trip->horse ? $trip->horse->registration_number : "undefined")}})</td>
                                    @else
                                        <td>undefined</td>
                                    @endif
                                    <td>{{$trip->loading_point ? $trip->loading_point->name : "undefined"}}</td>
                                    <td>{{$trip->offloading_point ? $trip->offloading_point->name : "undefined"}}</td>
                                    <td>
                                        @if ($trip->payments->count()>0)
                                        <a href="{{ route('payments.show',$trip->payments->first()->id) }}"><span class="label label-success"> submitted</span></a>
                                        @else 
                                        <span class="label label-warning">pending</span>
                                        @endif
                                    </td>
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
                                    <td> <span class="label label-{{isset($trip->fuel_order) ? "success" : "warning"}}"> {{isset($trip->fuel_order) ? "submitted" : "pending"}}</span></td>
                                     <td> <span class="label label-{{isset($trip->delivery_note->offloaded) ? "success" : "warning"}}"> {{isset($trip->delivery_note->offloaded) ? "submitted" : "pending"}}</span></td>
                                     <td><span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                     <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i>View</a></li>
                                               

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
                                    <td>{{ucfirst($trip->customer ? $trip->customer->name : "undefined")}}</td>
                                    <td>{{ucfirst($trip->transporter ? $trip->transporter->name : "")}}</td>
                                   @if ($trip->horse)
                                   <td>{{ucfirst($trip->horse->horse_make ? $trip->horse->horse_make->name : "undefined")}} {{ucfirst($trip->horse->horse_model ? $trip->horse->horse_model->name : "undefined")}} ({{ucfirst($trip->horse ? $trip->horse->registration_number : "undefined")}})</td>
                                   @else
                                       <td>undefined</td>
                                   @endif
                                    <td>{{$trip->loading_point ? $trip->loading_point->name : "undefined"}}</td>
                                    <td>{{$trip->offloading_point ? $trip->offloading_point->name : "undefined"}}</td>
                                    <td>
                                        @if ($trip->payments->count()>0)
                                        <a href="{{ route('payments.show',$trip->payments->first()->id) }}"><span class="label label-success"> submitted</span></a>
                                        @else 
                                        <span class="label label-warning">pending</span>
                                        @endif
                                    </td>
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
                                    <td> <span class="label label-{{isset($trip->fuel_order) ? "success" : "warning"}}"> {{isset($trip->fuel_order) ? "submitted" : "pending"}}</span></td>
                                     <td> <span class="label label-{{isset($trip->delivery_note->offloaded) ? "success" : "warning"}}"> {{isset($trip->delivery_note->offloaded) ? "submitted" : "pending"}}</span></td>
                                     <td><span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span></td>
                                     <td class="w-10 line-height-35 table-dropdown">
                                        <div class="dropdown">
                                            <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fa fa-bars"></i>
                                                <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="{{route('trips.show', $trip->id)}}"><i class="fas fa-eye color-default"></i>View</a></li>
                                            </ul>
                                        </div>


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
   
    <div wire:ignore.self data-backdrop="static" data-keyboard="false" class="modal" id="trip_groupEditModal" tabindex="-1" role="dialog" aria-labelledby="modal4Label" data-backdrop-color="blue">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modal4Label"><i class="fas fa-truck-loading"></i> Edit Trip Group <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button></h4>
                </div>
                <form wire:submit.prevent="update()" >

                <div class="modal-body">
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" wire:model.debounce.300ms="name" placeholder="Enter Name" required>
                        @error('name') <span class="error" style="color:red">{{ $message }}</span> @enderror
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



</div>

