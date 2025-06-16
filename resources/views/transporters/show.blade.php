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
    Transporters | @if (Auth::user()->employee->company)
    {{Auth::user()->employee->company->name}}
    @elseif (Auth::user()->company)
    {{Auth::user()->company->name}}
    @endif
@endsection

@section('body-id')
<body class="top-navbar-fixed">
@endsection

          

                    <div class="main-page">
                        <div class="container-fluid">
                            <div class="row page-title-div">
                              @include('includes.top-message')
                            </div>
                            <!-- /.row -->
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
            							<li><a href="{{route('dashboard.index')}}"><i class="fa fa-home"></i> Home</a></li>
            							<li><a href="{{route('transporters.index')}}"><i class="fa fa-list"></i>Transporters</a></li>
            							<li class="active"> <i class="fa fa-eye"></i>Transporter</li>
            						</ul>
                                </div>
                            </div>
                            <!-- /.row -->
                        </div>

                        @livewire('transporters.show',['transporter' => $transporter])


                    </div>



@endsection

@section('extra-js')
    <script>
    $(document).ready( function () {
        $('#transportersTable').DataTable();
    } );
    </script>
   
    <script>
    $(document).ready( function () {
        $('#documentsTable').DataTable();
    } );
    </script>
    <script>
    $(document).ready( function () {
        $('#tripsTable').DataTable();
    } );
    </script>
    <script>
    $(document).ready( function () {
        $('#transporter_cargosTable').DataTable();
    } );
    </script>
    <script>
    $(document).ready( function () {
        $('#transporter_corridorsTable').DataTable();
    } );
    </script>
    <script>
    $(document).ready( function () {
        $('#billsTable').DataTable();
    } );
    </script>
        <script>
            $(document).ready( function () {
                $('#driversTable').DataTable();
            } );
            </script>
         <script>
            $(document).ready( function () {
                $('#trailersTable').DataTable();
            } );
            </script>
         <script>
            $(document).ready( function () {
                $('#horsesTable').DataTable();
            } );
            </script>
         <script>
            $(document).ready( function () {
                $('#vehiclesTable').DataTable();
            } );
            </script>
@endsection

