<x-layouts.main :title="'Transactions'" :contentHeader="'Transactions for ' . $bank->name">

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Income</h6>
                            <h4>₹{{ number_format($totalIncome, 2) }}</h4>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-arrow-up fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Expenses</h6>
                            <h4>₹{{ number_format($totalExpense, 2) }}</h4>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-arrow-down fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Net Amount</h6>
                            <h4>₹{{ number_format($netAmount, 2) }}</h4>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-balance-scale fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Total Transactions</h6>
                            <h4>{{ $transactions->count() }}</h4>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-list fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('transactions.index', $bank->id) }}">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="date_from" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="date_from" name="date_from"
                               value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="date_to" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="date_to" name="date_to"
                               value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="type" class="form-label">Type</label>
                        <select class="form-control" id="type" name="type">
                            <option value="">All Types</option>
                            <option value="credit" {{ request('type') == 'credit' ? 'selected' : '' }}>Income</option>
                            <option value="debit" {{ request('type') == 'debit' ? 'selected' : '' }}>Expense</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-control" id="category" name="category">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                                    {{ ucfirst($category) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('transactions.index', $bank->id) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Add Button --}}
    <div class="d-flex justify-content-start mb-3">
        <button class="btn btn-primary" data-toggle="modal" data-target="#createModal">
            <i class="fas fa-plus"></i> Add Transaction
        </button>
    </div>

    {{-- Transaction Table or Message --}}
    @if($transactions->isEmpty())
        <div class="alert alert-info text-center">
            <i class="fas fa-inbox fa-2x mb-2"></i>
            <br>
            No transactions found for <strong>{{ $bank->name }}</strong>.
        </div>
    @else
        <div class="card">
            <div class="card-body p-0">
                <table id="mydataTable" class="table table-bordered table-striped mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transactions as $tx)
                            <tr>
                                <td>
                                    <span class="text-muted">{{ $tx->date_for_ui }}</span>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $tx->title }}</div>
                                    @if($tx->description)
                                        <small class="text-muted">{{ $tx->description }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        {{ ucfirst($tx->category ?? 'Uncategorized') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ $tx->type === 'credit' ? 'bg-success' : 'bg-danger' }}">
                                        {{ $tx->transaction_type_label }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-bold {{ $tx->type === 'credit' ? 'text-success' : 'text-danger' }}">
                                        {{ $tx->formatted_amount }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModal{{ $tx->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal{{ $tx->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
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
                                                    <label>Date <span class="text-danger">*</span></label>
                                                    <input type="date" name="date" class="form-control" value="{{ $tx->date }}" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>Title <span class="text-danger">*</span></label>
                                                    <input type="text" name="title" class="form-control" value="{{ $tx->title }}" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>Description</label>
                                                    <textarea name="description" class="form-control" rows="2">{{ $tx->description }}</textarea>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Type <span class="text-danger">*</span></label>
                                                            <select name="type" class="form-control" required>
                                                                <option value="credit" {{ $tx->type === 'credit' ? 'selected' : '' }}>Credit (Income)</option>
                                                                <option value="debit" {{ $tx->type === 'debit' ? 'selected' : '' }}>Debit (Expense)</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Amount <span class="text-danger">*</span></label>
                                                            <input type="number" name="amount" class="form-control" step="0.01" value="{{ $tx->amount }}" required>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label>Category</label>
                                                    <select name="category" class="form-control">
                                                        <option value="">Auto-detect</option>
                                                        @foreach($categories as $category)
                                                            <option value="{{ $category }}" {{ $tx->category === $category ? 'selected' : '' }}>
                                                                {{ ucfirst($category) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <small class="text-muted">Leave blank to auto-categorize based on title</small>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-success">
                                                    <i class="fas fa-save"></i> Update
                                                </button>
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
                                                <h5 class="modal-title text-danger">
                                                    <i class="fas fa-exclamation-triangle"></i> Delete Transaction
                                                </h5>
                                                <button type="button" class="close" data-dismiss="modal">×</button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="text-center">
                                                    <i class="fas fa-trash-alt fa-3x text-danger mb-3"></i>
                                                    <h5>Are you sure?</h5>
                                                    <p>You want to delete <strong>{{ $tx->title }}</strong>?</p>
                                                    <div class="alert alert-warning">
                                                        <strong>Amount:</strong> {{ $tx->formatted_amount }}<br>
                                                        <strong>Date:</strong> {{ $tx->date_for_ui }}<br>
                                                        <strong>Type:</strong> {{ $tx->transaction_type_label }}
                                                    </div>
                                                    <p class="text-muted">This action cannot be undone.</p>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="fas fa-trash"></i> Yes, Delete
                                                </button>
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Create Modal --}}
    <div class="modal fade" id="createModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form action="{{ route('transactions.store') }}" method="POST">
                @csrf
                <input type="hidden" name="bank_id" value="{{ $bank->id }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-plus"></i> Add New Transaction
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">×</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Date <span class="text-danger">*</span></label>
                            <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-group">
                            <label>Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="Enter transaction title" required>
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Optional description"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Type <span class="text-danger">*</span></label>
                                    <select name="type" class="form-control" required>
                                        <option value="">Select Type</option>
                                        <option value="credit">Credit (Income)</option>
                                        <option value="debit">Debit (Expense)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Amount <span class="text-danger">*</span></label>
                                    <input type="number" name="amount" class="form-control" step="0.01" min="0" placeholder="0.00" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category" class="form-control">
                                <option value="">Auto-detect</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category }}">{{ ucfirst($category) }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">Leave blank to auto-categorize based on title</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Save Transaction
                        </button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize DataTable if exists
            if ($.fn.DataTable) {
                $('#mydataTable').DataTable({
                    "responsive": true,
                    "autoWidth": false,
                    "order": [[ 0, "desc" ]], // Sort by date descending
                    "columnDefs": [
                        { "orderable": false, "targets": [5] } // Disable sorting on Actions column
                    ]
                });
            }

            // Auto-set current date when creating new transaction
            $('#createModal').on('show.bs.modal', function () {
                const today = new Date().toISOString().split('T')[0];
                $(this).find('input[name="date"]').val(today);
            });

            // Form validation feedback
            $('form').on('submit', function() {
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Processing...').prop('disabled', true);

                // Re-enable after 3 seconds in case of error
                setTimeout(() => {
                    submitBtn.html(originalText).prop('disabled', false);
                }, 3000);
            });
        });
    </script>
    @endpush

</x-layouts.main>
