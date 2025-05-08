@extends('adminlte::page')

@section('title', 'Transactions')

@section('content_header')
    <h1>Transactions for {{ $bank->name }}</h1>
@stop

@section('content')

    {{-- Add Button --}}
    <div class="d-flex justify-content-start mb-3">
        <button class="btn btn-primary" data-toggle="modal" data-target="#createModal">Add Transaction</button>
    </div>

    {{-- Transaction Table or Message --}}
    @if($transactions->isEmpty())
        <div class="alert alert-info text-center">
            No transactions found for <strong>{{ $bank->name }}</strong>.
        </div>
    @else
        <table id="mydataTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transactions as $tx)
                    <tr>
                        <td>{{ $tx->date_for_ui }}</td>
                        <td>{{ $tx->title }}</td>
                        <td>
                            <span class="badge {{ $tx->type === 'income' ? 'bg-success' : 'bg-danger' }}">
                                {{ ucfirst($tx->type) }}
                            </span>
                        </td>
                        <td>₹{{ number_format($tx->amount, 2) }}</td>
                        <td>
                            <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editModal{{ $tx->id }}">Edit</button>
                            <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal{{ $tx->id }}">Delete</button>
                        </td>
                    </tr>

                    {{-- Edit Modal --}}
                    <div class="modal fade" id="editModal{{ $tx->id }}" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <form action="{{ route('transactions.update', $tx->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="bank_id" value="{{ $tx->bank_id }}">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Transaction</h5>
                                        <button type="button" class="close" data-dismiss="modal">×</button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label>Date</label>
                                            <input type="date" name="date" class="form-control" value="{{ $tx->date }}" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Title</label>
                                            <input type="text" name="title" class="form-control" value="{{ $tx->title }}" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Type</label>
                                            <select name="type" class="form-control" required>
                                                <option value="income" {{ $tx->type === 'income' ? 'selected' : '' }}>Income</option>
                                                <option value="expense" {{ $tx->type === 'expense' ? 'selected' : '' }}>Expense</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Amount</label>
                                            <input type="number" name="amount" class="form-control" step="0.01" value="{{ $tx->amount }}" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-success">Update</button>
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Delete Modal --}}
                    <div class="modal fade" id="deleteModal{{ $tx->id }}" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <form action="{{ route('transactions.destroy', $tx->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Delete Transaction</h5>
                                        <button type="button" class="close" data-dismiss="modal">×</button>
                                    </div>
                                    <div class="modal-body">
                                        Are you sure you want to delete <strong>{{ $tx->title }}</strong>?
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-danger">Yes, Delete</button>
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Create Modal --}}
    <div class="modal fade" id="createModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form action="{{ route('transactions.store') }}" method="POST">
                @csrf
                <input type="hidden" name="bank_id" value="{{ $bank->id }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Transaction</h5>
                        <button type="button" class="close" data-dismiss="modal">×</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Date</label>
                            <input type="date" name="date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Type</label>
                            <select name="type" class="form-control" required>
                                <option value="income">Income</option>
                                <option value="expense">Expense</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Amount</label>
                            <input type="number" name="amount" class="form-control" step="0.01" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Save</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

{{-- Optional: include this if you use DataTables --}}
@include('partials.datatables')
