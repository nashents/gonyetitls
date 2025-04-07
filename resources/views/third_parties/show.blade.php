@extends('layouts.app')


@if (Auth::user()->agent)
@section('extra-css')
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->agent->company->logo)!!}">
@endsection
@section('title')
    Trips | {{Auth::user()->agent->company->name}}
@endsection
@elseif (Auth::user()->customer)
@section('extra-css')
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->customer->company->logo)!!}">
@endsection
@section('title')
    Trips | {{Auth::user()->customer->company->name}}
@endsection
@elseif (Auth::user()->transporter)
@section('extra-css')
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->transporter->company->logo)!!}">
@endsection
@section('title')
    Trips | {{Auth::user()->transporter->company->name}}
@endsection
@endif

@section('body-id')
<body class="top-navbar-fixed">
@endsection

@section('content')
            <!-- ========== TOP NAVBAR ========== -->
            @include('includes.third_party_navbar')

            <!-- ========== WRAPPER FOR BOTH SIDEBARS & MAIN CONTENT ========== -->
            <div class="content-wrapper">
                <div class="content-container">

                    <!-- ========== LEFT SIDEBAR ========== -->
                    @include('includes.third_party_sidebar')
                    <!-- /.left-sidebar -->

                    <div class="main-page">
                        <div class="container-fluid">
                            <div class="row page-title-div">
                                <div class="col-md-6">
                                    <h4 class="title">Trip Details </h4>

                                </div>
                                <!-- /.col-md-6 -->

                                <!-- /.col-md-6 text-right -->
                            </div>
                            <!-- /.row -->
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
            							<li><a href="{{route('dashboard.third_parties')}}"><i class="fa fa-home"></i> Home</a></li>
            							<li><a href="{{route('trips.third_parties')}}"><i class="fa fa-road"></i> Trips</a></li>
            							<li class="active">Trip Details</li>
            						</ul>
                                </div>
                                <!-- /.col-md-6 -->

                                <!-- /.col-md-6 -->
                            </div>
                            <!-- /.row -->

                            <section class="section">
                                <div class="container-fluid">
                                    <div class="row mt-15">
                                        <div class="col-md-12">
                                            <div class="panel">
                                                <div class="panel-heading">
                                                    <div class="panel-title">
                                                        <h5>Trip Number {{$trip->trip_number}} <small>{{$trip->start_date}}</small></h5>
                                                    </div>
                                                </div>
                                                <div class="panel-body">
                                                    <!-- Nav tabs -->
                                                    <ul class="nav nav-tabs border-bottom border-primary" role="tablist">
                                                        <li role="presentation" class="active"><a class="" href="#trip" aria-controls="trip" role="tab" data-toggle="tab">Trip Details</a></li>
                                                        <li role="presentation"><a class="" href="#documents" aria-controls="documents" role="tab" data-toggle="tab">Trip Documents</a></li>
                                                        @if (Auth::user()->transporter)
                                                        <li role="presentation"><a class="" href="#expenses" aria-controls="documents" role="tab" data-toggle="tab">Trip Expenses</a></li>
                                                        @endif
                                                        <li role="presentation"><a class="" href="#delivery_note" aria-controls="delivery_note" role="tab" data-toggle="tab">Offloading Details</a></li>
                                                        <li role="presentation"><a class="" href="#locations" aria-controls="locations" role="tab" data-toggle="tab">Location Updates</a></li>
                                                    </ul>


                                                    <!-- Tab panes -->
                                                    <div class="tab-content bg-white pt-30">
                                                        <div role="tabpanel" class="tab-pane active" id="trip">
                                                            <div class="col-md-12 p-n">
                                                                <div class="col-md-12">
                                                                    <div class="panel panel-info">
                                                                        <div class="panel-heading">
                                                                            <div class="panel-title">
                                                                                <h5>Trip Details</h5>
                                                                            </div>
                                                                        </div>
                                                                        <div class="panel-body overflow-x-auto">
                                                                            <div class="col-xs-12 p-n">
                                                                                <div class="col-xs-6 p-n">
                                                                                    Trip Number
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                                <div class="col-xs-6 p-n">
                                                                                    {{$trip->trip_number}}
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                            </div>
                                                                            <div class="col-xs-12 p-n">
                                                                                <div class="col-xs-6 p-n">
                                                                                  Transporter
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                                <div class="col-xs-6 p-n">
                                                                                    {{ucfirst($trip->transporter ? $trip->transporter->name : "")}}
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                            </div>
                                                                            <div class="col-xs-12 p-n">
                                                                                <div class="col-xs-6 p-n">
                                                                                    Trip Type
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                                <div class="col-xs-6 p-n">
                                                                                    {{$trip->trip_type ? $trip->trip_type->name : "undefined"}}
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                            </div>
                                                                            <div class="col-xs-12 p-n">
                                                                                <div class="col-xs-6 p-n">
                                                                                    Trip Group
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                                <div class="col-xs-6 p-n">
                                                                                    {{$trip->trip_group ? $trip->trip_group->name : "no group"}}
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                            </div>
                                                                            @if ($trip->broker)
                                                                            <div class="col-xs-12 p-n">
                                                                                <div class="col-xs-6 p-n">
                                                                                   Broker
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                                <div class="col-xs-6 p-n">
                                                                                    {{$trip->broker ? $trip->broker->name : "undefined"}}
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                            </div>
                                                                            @endif
                                                                            <div class="col-xs-12 p-n">
                                                                                <div class="col-xs-6 p-n">
                                                                                    Customer
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                                <div class="col-xs-6 p-n">
                                                                                   {{$trip->customer ? $trip->customer->name : "undefined"}}
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                            </div>
                                                                            <!-- /.col-xs-12 -->
                                                                            <div class="col-xs-12 p-n">
                                                                                <div class="col-xs-6 p-n">
                                                                                  Horse
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                                <div class="col-xs-6 p-n">
                                                                                    @if ($trip->horse)
                                                                                    {{$trip->horse->horse_make->name ? $trip->horse->horse_make->name : "undefined make" }} {{$trip->horse->horse_model->name ? $trip->horse->horse_model->name : " & model" }} ({{$trip->horse->registration_number}})
                                                                                    @else
                                                                                        <td>undefined</td>
                                                                                    @endif


                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                            </div>
                                                                            <div class="col-xs-12 p-n">
                                                                                <div class="col-xs-6 p-n">
                                                                                  Trailer(s)
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                                <div class="col-xs-6 p-n">
                                                                                    @if ($trip->trailers)
                                                                                    [ @foreach ($trip->trailers as $trailer)
                                                                                    {{$trailer->make}} {{$trailer->model}} ({{$trailer->registration_number}}),
                                                                                    @endforeach
                                                                                    ]
                                                                                    @else
                                                                                        <td>undefined</td>
                                                                                    @endif


                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                            </div>
                                                                            <!-- /.col-xs-12 -->
                                                                            <div class="col-xs-12 p-n">
                                                                                <div class="col-xs-6 p-n">
                                                                                   Driver
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                                <div class="col-xs-6 p-n">
                                                                                    {{$trip->driver->employee->name}} {{$trip->driver->employee->surname}}
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                            </div>
                                                                            @php
                                                                            $from = App\Models\Destination::find($trip->from);
                                                                            $to = App\Models\Destination::find($trip->to);
                                                                        @endphp
                                                                        <div class="col-xs-12 p-n">
                                                                            <div class="col-xs-6 p-n">
                                                                                From
                                                                            </div>
                                                                            <!-- /.col-md-6 -->
                                                                            <div class="col-xs-6 p-n">
                                                                                {{$from->country ? $from->country->name : "undefined"}} {{$from->city}}
                                                                            </div>
                                                                            <!-- /.col-md-6 -->
                                                                        </div>
                                                                        <div class="col-xs-12 p-n">
                                                                            <div class="col-xs-6 p-n">
                                                                               To
                                                                            </div>
                                                                            <!-- /.col-md-6 -->
                                                                            <div class="col-xs-6 p-n">
                                                                                {{$to->country ? $to->country->name : "undefined"}} {{$to->city}}
                                                                            </div>
                                                                            <!-- /.col-md-6 -->
                                                                        </div>
                                                                        <div class="col-xs-12 p-n">
                                                                            <div class="col-xs-6 p-n">
                                                                               Loading Point
                                                                            </div>
                                                                            <!-- /.col-md-6 -->
                                                                            <div class="col-xs-6 p-n">
                                                                                {{$trip->loading_point ? $trip->loading_point->name : "undefined"}}
                                                                            </div>
                                                                            <!-- /.col-md-6 -->
                                                                        </div>
                                                                        <div class="col-xs-12 p-n">
                                                                            <div class="col-xs-6 p-n">
                                                                               Offloading Point
                                                                            </div>
                                                                            <!-- /.col-md-6 -->
                                                                            <div class="col-xs-6 p-n">
                                                                                {{$trip->offloading_point ? $trip->offloading_point->name : "undefined"}}
                                                                            </div>
                                                                            <!-- /.col-md-6 -->
                                                                        </div>
                                                                        <div class="col-xs-12 p-n">
                                                                            <div class="col-xs-6 p-n">
                                                                               Route
                                                                            </div>
                                                                            <!-- /.col-md-6 -->
                                                                            <div class="col-xs-6 p-n">
                                                                                {{$trip->route ? $trip->route->name : "undefined"}} (<i>Rank {{$trip->route ? $trip->route->rank : "undefined"}}</i>)
                                                                            </div>
                                                                            <!-- /.col-md-6 -->
                                                                        </div>
                                                                        @if ($trip->truck_stops->count()>0)
                                                                        <div class="col-xs-12 p-n">
                                                                            <div class="col-xs-6 p-n">
                                                                               Recommended Truck Stops
                                                                            </div>
                                                                            <!-- /.col-md-6 -->
                                                                            <div class="col-xs-6 p-n">
                                                                              [ @foreach ($trip->truck_stops as $truck_stop)
                                                                                   {{ $truck_stop->name }},
                                                                               @endforeach]
                                                                            </div>
                                                                            <!-- /.col-md-6 -->
                                                                        </div>
                                                                        @endif
                                                                        <div class="col-xs-12 p-n">
                                                                            <div class="col-xs-6 p-n">
                                                                              Approx Distance
                                                                            </div>
                                                                            <!-- /.col-md-6 -->
                                                                            <div class="col-xs-6 p-n">
                                                                                {{$trip->distance}}Kms
                                                                            </div>
                                                                            <!-- /.col-md-6 -->
                                                                        </div>
                                                                            <div class="col-xs-12 p-n">
                                                                                <div class="col-xs-6 p-n">
                                                                                    Starting Date
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                                <div class="col-xs-6 p-n">
                                                                                    {{$trip->start_date}}
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                            </div>
                                                                            <div class="col-xs-12 p-n">
                                                                                <div class="col-xs-6 p-n">
                                                                                    Ending Date
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                                <div class="col-xs-6 p-n">
                                                                                    {{$trip->end_date}}
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                            </div>
                                                                            <div class="col-xs-12 p-n">
                                                                                <div class="col-xs-6 p-n">
                                                                                    Trip Status
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                                <div class="col-xs-6 p-n">
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

                                                                                    {{-- {{$trip->trip_status}} --}}
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                            </div>
                                                                            <div class="col-xs-12 p-n">
                                                                                <div class="col-xs-6 p-n">
                                                                                  Cargo
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                                <div class="col-xs-6 p-n">
                                                                                    {{$trip->cargo->name}}
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                            </div>
                                                                            <div class="col-xs-12 p-n">
                                                                                <div class="col-xs-6 p-n">
                                                                                  Quantity
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                                <div class="col-xs-6 p-n">
                                                                                    {{$trip->quantity}} L/Kgs
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                            </div>
                                                                            <div class="col-xs-12 p-n">
                                                                                <div class="col-xs-6 p-n">
                                                                                  Weight
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                                <div class="col-xs-6 p-n">
                                                                                    {{$trip->weight}} Tons/Litres
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                            </div>
                                                                            @if ($trip->currency)
                                                                            <div class="col-xs-12 p-n">
                                                                                <div class="col-xs-6 p-n">
                                                                                  Currency
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                                <div class="col-xs-6 p-n">
                                                                                    {{$trip->currency->name}}
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                            </div>
                                                                            @endif
                                                                            @if (Auth::user()->customer)
                                                                            <div class="col-xs-12 p-n">
                                                                                <div class="col-xs-6 p-n">
                                                                                  Customer Rate
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                                <div class="col-xs-6 p-n">
                                                                                    @if ($trip->rate)
                                                                                    {{$trip->currency->symbol}}{{number_format($trip->rate,2)}}
                                                                                    @endif
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                            </div>
                                                                            <div class="col-xs-12 p-n">
                                                                                <div class="col-xs-6 p-n">
                                                                                 Customer Freight
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                                <div class="col-xs-6 p-n">
                                                                                    @if ($trip->freight)
                                                                                    {{$trip->currency->symbol}}{{number_format($trip->freight,2)}}
                                                                                    @else
                                                                                    undefined
                                                                                    @endif
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                            </div>
                                                                            @endif
                                                                            @if (Auth::user()->transporter)
                                                                            <div class="col-xs-12 p-n">
                                                                                <div class="col-xs-6 p-n">
                                                                                  Transporter Rate
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                                <div class="col-xs-6 p-n">
                                                                                    @if ($trip->transporter_rate)
                                                                                    ${{number_format($trip->transporter_rate,2)}}
                                                                                    @else
                                                                                    undefined
                                                                                    @endif
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                            </div>
                                                                            <div class="col-xs-12 p-n">
                                                                                <div class="col-xs-6 p-n">
                                                                                 Transporter Freight
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                                <div class="col-xs-6 p-n">
                                                                                    @if ($trip->transporter_freight)
                                                                                    ${{number_format($trip->transporter_freight,2)}}
                                                                                    @else
                                                                                    undefined
                                                                                    @endif
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                            </div>
                                                                            @endif
                                                                            <div class="col-xs-12 p-n">
                                                                                <div class="col-xs-6 p-n">
                                                                                    Payment Status
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                                <div class="col-xs-6 p-n">
                                                                                    @if ($trip->payment_status == "Paid")
                                                                                    <span class="label label-success label-wide">{{$trip->payment_status}}</span>
                                                                                    @elseif($trip->payment_status == "ToPay")
                                                                                    <span class="label label-warning label-wide">{{$trip->payment_status}}</span>
                                                                                    @elseif($trip->payment_status == "Free")
                                                                                    <span class="label label-info label-wide">{{$trip->payment_status}}</span>
                                                                                    @endif

                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                            </div>
                                                                            @if (Auth::user()->agent)
                                                                            
                                                                            @if ($trip->agent)
                                                                            <div class="col-xs-12 p-n">
                                                                                <div class="col-xs-6 p-n">
                                                                                  Agent
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                                <div class="col-xs-6 p-n">
                                                                                   {{$trip->agent->name}} {{$trip->agent->surname}}

                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                            </div>
                                                                            <div class="col-xs-12 p-n">
                                                                                <div class="col-xs-6 p-n">
                                                                                    Commission
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                                <div class="col-xs-6 p-n">
                                                                                    {{$trip->commission->commission}}%
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                            </div>
                                                                            <div class="col-xs-12 p-n">
                                                                                <div class="col-xs-6 p-n">
                                                                                    Amount
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                                <div class="col-xs-6 p-n">
                                                                                    @if ($trip->commission->amount)
                                                                                    ${{number_format($trip->commission->amount,2)}}
                                                                                    @endif

                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                            </div>
                                                                            @endif
                                                                            @endif
                                                                            <div class="col-xs-12 p-n">
                                                                                <div class="col-xs-6 p-n">
                                                                                  Authorization
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                                <div class="col-xs-6 p-n">
                                                                                    <span class="label label-{{($trip->authorization == 'approved') ? 'success' : (($trip->authorization == 'rejected') ? 'danger' : 'warning') }}">{{($trip->authorization == 'approved') ? 'approved' : (($trip->authorization == 'rejected') ? 'rejected' : 'pending') }}</span>
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                            </div>
                                                                            @if ($trip->reason)
                                                                            <div class="col-xs-12 p-n">
                                                                                <div class="col-xs-6 p-n">
                                                                                  Reason
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                                <div class="col-xs-6 p-n">
                                                                                    {{$trip->reason}}
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                            </div>
                                                                          
                                                                            @endif
                                                                           
                                                                            @if ($trip->comments)
                                                                            <div class="col-xs-12 p-n">
                                                                                <div class="col-xs-6 p-n">
                                                                                  Comments
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                                <div class="col-xs-6 p-n">
                                                                                    {{$trip->comments}}
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                            </div>
                                                                            @endif
                                                                          
                                                                            <br>
                                                                            <br>
                                                                            <!-- /.col-xs-12 -->
                                                                          

                                                                        </div>



                                                                        <!-- /.panel-body -->
                                                                    </div>
                                                                    <!-- /.panel -->

                                                                </div>

                                                            </div>

                                                        </div>
                                                        <!-- /.tab-pane -->


                                                        <div role="tabpanel" class="tab-pane" id="documents">
                                                            <div class="col-md-12 p-n">
                                                                <div class="col-md-12">
                                                                    <div class="panel panel-info">
                                                                        <div class="panel-heading">
                                                                            <div class="panel-title">
                                                                                <h5>Trip Document(s)</h5>
                                                                            </div>
                                                                        </div>
                                                                        <div class="panel-body overflow-x-auto">
                                                                           @livewire('trips.documents', ['id' => $trip->id])

                                                                        </div>
                                                                        <!-- /.panel-body -->
                                                                    </div>
                                                                    <!-- /.panel -->

                                                                </div>

                                                            </div>
                                                        </div>
                                                        @if (Auth::user()->transporter)
                                                        <div role="tabpanel" class="tab-pane" id="expenses">
                                                            <div class="col-md-12 p-n">
                                                                <div class="col-md-12">
                                                                    <div class="panel panel-info">
                                                                        <div class="panel-heading">
                                                                            <div class="panel-title">
                                                                                <h5>Trip Expenses</h5>
                                                                            </div>
                                                                        </div>
                                                                        <div class="panel-body overflow-x-auto">
                                                                           @livewire('trips.expenses', ['id' => $trip->id])

                                                                        </div>
                                                                        <!-- /.panel-body -->
                                                                    </div>
                                                                    <!-- /.panel -->

                                                                </div>

                                                            </div>
                                                        </div>
                                                        @endif

                                                        <div role="tabpanel" class="tab-pane" id="delivery_note">
                                                            <div class="col-md-12 p-n">
                                                                <div class="col-md-12">
                                                                    <div class="panel panel-info">
                                                                        <div class="panel-heading">
                                                                            <div class="panel-title">
                                                                                <h5>Offloading Details</h5>
                                                                            </div>
                                                                        </div>
                                                                        <div class="panel-body overflow-x-auto">
                                                                            <div class="col-xs-12 p-n">
                                                                                <div class="col-xs-6 p-n">
                                                                                    Loading Date
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                                <div class="col-xs-6 p-n">
                                                                                @if (isset($trip->delivery_note->loaded))
                                                                                {{$trip->delivery_note->loaded_date}}
                                                                                @else
                                                                                No Loading Information Recorded
                                                                                @endif
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                            </div>
                                                                            <div class="col-xs-12 p-n">
                                                                                <div class="col-xs-6 p-n">
                                                                                    Loaded Quantinty
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                                <div class="col-xs-6 p-n">
                                                                                @if (isset($trip->delivery_note->loaded))
                                                                                {{$trip->delivery_note->loaded}}  {{$trip->delivery_note->measurement}}
                                                                                @else
                                                                                No Loading Information Recorded
                                                                                @endif
                                                                            </div>
                                                                                <!-- /.col-md-6 -->
                                                                            </div>
                                                                            <div class="col-xs-12 p-n">
                                                                                <div class="col-xs-6 p-n">
                                                                                    OffLoading Date
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                                <div class="col-xs-6 p-n">
                                                                                    @if (isset($trip->delivery_note->offloaded))
                                                                                    {{$trip->delivery_note->offloaded_date}}
                                                                                    @else
                                                                                    No Offloading Information Recorded
                                                                                    @endif

                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                            </div>
                                                                            <div class="col-xs-12 p-n">
                                                                                <div class="col-xs-6 p-n">
                                                                                    OffLoaded Quantinty
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                                <div class="col-xs-6 p-n">
                                                                                @if (isset($trip->delivery_note->offloaded))
                                                                                    {{$trip->delivery_note->offloaded}}  {{$trip->delivery_note->measurement}}
                                                                                    @else
                                                                                    No Offloading Information Recorded
                                                                                @endif
                                                                                </div>
                                                                                <!-- /.col-md-6 -->
                                                                            </div>



                                                                        </div>
                                                                        <!-- /.panel-body -->
                                                                    </div>
                                                                    <!-- /.panel -->

                                                                </div>

                                                            </div>
                                                        </div>

                                                        <div role="tabpanel" class="tab-pane" id="locations">
                                                            <div class="col-md-12 p-n">
                                                                <div class="col-md-12">
                                                                    <div class="panel panel-info">
                                                                        <div class="panel-heading">
                                                                            <div class="panel-title">
                                                                                <h5>Trip Location Update(s)</h5>
                                                                            </div>
                                                                        </div>
                                                                        <div class="panel-body overflow-x-auto">
                                                                           @livewire('trips.locations', ['id' => $trip->id])

                                                                        </div>
                                                                        <!-- /.panel-body -->
                                                                    </div>
                                                                    <!-- /.panel -->

                                                                </div>

                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="btn-group pull-right mt-10" >
                                                                    @livewire('trips.show',['id'=>$trip->id])
                                                                </div>
                                                               
                                                            </div>
                                                            </div>
                                                       
                                                    </div>
                                                    <!-- /.tab-content -->
                                                </div>
                                                <!-- /.panel-body -->
                                            </div>
                                            <!-- /.panel -->
                                        </div>
                                        <!-- /.col-md-12 -->
                                    </div>
                                    <!-- /.row -->
                                </div>
                                <!-- /.container-fluid -->
                            </section>
                            <!-- /.row -->
                        </div>
                        <!-- /.container-fluid -->


                    </div>
                    <!-- /.main-page -->


                    <!-- /.right-sidebar -->

                </div>
                <!-- /.content-container -->
            </div>
            <!-- /.content-wrapper -->


        <!-- ========== PAGE JS FILES ========== -->

@endsection
@section('extra-js')
{{-- <script>
    window.addEventListener("load", window.print());
  </script> --}}
  <script>
    $(document).ready( function () {
        $('#documentsTable').DataTable();
    } );
    </script>
  <script>
    $(document).ready( function () {
        $('#tripExpensesTable').DataTable();
    } );
    </script>
@endsection
