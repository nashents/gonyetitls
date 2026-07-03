@extends('layouts.app')
@section('content')
@section('extra-css')
    @if (isset(Auth::user()->employee->company))
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->employee->company->logo)!!}">
    @elseif (isset(Auth::user()->company))
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->company->logo)!!}">
    @endif
@endsection
@section('title')
    Profile | @if (isset(Auth::user()->employee->company))
    {{Auth::user()->employee->company->name}}
    @elseif (isset(Auth::user()->company))
    {{Auth::user()->company->name}}
    @endif
@endsection
     

                    <div class="main-page">
                        <div class="container-fluid">
                            <div class="row page-title-div">
                                <div class="col-md-6">
                                <h4 class="title"> {{$company->name}} <small class="ml-10">Profile</small></h4>

                                </div>
                            </div>
                            <!-- /.row -->
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
            							<li><a href="{{route('dashboard.index')}}"><i class="fa fa-home"></i>Dashboard</a></li>
            							<li class="active"> <i class="fa fa-user"></i> Company Profile</li>
                                        <li class="active"> 
                                            {{$company->name}}
                                        </li>
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

                            <div class="row mt-30">
                                <div class="col-md-2">
                                    <div class="panel border-primary no-border border-3-top">
                                        <div class="panel-heading">
                                            <div class="panel-title">
                                                <h5>Company Logo</h5>
                                            </div>
                                        </div>
                                        <div class="panel-body">
                                            <div class="row">
                                                @livewire('companies.logo', ['company' => $company])
                                            </div>

                                        </div>
                                    </div>

                                </div>
                                <!-- /.col-md-3 -->

                                <div class="col-md-10">
                                    @include('includes.messages')
                                    
                                    @livewire('companies.profile', ['company' => $company])
                                   
                                </div>
                                <!-- /.col-md-9 -->
                            </div>
                            <!-- /.row -->
                        </div>
                        <!-- /.container-fluid -->


                    </div>
                    <!-- /.main-page -->



        </div>
@endsection
@section('extra-js')
    <script>
    $(document).ready( function () {
        $('#bank_accountsTable').DataTable();
    } );
    </script>
    <script>
    $(document).ready( function () {
        $('#documentsTable').DataTable();
    } );
    </script>
@endsection
