@extends('layouts.app')

@section('title')
    Freight Job | Edit {{ $job->job_number }}
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
                                        <li><a href="{{route('freight.jobs.index')}}"><i class="fa fa-list"></i> Freight Jobs</a></li>
                                        <li><a href="{{route('freight.jobs.show', $job->id)}}">{{ $job->job_number }}</a></li>
            							<li class="active"> <i class="fas fa-edit"></i> Edit</li>
            						</ul>
                                </div>
                                <!-- /.col-md-6 -->
                            </div>
                            <!-- /.row -->
                        </div>
                        <!-- /.container-fluid -->

                        @livewire('freight.jobs.edit', ['jobId' => $job->id])
                        <!-- /.section -->

                    </div>
                    <!-- /.main-page -->

@endsection
