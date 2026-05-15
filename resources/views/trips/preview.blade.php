@extends('layouts.jobcard')

@section('title')
    Trip Sheet    
@endsection

@section('content')
    
    @livewire('trips.preview', ['trip' => $trip])

@endsection