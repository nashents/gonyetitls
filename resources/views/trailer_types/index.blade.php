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
    Trailers|@if (Auth::user()->employee)
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

            <!-- ========== TOP NAVBAR ========== -->
            @if (!Auth::user()->employee && Auth::user()->transporter)
            @include('includes.third_party_navbar')
            @else   
            @include('includes.navbar')
            @endif
          

            <!-- ========== WRAPPER FOR BOTH SIDEBARS & MAIN CONTENT ========== -->
            <div class="content-wrapper">
                <div class="content-container">

                    <!-- ========== LEFT SIDEBAR ========== -->
                    @if (!Auth::user()->employee && Auth::user()->transporter)
                    @include('includes.third_party_sidebar')
                    @else   
                    @include('includes.sidebar')
                    @endif
                    

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
            							<li class="active"> <i class="fas fa-list"></i> Trailer Types</li>
            						</ul>
                                </div>
                            </div>
                            <!-- /.row -->
                        </div>

                        @livewire('trailer-types.index')


                    </div>


                </div>
                <!-- /.content-container -->
            </div>



@endsection

@section('extra-js')
    <script>
    $(document).ready( function () {
        $('#trailer_typesTable').DataTable();
    } );
    </script>
@endsection

