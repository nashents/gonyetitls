@extends('layouts.app')
@section('content')

@section('extra-css')
    @if (isset(Auth::user()->employee->company))
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->employee->company->logo)!!}">
    @elseif (Auth::user()->company)
    <link rel="shortcut icon" type = "image/png" href="{!! asset('images/uploads/'.Auth::user()->company->logo)!!}">
    @endif
@endsection
@section('title')
    Reminders | @if (isset(Auth::user()->employee->company))
    {{Auth::user()->employee->company->name}}
    @elseif (Auth::user()->company)
    {{Auth::user()->company->name}}
    @endif
@endsection
@section('body-id')
<body class="top-navbar-fixed">
@endsection

    
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
            							<li><a href="{{route('reminders.index')}}" ><i class="fa fa-list"></i> Reminders</a></li>
            							<li class="active"> <i class="fas fa-eye"></i> Reminder</li>
            						</ul>
                                </div>
                            </div>
                            <!-- /.row -->
                        </div>

                        @livewire('fitnesses.show',['id' => $fitness->id])


                    </div>


@endsection

@section('extra-js')
    <script>
    $(document).ready( function () {
        $('#remindersTable').DataTable();
    } );
    </script>

@endsection

