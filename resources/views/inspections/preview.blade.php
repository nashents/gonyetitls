@extends('layouts.jobcard')

@section('title')
 Inspection Preview
@endsection

@section('content')
    
    @livewire('inspections.preview', ['inspection' => $inspection])

@endsection

