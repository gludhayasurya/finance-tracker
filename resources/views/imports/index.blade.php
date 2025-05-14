<x-layouts.main :title="'Imports'" :contentHeader="'Manage Imports'">

    @if ($imports->isEmpty())
        <div class="alert alert-info text-center">No imports found.</div>
    @else
        <table id="mydataTable" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Bank</th>
                    <th>Filename</th>
                    <th>Filepath</th>
                    <th>Total Deposit</th>
                    <th>Total Withdrawal</th>
                    <th>Total Balance</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($imports as $import)
                    <tr>
                        <td>{{ $import->id }}</td>
                        <td>{{ $import->bank->name ?? 'N/A' }}</td>
                        <td>{{ $import->filename }}</td>
                        <td>{{ $import->filepath }}</td>
                        <td>{{ number_format($import->total_deposit, 2) }}</td>
                        <td>{{ number_format($import->total_withdrawal, 2) }}</td>
                        <td>{{ number_format($import->total_balance, 2) }}</td>
                        <td>{{ ucfirst($import->status) }}</td>
                        <td>{{ $import->created_at->format('Y-m-d H:i:s') }}</td>
                        <td>
                            <a href="{{ route('imports.view.statements', ['bank' => $import->bank_id, 'import' => $import->id]) }}"
                               class="btn btn-sm btn-primary">View Transactions</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

</x-layouts.main>
