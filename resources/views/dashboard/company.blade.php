@extends('layouts.app')
@section('extra-css')
<link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->company->logo)!!}">
@endsection
@section('title')
    Dashboard | {{Auth::user()->company->name}}

@endsection
@section('body-id')
<body class="top-navbar-fixed">
@endsection
@section('content')



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
            							<li><a href="#"><i class="fa fa-home"></i> Home</a></li>
            							<li class="active">Dashboard</li>
            						</ul>
                                </div>

                            </div>
                            <!-- /.row -->
                        </div>
                        <!-- /.container-fluid -->
                        @livewire('dashboard.company')

                    </div>
                    <!-- /.main-page -->


          

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
