<x-layouts.main :title="'Banks'" :contentHeader="'Banks Management'">

    {{-- Add Button --}}
    <div class="d-flex justify-content-start mb-3">
        <button class="btn btn-primary" data-toggle="modal" data-target="#createModal">Add Bank</button>
    </div>

    {{-- Banks Table or Message --}}
    @if($banks->isEmpty())
        <div class="alert alert-info text-center">
            No banks found.
        </div>
    @else
        <table id="mydataTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Account Number</th>
                    <th>Type</th>
                    <th>Current Balance</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($banks as $bank)
                    <tr>
                        <td>
                            <i class="{{ $bank->fa_icon }}" style="color: {{ $bank->icon_color }}"></i>
                            {{ $bank->name }}
                        </td>
                        <td>{{ $bank->account_number }}</td>
                        <td>
                            <span class="badge bg-info">{{ ucfirst($bank->bank_type) }}</span>
                        </td>
                        <td>₹{{ number_format($bank->current_balance, 2) }}</td>
                        <td>
                            <span class="badge {{ $bank->status === 'active' ? 'bg-success' : 'bg-danger' }}">
                                {{ ucfirst($bank->status) }}
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#editModal{{ $bank->id }}">Edit</button>
                            <button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#deleteModal{{ $bank->id }}">Delete</button>
                        </td>
                    </tr>

                    {{-- Edit Modal --}}
                    <div class="modal fade" id="editModal{{ $bank->id }}" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <form action="{{ route('banks.update', $bank->id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Bank</h5>
                                        <button type="button" class="close" data-dismiss="modal">×</button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label>Name</label>
                                            <input type="text" name="name" class="form-control" value="{{ $bank->name }}" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Address</label>
                                            <textarea name="address" class="form-control">{{ $bank->address }}</textarea>
                                        </div>
                                        <div class="form-group">
                                            <label>Account Number</label>
                                            <input type="text" name="account_number" class="form-control" value="{{ $bank->account_number }}">
                                        </div>
                                        <div class="form-group">
                                            <label>Bank Type</label>
                                            <select name="bank_type" class="form-control" required>
                                                <option value="savings" {{ $bank->bank_type === 'savings' ? 'selected' : '' }}>Savings</option>
                                                <option value="current" {{ $bank->bank_type === 'current' ? 'selected' : '' }}>Current</option>
                                                <option value="credit" {{ $bank->bank_type === 'credit' ? 'selected' : '' }}>Credit</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label>Initial Balance</label>
                                            <input type="number" name="initial_balance" class="form-control" step="0.01" value="{{ $bank->initial_balance }}" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Current Balance</label>
                                            <input type="number" name="current_balance" class="form-control" step="0.01" value="{{ $bank->current_balance }}" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Font Awesome Icon</label>
                                            <input type="text" name="fa_icon" class="form-control" value="{{ $bank->fa_icon }}" placeholder="fas fa-university">
                                        </div>
                                        <div class="form-group">
                                            <label>Icon Color</label>
                                            <input type="color" name="icon_color" class="form-control" value="{{ $bank->icon_color }}">
                                        </div>
                                        <div class="form-group">
                                            <label>Status</label>
                                            <select name="status" class="form-control" required>
                                                <option value="active" {{ $bank->status === 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="inactive" {{ $bank->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                            </select>
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
                    <div class="modal fade" id="deleteModal{{ $bank->id }}" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <form action="{{ route('banks.destroy', $bank->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Delete Bank</h5>
                                        <button type="button" class="close" data-dismiss="modal">×</button>
                                    </div>
                                    <div class="modal-body">
                                        Are you sure you want to delete <strong>{{ $bank->name }}</strong>?
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
            <form action="{{ route('banks.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Bank</h5>
                        <button type="button" class="close" data-dismiss="modal">×</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <textarea name="address" class="form-control"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Account Number</label>
                            <input type="text" name="account_number" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Bank Type</label>
                            <select name="bank_type" class="form-control" required>
                                <option value="savings">Savings</option>
                                <option value="current">Current</option>
                                <option value="credit">Credit</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Initial Balance</label>
                            <input type="number" name="initial_balance" class="form-control" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label>Current Balance</label>
                            <input type="number" name="current_balance" class="form-control" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label>Font Awesome Icon</label>
                            <input type="text" name="fa_icon" class="form-control" placeholder="fas fa-university">
                        </div>
                        <div class="form-group">
                            <label>Icon Color</label>
                            <input type="color" name="icon_color" class="form-control" value="#007bff">
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" class="form-control" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
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

</x-layouts.main>
