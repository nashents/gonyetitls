@extends('layouts.app')
@section('content')

@section('extra-css')
    @if (Auth::user()->employee->company)
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->employee->company->logo)!!}">
    @elseif (Auth::user()->company)
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->company->logo)!!}">
    @endif
@endsection
@section('title')
    Vehicles | @if (Auth::user()->employee->company)
    {{Auth::user()->employee->company->name}}
    @elseif (Auth::user()->company)
    {{Auth::user()->company->name}}
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
                                    <h4 class="title">Vehicle Details </h4>

                                </div>
                                <!-- /.col-md-6 -->

                                <!-- /.col-md-6 text-right -->
                            </div>
                            <!-- /.row -->
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
            							<li><a href="{{route('dashboard.index')}}"><i class="fa fa-home"></i> Home</a></li>
            							<li><a href="{{route('vehicles.index')}}"><i class="fa fa-list"></i> Vehicles</a></li>
            							<li class="active"><i class="fa fa-eye"></i>Vehicle Details</li>
            						</ul>
                                </div>
                                <!-- /.col-md-6 -->

                                <!-- /.col-md-6 -->
                            </div>
                            <!-- /.row -->

                                @livewire('vehicles.show', ['id' => $vehicle->id])
                            <!-- /.row -->
                        </div>
                        <!-- /.container-fluid -->


                    </div>
         

                </div>
                <!-- /.content-container -->
            </div>
            <!-- /.content-wrapper -->


        <!-- ========== PAGE JS FILES ========== -->

@endsection
@section('extra-js')
    <script>
    $(document).ready( function () {
        $('#tripsTable').DataTable();
    } );
    </script>
    <script>
        $(document).ready( function () {
            $('#fitnessesTable').DataTable();
        } );
        </script>
    <script>
        $(document).ready( function () {
            $('#bookingsTable').DataTable();
        } );
        </script>
    <script>
        $(document).ready( function () {
            $('#documentsTable').DataTable();
        } );
        </script>
         <script>
            $(document).ready( function () {
                $('#tyre_assignmentsTable').DataTable();
            } );
            </script>
         <script>
            $(document).ready( function () {
                $('#cashflowsTable').DataTable();
            } );
            </script>
         <script>
            $(document).ready( function () {
                $('#usageTable').DataTable();
            } );
            </script>
         <script>
            $(document).ready( function () {
                $('#logsTable').DataTable();
            } );
            </script>
@endsection
