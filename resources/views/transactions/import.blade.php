@extends('adminlte::page')

@section('title', 'Import Statements')

@section('content_header')
    <h1>Import Bank Statements</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success">
            <strong>Success!</strong> {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Error!</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="bank_id" value="{{ $bank->id }}">
                <div class="form-group">
                    <label for="file">Upload CSV File</label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input" id="file" name="file" required accept=".csv,.xls,.xlsx">
                        <label class="custom-file-label" for="file">Choose file</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-3">
                    <i class="fas fa-upload"></i> Upload and Import
                </button>
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
    // Show selected file name in label
    document.querySelector('.custom-file-input').addEventListener('change', function(e){
        var fileName = document.getElementById("file").files[0].name;
        var nextSibling = e.target.nextElementSibling
        nextSibling.innerText = fileName
    });
</script>
@stop
