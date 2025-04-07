@extends('layouts.app')
@section('content')

@section('extra-css')
    @if (Auth::user()->employee)
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->employee->company->logo)!!}">
    @elseif (Auth::user()->company)
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->company->logo)!!}">
    @elseif (Auth::user()->transporter)
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->transporter->company->logo)!!}">
    @elseif (Auth::user()->customer)
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->customer->company->logo)!!}">
    @elseif (Auth::user()->agent)
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->agent->company->logo)!!}">
    @endif
@endsection
@section('title')
    Trips | @if (Auth::user()->employee)
    {{Auth::user()->employee->company->name}}
    @elseif (Auth::user()->company)
    {{Auth::user()->company->name}}
    @elseif (Auth::user()->transporter)
    {{Auth::user()->transporter->company->name}}
    @elseif (Auth::user()->customer)
    {{Auth::user()->customer->company->name}}
    @elseif (Auth::user()->agent)
    {{Auth::user()->agent->company->name}}
    @endif
@endsection

@section('body-class')
<body class="top-navbar-fixed">
@endsection
            <!-- ========== TOP NAVBAR ========== -->
           @include('includes.navbar')

            <!-- ========== WRAPPER FOR BOTH SIDEBARS & MAIN CONTENT ========== -->
            <div class="content-wrapper">
                <div class="content-container">

                    <!-- ========== LEFT SIDEBAR ========== -->
                  @include('includes.sidebar')
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
            							<li><a href="{{route('dashboard.index')}}"><i class="fa fa-home"></i> Home</a></li>
                                        <li><a href="{{route('trips.index')}}"><i class="fa fa-list"></i> All Trips</a></li>
            							<li class="active"><i class="fa fa-eye"></i> Trip</li>
            						</ul>
                                </div>
                                <!-- /.col-md-6 -->

                                <!-- /.col-md-6 -->
                            </div>
                            <!-- /.row -->
                            @livewire('trips.show', ['id' => $trip->id])
                 
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
<script>
    window.addEventListener("load", window.print());
  </script>
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
  <script>
    $(document).ready( function () {
        $('#paymentsTable').DataTable();
    } );
    </script>
  <script>
    $(document).ready( function () {
        $('#trip_locationsTable').DataTable();
    } );
    </script>
  <script>
    $(document).ready( function () {
        $('#trip_destinationsTable').DataTable();
    } );
    </script>
@endsection
