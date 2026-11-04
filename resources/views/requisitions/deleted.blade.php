@extends('layouts.app')

@section('extra-css')
    @if (Auth::user()->employee->company)
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->employee->company->logo)!!}">
    @elseif (Auth::user()->company)
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->company->logo)!!}">
    @endif
@endsection
@section('title')
    Deleted Requisitions | @if (Auth::user()->employee->company)
    {{Auth::user()->employee->company->name}}
    @elseif (Auth::user()->company)
    {{Auth::user()->company->name}}
    @endif
@endsection

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
            							<li><a href="{{route('dashboard.index')}}"><i class="fa fa-home"></i> Home</a></li>
            							<li><a href="{{route('requisitions.index')}}"><i class="fa fa-list"></i> All Requisitions</a></li>
            							<li class="active"> <i class="fa fa-trash"></i> Deleted Requisitions</li>
            						</ul>
                                </div>
                            </div>
                            <!-- /.row -->
                        </div>
                        <!-- /.container-fluid -->

                        @livewire('requisitions.deleted')

                    </div>
                    <!-- /.main-page -->

@endsection

@section('extra-js')
    <script>
        window.addEventListener('show-requisitionRestoreModal', event => {
            $('#requisitionRestoreModal').modal('show');
        });
        window.addEventListener('hide-requisitionRestoreModal', event => {
            $('#requisitionRestoreModal').modal('hide');
        });
    </script>
@endsection
