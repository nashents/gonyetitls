@extends('layouts.app')

@if (Auth::user()->agent)
@section('extra-css')
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->agent->company->logo)!!}">
@endsection
@section('title')
    Dashboard | {{Auth::user()->agent->company->name}}
@endsection
@elseif (Auth::user()->customer)
@section('extra-css')
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->customer->company->logo)!!}">
@endsection
@section('title')
    Dashboard | {{Auth::user()->customer->company->name}}
@endsection
@elseif (Auth::user()->transporter)
@section('extra-css')
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->transporter->company->logo)!!}">
@endsection
@section('title')
    Dashboard | {{Auth::user()->transporter->company->name}}
@endsection
@elseif (Auth::user()->broker)
@section('extra-css')
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->broker->company->logo)!!}">
@endsection
@section('title')
    Dashboard | {{Auth::user()->broker->company->name}}
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
                            <x-loading/>
                            <div class="row page-title-div">
                               @include('includes.top-message')
                                <!-- /.col-sm-6 -->

                                <!-- /.col-sm-6 text-right -->
                            </div>
                            <!-- /.row -->
                            <div class="row">
                                @include('includes.messages')
                            </div>
                            <div class="row breadcrumb-div">
                                <div class="col-sm-6">
                                    <ul class="breadcrumb">
            							<li><a href="#"><i class="fa fa-home"></i>Home</a></li>
            							<li class="active">Dashboard</li>
            						</ul>
                                </div>

                            </div>
                            <!-- /.row -->
                        </div>
                        <!-- /.container-fluid -->
                        @livewire('dashboard.third_party')

                    </div>
                    <!-- /.main-page -->


                </div>
                <!-- /.content-container -->
            </div>
            <!-- /.content-wrapper -->


@endsection
@section('extra-js')
<script src="{{asset('js/prism/prism.js')}}"></script>
<script src="{{asset('js/amcharts/amcharts.js')}}"></script>
<script src="{{asset('js/amcharts/serial.js')}}"></script>
<script src="{{asset('js/amcharts/pie.js')}}"></script>
<script src="{{asset('js/amcharts/plugins/animate/animate.min.js')}}"></script>
<script src="{{asset('js/amcharts/plugins/export/export.min.js')}}"></script>
<link rel="stylesheet" href="{{asset('js/amcharts/plugins/export/export.css')}}" type="text/css" media="all" />
<script src="{{asset('js/amcharts/themes/light.js')}}"></script>


@endsection
