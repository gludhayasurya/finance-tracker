@include('components.wave-popup')
@include('partials.datatables')

@extends('adminlte::page')

@section('title', $title ?? 'Dashboard')

@section('content_header')
    @isset($contentHeader)
        <h1>{{ $contentHeader }}</h1>
    @endisset
@endsection

@section('content')
    {!! $slot !!}
@endsection

@section('css')
    @stack('custom-css')
@endsection

@section('js')
    @stack('custom-js')
@endsection
