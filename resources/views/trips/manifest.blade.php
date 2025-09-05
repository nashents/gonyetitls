@extends('layouts.manifest')

@section('title')
    Road Manifest
@endsection

@section('content')
    
    @livewire('trips.manifest', ['trip' => $trip])

@endsection

