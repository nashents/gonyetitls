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
            							<li class="active"> <i class="fas fa-list"></i> Manage Trips</li>
            						</ul>
                                </div>
                                <!-- /.col-md-6 -->

                            </div>
                            <!-- /.row -->
                        </div>
                        <!-- /.container-fluid -->

                        @livewire('third-parties.index')
                        <!-- /.section -->

                    </div>
                    <!-- /.main-page -->



        <!-- ========== PAGE JS FILES ========== -->


@endsection

@section('extra-js')
    <script>
    $(document).ready( function () {
        $('#tripsTable').DataTable();
    } );
    </script>
@endsection
