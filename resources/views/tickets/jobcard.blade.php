@extends('layouts.jobcard')

@section('title')
    Job Card    
@endsection

@section('content')
    
    @livewire('tickets.jobcard', ['ticket' => $ticket])

@endsection