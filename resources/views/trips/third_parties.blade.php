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



{{-- @section('page-css')
<link rel="stylesheet" href="css/prism/prism.css" media="screen" > <!-- USED FOR DEMO HELP - YOU CAN REMOVE IT -->
<link rel="stylesheet" type="text/css" href="js/DataTables/datatables.min.css"/>
@endsection --}}


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
                              @include('includes.top-message')
                            </div>
                            <!-- /.row -->
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
            							<li><a href="{{route('dashboard.third_parties')}}"><i class="fa fa-home"></i> Home</a></li>
            							<li class="active"> <i class="fas fa-road"></i>Trip History</li>
            						</ul>
                                </div>
                                <!-- /.col-md-6 -->

                            </div>
                            <!-- /.row -->
                        </div>
                        <!-- /.container-fluid -->

                        @livewire('third_parties.index')
                        <!-- /.section -->

                    </div>
                    <!-- /.main-page -->

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
@endsection
