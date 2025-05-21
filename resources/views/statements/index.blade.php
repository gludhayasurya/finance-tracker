<x-layouts.main :title="'Bank Transactions'" :contentHeader="'Manage Bank Transactions'">

@if($statements->isEmpty())
    <div class="alert alert-info text-center">No transactions found.</div>
@else
    <table id="mydataTable" class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Date</th>
                <th>Mode</th>
                <th>Particulars</th>
                <th>Deposit</th>
                <th>Withdrawal</th>
                <th>Balance</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($statements as $txn)
                <tr>
                    <td>{{ $txn->date }}</td>
                    <td>{{ $txn->mode }}</td>
                    <td>{{ $txn->extracted_particular }}</td>
                    <td>{{ number_format($txn->deposit, 2) }}</td>
                    <td>{{ number_format($txn->withdrawal, 2) }}</td>
                    <td>{{ number_format($txn->balance, 2) }}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editModal{{ $txn->id }}">
                            Edit
                        </button>
                    </td>

                    <!-- Edit Modal -->
<div class="modal fade" id="editModal{{ $txn->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $txn->id }}" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('statements.update', $txn->id) }}">
        @csrf
        @method('PUT')
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel{{ $txn->id }}">Edit Transaction</h5>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Date</label>
                    <input type="date" name="date" class="form-control" value="{{ $txn->date }}" required>
                </div>
                <div class="mb-3">
                    <label>Mode</label>
                    <input type="text" name="mode" class="form-control" value="{{ $txn->mode }}">
                </div>
                <div class="mb-3">
                    <label>Particulars</label>
                    <input type="text" name="extracted_particular" class="form-control" value="{{ $txn->extracted_particular }}">
                </div>
                <div class="mb-3">
                    <label>Deposit</label>
                    <input type="number" name="deposit" step="0.01" class="form-control" value="{{ $txn->deposit }}">
                </div>
                <div class="mb-3">
                    <label>Withdrawal</label>
                    <input type="number" name="withdrawal" step="0.01" class="form-control" value="{{ $txn->withdrawal }}">
                </div>
                <div class="mb-3">
                    <label>Balance</label>
                    <input type="number" name="balance" step="0.01" class="form-control" value="{{ $txn->balance }}">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Update</button>
            </div>
        </div>
    </form>
  </div>
</div>



                </tr>
            @endforeach
        </tbody>
    </table>
@endif

</x-layouts.main>
