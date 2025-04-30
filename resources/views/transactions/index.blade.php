@extends('adminlte::page')

@section('title', 'Transactions')

@section('content_header')
    <h1>Transactions for {{ $bank->name }}</h1>
@stop

@section('content')

    <!-- Add Transaction Button -->
    <button type="button" class="btn btn-primary mb-3" data-toggle="modal" data-target="#createTransactionModal">
        Add Transaction
    </button>

    <!-- Transactions Table -->
    <table class="table table-bordered">
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
            @foreach($transactions as $tx)
                <tr>
                    <td>{{ $tx->date }}</td>
                    <td>{{ $tx->title }}</td>
                    <td>
                        <span class="badge {{ $tx->type === 'income' ? 'bg-success' : 'bg-danger' }}">
                            {{ ucfirst($tx->type) }}
                        </span>
                    </td>
                    <td>₹{{ number_format($tx->amount, 2) }}</td>
                    <td>
                        <!-- Edit Button -->
                        <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editTransactionModal{{ $tx->id }}">
                            Edit
                        </button>

                        <!-- Delete Button -->
                        <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteTransactionModal{{ $tx->id }}">
                            Delete
                        </button>
                    </td>
                </tr>

                <!-- Edit Modal -->
                <div class="modal fade" id="editTransactionModal{{ $tx->id }}" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <form action="{{ route('transactions.update', $tx->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Transaction</h5>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="bank_id" value="{{ $tx->bank_id }}">
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
                                            <option value="income" {{ $tx->type == 'income' ? 'selected' : '' }}>Income</option>
                                            <option value="expense" {{ $tx->type == 'expense' ? 'selected' : '' }}>Expense</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Amount</label>
                                        <input type="number" name="amount" class="form-control" value="{{ $tx->amount }}" step="0.01" required>
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

                <!-- Delete Modal -->
                <div class="modal fade" id="deleteTransactionModal{{ $tx->id }}" tabindex="-1" role="dialog">
                    <div class="modal-dialog" role="document">
                        <form action="{{ route('transactions.destroy', $tx->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Delete Transaction</h5>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
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

    <!-- Pagination -->
    {{ $transactions->links() }}

    <!-- Create Modal -->
    <div class="modal fade" id="createTransactionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form action="{{ route('transactions.store') }}" method="POST">
                @csrf
                <input type="hidden" name="bank_id" value="{{ $bank->id }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Transaction</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
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
