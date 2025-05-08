@extends('adminlte::page')

@section('title', 'Upload Bank Statement')

@section('content_header')
    <h1>Upload Bank Statement PDF</h1>
@stop

@section('content')
    @if(session('success'))
        <x-adminlte-alert theme="success" title="Success">
            {{ session('success') }}
        </x-adminlte-alert>
    @endif

    @if ($errors->any())
        <x-adminlte-alert theme="danger" title="Upload Error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-adminlte-alert>
    @endif

    <form action="{{ route('bank.parse.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <x-adminlte-input-file name="statement" label="Bank Statement PDF" igroup-size="md" required>
            <x-slot name="prependSlot">
                <div class="input-group-text bg-primary">
                    <i class="fas fa-file-pdf"></i>
                </div>
            </x-slot>
        </x-adminlte-input-file>

        <x-adminlte-button type="submit" label="Upload & Parse" theme="primary" icon="fas fa-upload" class="mt-3"/>
    </form>
@stop
