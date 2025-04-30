@extends('adminlte::page')

@section('title', 'Banks')
@section('content_header')
    <h1>Manage Banks</h1>
@stop

@section('content')

<!-- Success Message -->
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<!-- Add Bank Button -->
<button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#addBankModal">
    Add Bank
</button>

<!-- Banks Table -->
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Name</th>
            <th>Address</th>
            <th>Initial Balance</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @foreach($banks as $bank)
        <tr>
            <td>{{ $bank->name }}</td>
            <td>{{ $bank->address }}</td>
            <td>₹{{ number_format($bank->initial_balance, 2) }}</td>
            <td>
                <!-- Edit Button -->
                <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#editBankModal{{ $bank->id }}">
                    Edit
                </button>
                <!-- Delete Button -->
                <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteBankModal{{ $bank->id }}">
                    Delete
                </button>
            </td>
        </tr>

        <!-- Edit Bank Modal -->
        <div class="modal fade" id="editBankModal{{ $bank->id }}" tabindex="-1" aria-labelledby="editBankLabel{{ $bank->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('banks.update', $bank->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editBankLabel{{ $bank->id }}">Edit Bank</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label>Name</label>
                                <input type="text" name="name" value="{{ $bank->name }}" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Address</label>
                                <input type="text" name="address" value="{{ $bank->address }}" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label>Initial Balance</label>
                                <input type="number" name="initial_balance" value="{{ $bank->initial_balance }}" class="form-control" step="0.01" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-primary" type="submit">Update</button>
                            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Bank Modal -->
        <div class="modal fade" id="deleteBankModal{{ $bank->id }}" tabindex="-1" aria-labelledby="deleteBankLabel{{ $bank->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <form action="{{ route('banks.destroy', $bank->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteBankLabel{{ $bank->id }}">Delete Bank</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to delete <strong>{{ $bank->name }}</strong>?
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-danger" type="submit">Delete</button>
                            <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @endforeach
    </tbody>
</table>

<!-- Add Bank Modal -->
<div class="modal fade" id="addBankModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <form action="{{ route('banks.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addBankLabel">Add New Bank</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Bank Name" required>
                    </div>
                    <div class="mb-3">
                        <label>Address</label>
                        <input type="text" name="address" class="form-control" placeholder="Branch Address">
                    </div>
                    <div class="mb-3">
                        <label>Initial Balance</label>
                        <input type="number" name="initial_balance" step="0.01" class="form-control" value="0.00" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success" type="submit">Add</button>
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
