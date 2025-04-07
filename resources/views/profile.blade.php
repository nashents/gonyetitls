@extends('layouts.app')
@section('extra-css')
    @if (Auth::user()->employee->company)
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->employee->company->logo)!!}">
    @elseif (Auth::user()->company)
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->company->logo)!!}">
    @endif
@endsection
@section('title')
    Profile|@if (Auth::user()->employee->company)
    {{Auth::user()->employee->company->name}}
    @elseif (Auth::user()->company)
    {{Auth::user()->company->name}}
    @endif
@endsection
@section('content')
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
                                <h4 class="title">{{ucfirst(Auth::user()->name)}} {{ucfirst(Auth::user()->surname)}}<small class="ml-10">My Profile</small></h4>

                                </div>
                            </div>
                            <!-- /.row -->
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
            							<li><a href="{{route('dashboard.index')}}"><i class="fa fa-home"></i>Dashboard</a></li>
            							<li class="active"> <i class="fa fa-user"></i> Profile</li>
                                        <li class="active">{{ucfirst(Auth::user()->name)}} {{ucfirst(Auth::user()->surname)}}</li>
            						</ul>
                                </div>
                                <!-- /.col-md-6 -->
                                <div class="col-md-6 text-right">
                                    {{-- <a href="#"><i class="fa fa-comments"></i> Talk to us</a>
                                    <a href="#" class="pl-20"><i class="fa fa-cog"></i> Settings</a> --}}
                                </div>
                                <!-- /.col-md-6 -->
                            </div>
                            <!-- /.row -->

                            @livewire('profile', ['user' => $user,'employee' => $employee])
                            <!-- /.row -->
                        </div>
                        <!-- /.container-fluid -->


                    </div>
                    <!-- /.main-page -->



                </div>
                <!-- /.content-container -->
            </div>
            <!-- /.content-wrapper -->

        </div>

      
@endsection
