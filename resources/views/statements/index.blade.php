<x-layouts.main :title="'Bank Transactions'" :contentHeader="'Manage Bank Transactions'">

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

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
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

</x-layouts.main>
