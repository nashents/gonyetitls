@extends('layouts.app')

@section('title')
    Freight Job | {{ $job->job_number }}
@endsection

@section('body-id')
<body class="top-navbar-fixed">
@endsection

@section('content')

                    <div class="main-page">
                        <div class="container-fluid">
                            <div class="row page-title-div">
                                <div class="col-md-6">
                                    <h4 class="title">Freight Job Details</h4>
                                </div>
                            </div>
                            <!-- /.row -->
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
            							<li><a href="{{route('dashboard.index')}}"><i class="fa fa-home"></i> Home</a></li>
                                        <li><a href="{{route('freight.jobs.index')}}"><i class="fa fa-list"></i> Freight Jobs</a></li>
            							<li class="active"><i class="fa fa-eye"></i> {{ $job->job_number }}</li>
            						</ul>
                                </div>
                                <!-- /.col-md-6 -->
                            </div>
                            <!-- /.row -->

                            @livewire('freight.jobs.show', ['id' => $job->id])

                        </div>
                        <!-- /.container-fluid -->

                    </div>
                    <!-- /.main-page -->

@endsection
