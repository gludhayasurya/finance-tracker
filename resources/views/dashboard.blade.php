@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Finance Overview</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-4">
        <x-adminlte-small-box title="Total Balance" text="₹{{ $totalBalance }}" theme="info" icon="fas fa-wallet"/>
    </div>
    <div class="col-md-4">
        <x-adminlte-small-box title="Income" text="₹{{ $totalIncome }}" theme="success" icon="fas fa-arrow-down"/>
    </div>
    <div class="col-md-4">
        <x-adminlte-small-box title="Expenses" text="₹{{ $totalExpenses }}" theme="danger" icon="fas fa-arrow-up"/>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-6">
        <x-adminlte-card title="Monthly Trend (Income vs Expenses)" theme="light">
            <canvas id="monthlyChart"></canvas>
        </x-adminlte-card>
    </div>
    <div class="col-md-6">
        <x-adminlte-card title="Spending by Category" theme="light">
            <canvas id="categoryChart"></canvas>
        </x-adminlte-card>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <x-adminlte-card title="Recent Transactions" theme="light">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Date</th><th>Title</th><th>Type</th><th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentTransactions as $tx)
                        <tr>
                            <td>{{ $tx->date }}</td>
                            <td>{{ $tx->title }}</td>
                            <td>
                                <span class="badge {{ $tx->type == 'income' ? 'bg-success' : 'bg-danger' }}">
                                    {{ ucfirst($tx->type) }}
                                </span>
                            </td>
                            <td>₹{{ number_format($tx->amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-adminlte-card>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const monthlyChart = new Chart(document.getElementById('monthlyChart'), {
    type: 'line',
    data: {
        labels: {!! json_encode($chartLabels) !!},
        datasets: [
            {
                label: 'Income',
                data: {!! json_encode($chartIncome) !!},
                borderColor: 'green',
                fill: false
            },
            {
                label: 'Expenses',
                data: {!! json_encode($chartExpenses) !!},
                borderColor: 'red',
                fill: false
            }
        ]
    }
});

const categoryChart = new Chart(document.getElementById('categoryChart'), {
    type: 'pie',
    data: {
        labels: {!! json_encode($categoryLabels) !!},
        datasets: [{
            data: {!! json_encode($categoryData) !!},
            backgroundColor: ['#4CAF50', '#F44336', '#FFC107', '#2196F3', '#9C27B0']
        }]
    }
});
</script>
@stop
