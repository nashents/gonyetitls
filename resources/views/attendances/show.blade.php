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
    Attendance Register |@if (Auth::user()->employee->company)
    {{Auth::user()->employee->company->name}}
    @elseif (Auth::user()->company)
    {{Auth::user()->company->name}}
    @endif
@endsection

@section('body-class')
<body class="top-navbar-fixed">
@endsection


                    <div class="main-page">
                        <div class="container-fluid">
                            <div class="row page-title-div">
                                <div class="col-md-6">
                                    <h4 class="title">Booking Details </h4>

                                </div>
                                <!-- /.col-md-6 -->

                                <!-- /.col-md-6 text-right -->
                            </div>
                            <!-- /.row -->
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
            							<li><a href="{{route('dashboard.index')}}"><i class="fa fa-home"></i> Home</a></li>
            							<li><a href="{{route('attendances.index')}}"><i class="fa fa-list"></i> Attendance Registers</a></li>
            							<li class="active"> <i class="fa fa-eye"></i> Attendance Register</li>
            						</ul>
                                </div>
                                <!-- /.col-md-6 -->

                                <!-- /.col-md-6 -->
                            </div>
                          @livewire('attendances.show', ['id' => $attendance->id])
                        </div>
                        <!-- /.container-fluid -->


                    </div>




        <!-- ========== PAGE JS FILES ========== -->

@endsection
@section('extra-js')
    <script>
    $(document).ready( function () {
        $('#attendancesTable').DataTable();
    } );
    </script>

@endsection
