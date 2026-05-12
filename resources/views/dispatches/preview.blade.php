@extends('layouts.jobcard')

@section('title')
    Dispatch Note   
@endsection

@section('content')
    
    @livewire('dispatches.preview', ['dispatch' => $dispatch])

@endsection