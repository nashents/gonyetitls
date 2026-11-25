@extends('layouts.app')

@section('title')
    Add Clients | {{ $consolidation->consolidation_number }}
@endsection

@section('body-id')
<body class="top-navbar-fixed">
@endsection

@section('content')

                    <div class="main-page">
                        <div class="container-fluid">
                            <div class="row page-title-div">
                                <div class="col-md-6">
                                    <h4 class="title">Add Clients to Consolidation</h4>
                                </div>
                            </div>
                            <div class="row breadcrumb-div">
                                <div class="col-md-6">
                                    <ul class="breadcrumb">
                                        <li><a href="{{route('dashboard.index')}}"><i class="fa fa-home"></i> Home</a></li>
                                        <li><a href="{{route('freight.consolidations.index')}}"><i class="fa fa-list"></i> Consolidations</a></li>
                                        <li><a href="{{route('freight.consolidations.show', $consolidation->id)}}">{{ $consolidation->consolidation_number }}</a></li>
                                        <li class="active"><i class="fa fa-users"></i> Add Clients</li>
                                    </ul>
                                </div>
                            </div>

                            @livewire('freight.consolidations.add-clients', ['id' => $consolidation->id])

                        </div>
                    </div>

@endsection
