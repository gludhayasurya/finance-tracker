@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Finance Dashboard</h1>
        <form method="GET" action="{{ route('dashboard') }}" class="d-flex">
            <select name="year" class="form-select me-2">
                @foreach ($availableYears as $yearOption)
                    <option value="{{ $yearOption }}" {{ $yearOption == $selectedYear ? 'selected' : '' }}>
                        {{ $yearOption }}
                    </option>
                @endforeach
            </select>
            <select name="month" class="form-select me-2">
                <option value="">All Months</option>
                @foreach ($availableMonths as $key => $monthName)
                    <option value="{{ $key }}" {{ $key == $selectedMonth ? 'selected' : '' }}>
                        {{ $monthName }}
                    </option>
                @endforeach
            </select>
            <button class="btn btn-primary" type="submit">Filter</button>
        </form>
    </div>
@stop

@section('content')
    <div class="row">
        <x-kpi-card icon="wallet" label="Total Balance" value="{{ $totalBalance }}" color="primary"/>
        <x-kpi-card icon="arrow-down" label="Total Income" value="{{ $totalIncome }}" color="success"/>
        <x-kpi-card icon="arrow-up" label="Total Expenses" value="{{ $totalExpenses }}" color="danger"/>
    </div>

    <div class="row">
        <div class="col-md-12">
            <x-adminlte-card title="Income vs Expenses (Monthly)" theme="light" class="shadow-sm border-0">
                <canvas id="monthlyChart" height="130"></canvas>
            </x-adminlte-card>
        </div>
    </div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($months) !!},
            datasets: [
                {
                    label: 'Income',
                    data: {!! json_encode($monthlyIncomes) !!},
                    backgroundColor: 'rgba(40, 167, 69, 0.6)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Expenses',
                    data: {!! json_encode($monthlyExpenses) !!},
                    backgroundColor: 'rgba(220, 53, 69, 0.6)',
                    borderColor: 'rgba(220, 53, 69, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@stop

@section('css')
<style>
.card h6 { font-size: 0.9rem; }
.card h4 { font-size: 1.4rem; font-weight: bold; }
</style>
@stop
