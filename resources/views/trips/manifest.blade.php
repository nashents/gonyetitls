@extends('layouts.manifest')

@section('yield')
    Title
@endsection

@section('content')
    
    @livewire('trips.manifest', ['trip' => $trip])

@endsection

