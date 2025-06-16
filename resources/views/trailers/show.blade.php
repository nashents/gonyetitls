@extends('layouts.app')
@section('content')

@section('extra-css')
    @if (Auth::user()->employee)
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->employee->company->logo)!!}">
    @elseif (Auth::user()->company)
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->company->logo)!!}">
    @elseif (Auth::user()->transporter)
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->transporter->company->logo)!!}">
    @endif
@endsection
@section('title')
    Trailer | @if (Auth::user()->employee)
    {{Auth::user()->employee->company->name}}
    @elseif (Auth::user()->company)
    {{Auth::user()->company->name}}
    @elseif (Auth::user()->transporter)
    {{Auth::user()->transporter->company->name}}
    @endif
@endsection

@section('body-id')
<body class="top-navbar-fixed">
@endsection

        
                    

                    <div class="main-page">
                        <div class="container-fluid">
                            <div class="row page-title-div">
                                <div class="col-md-6">
                                    <h4 class="title">Trailer Details </h4>

                                </div>
                                <!-- /.col-md-6 -->

                                <!-- /.col-md-6 text-right -->
                            </div>
                            <!-- /.row -->
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
            							<li><a href="{{route('dashboard.index')}}"><i class="fa fa-home"></i> Home</a></li>
            							<li><a href="{{route('trailers.index')}}"><i class="fas fa-list"></i> Trailers</a></li>
            							<li class="active"><i class="fas fa-eye"></i>Trailer Details</li>
            						</ul>
                                </div>
                                <!-- /.col-md-6 -->

                                <!-- /.col-md-6 -->
                            </div>
                            <!-- /.row -->

                            <div class="row mt-30">
                                @livewire('trailers.show', ['id' => $trailer->id])
                                <!-- /.col-md-9 -->
                            </div>
                            <!-- /.row -->
                        </div>
                        <!-- /.container-fluid -->


                    </div>


              


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
                $('#bookingsTable').DataTable();
            } );
            </script>
         <script>
            $(document).ready( function () {
                $('#documentsTable').DataTable();
            } );
            </script>
@endsection
