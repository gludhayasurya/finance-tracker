@extends('adminlte::page')

@section('title', 'Dashboard')

@section('css')
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
<style>
    .dashboard-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 15px;
        padding: 25px;
        color: white;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }

    .dashboard-card.income {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .dashboard-card.expense {
        background: linear-gradient(135deg, #ff5f6d 0%, #ffc371 100%);
    }

    .dashboard-card.balance {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .dashboard-card.accounts {
        background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        color: #333;
    }

    .card-icon {
        font-size: 2.5rem;
        opacity: 0.8;
        margin-bottom: 15px;
    }

    .card-value {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 5px;
    }

    .card-label {
        font-size: 0.9rem;
        opacity: 0.9;
    }

    .chart-container {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        margin-bottom: 30px;
        position: relative;
        height: auto;
    }

    .chart-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 20px;
    }

    .chart-wrapper {
        position: relative;
        height: 300px;
        width: 100%;
    }

    .chart-wrapper canvas {
        max-height: 100% !important;
    }

    .recent-transaction {
        display: flex;
        align-items: center;
        padding: 15px;
        border-bottom: 1px solid #f0f0f0;
        transition: background-color 0.3s ease;
    }

    .recent-transaction:hover {
        background-color: #f8f9fa;
    }

    .transaction-icon {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 1.2rem;
        color: white;
    }

    .transaction-icon.income {
        background: linear-gradient(135deg, #11998e, #38ef7d);
    }

    .transaction-icon.expense {
        background: linear-gradient(135deg, #ff5f6d, #ffc371);
    }

    .transaction-details {
        flex: 1;
    }

    .transaction-amount {
        font-weight: 600;
    }

    .transaction-amount.income {
        color: #11998e;
    }

    .transaction-amount.expense {
        color: #ff5f6d;
    }

    .progress-bar {
        height: 8px;
        border-radius: 4px;
        overflow: hidden;
        margin-top: 8px;
    }

    .account-balance-item {
        display: flex;
        align-items: center;
        padding: 15px 0;
        border-bottom: 1px solid #f0f0f0;
    }

    .account-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 1.1rem;
        color: white;
    }

    .trend-up {
        color: #11998e;
    }

    .trend-down {
        color: #ff5f6d;
    }

    .dashboard-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
    }
</style>
@stop

@section('content')
<div class="container-fluid">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="mb-2">
                    <i class="fas fa-chart-line me-3"></i>
                    Finance Dashboard
                </h1>
                <p class="mb-0 opacity-75">Welcome back! Here's your financial overview for {{ date('F Y') }}</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="h5 mb-1">{{ date('l, F j, Y') }}</div>
                <div class="opacity-75">Last updated: {{ now()->format('h:i A') }}</div>
            </div>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="dashboard-card balance">
                <div class="card-icon">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="card-value">₹{{ number_format($data['totalBalance'] ?? 0, 2) }}</div>
                <div class="card-label">Total Balance</div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="dashboard-card income">
                <div class="card-icon">
                    <i class="fas fa-arrow-up"></i>
                </div>
                <div class="card-value">₹{{ number_format($data['totalIncome'] ?? 0, 2) }}</div>
                <div class="card-label">Monthly Income</div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="dashboard-card expense">
                <div class="card-icon">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div class="card-value">₹{{ number_format($data['totalExpenses'] ?? 0, 2) }}</div>
                <div class="card-label">Monthly Expenses</div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-4">
            <div class="dashboard-card accounts">
                <div class="card-icon">
                    <i class="fas fa-university"></i>
                </div>
                <div class="card-value">{{ $data['activeAccounts'] ?? 0 }}</div>
                <div class="card-label">Active Accounts</div>
            </div>
        </div>
    </div>

    <!-- Cash Flow Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="chart-container">
                <div class="row">
                    <div class="col-md-8">
                        <h5 class="chart-title">
                            <i class="fas fa-exchange-alt me-2"></i>
                            Net Cash Flow
                        </h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="text-center p-3">
                                    <div class="h4 mb-1" style="color: {{ ($data['cashFlow']['current'] ?? 0) >= 0 ? '#11998e' : '#ff5f6d' }}">
                                        ₹{{ number_format(abs($data['cashFlow']['current'] ?? 0), 2) }}
                                    </div>
                                    <div class="text-muted">This Month</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3">
                                    <div class="h5 mb-1">₹{{ number_format(abs($data['cashFlow']['previous'] ?? 0), 2) }}</div>
                                    <div class="text-muted">Last Month</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3">
                                    <div class="h5 mb-1 {{ ($data['cashFlow']['change_percent'] ?? 0) >= 0 ? 'trend-up' : 'trend-down' }}">
                                        <i class="fas fa-{{ ($data['cashFlow']['change_percent'] ?? 0) >= 0 ? 'arrow-up' : 'arrow-down' }} me-1"></i>
                                        {{ abs($data['cashFlow']['change_percent'] ?? 0) }}%
                                    </div>
                                    <div class="text-muted">Change</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center justify-content-center h-100">
                            <div class="text-center">
                                <i class="fas fa-chart-pie" style="font-size: 4rem; color: #667eea; opacity: 0.7;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Monthly Trend Chart -->
        <div class="col-lg-8 mb-4">
            <div class="chart-container">
                <h5 class="chart-title">
                    <i class="fas fa-chart-line me-2"></i>
                    Income vs Expenses Trend
                </h5>
                <div class="chart-wrapper">
                    <canvas id="monthlyTrendChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Expense Categories -->
        <div class="col-lg-4 mb-4">
            <div class="chart-container">
                <h5 class="chart-title">
                    <i class="fas fa-chart-pie me-2"></i>
                    Top Expense Categories
                </h5>
                <div class="chart-wrapper">
                    <canvas id="expensePieChart"></canvas>
                </div>

                <div class="mt-3">
                    @if(isset($data['topExpenseCategories']) && count($data['topExpenseCategories']) > 0)
                        @foreach($data['topExpenseCategories'] as $category)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">{{ $category->category ?? 'Unknown' }}</span>
                            <span class="fw-bold">₹{{ number_format($category->amount ?? 0, 0) }}</span>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-3 text-muted">
                            <p>No expense data available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Transactions -->
        <div class="col-lg-7 mb-4">
            <div class="chart-container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="chart-title mb-0">
                        <i class="fas fa-history me-2"></i>
                        Recent Transactions
                    </h5>
                    <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
                </div>

                @if(isset($data['recentTransactions']) && count($data['recentTransactions']) > 0)
                    @foreach($data['recentTransactions'] as $transaction)
                    <div class="recent-transaction">
                        <div class="transaction-icon {{ ($transaction->deposit ?? 0) > 0 ? 'income' : 'expense' }}">
                            <i class="fas fa-{{ ($transaction->deposit ?? 0) > 0 ? 'plus' : 'minus' }}"></i>
                        </div>
                        <div class="transaction-details">
                            <div class="fw-bold">{{ $transaction->particulars ?? $transaction->extracted_particular ?? 'Unknown Transaction' }}</div>
                            <div class="text-muted small">
                                {{ $transaction->bank->name ?? 'Unknown Bank' }} • {{ isset($transaction->date) ? $transaction->date->format('M d, Y') : 'Unknown Date' }}
                            </div>
                        </div>
                        <div class="transaction-amount {{ ($transaction->deposit ?? 0) > 0 ? 'income' : 'expense' }}">
                            {{ ($transaction->deposit ?? 0) > 0 ? '+' : '-' }}₹{{ number_format(($transaction->deposit ?? 0) > 0 ? ($transaction->deposit ?? 0) : ($transaction->withdrawal ?? 0), 2) }}
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-inbox fa-3x mb-3"></i>
                        <p>No recent transactions found</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Account Balances -->
        <div class="col-lg-5 mb-4">
            <div class="chart-container">
                <h5 class="chart-title">
                    <i class="fas fa-university me-2"></i>
                    Account Balances
                </h5>

                @if(isset($data['accountBalances']) && count($data['accountBalances']) > 0)
                    @foreach($data['accountBalances'] as $account)
                    <div class="account-balance-item">
                        <div class="account-icon" style="background-color: {{ $account->icon_color ?? '#667eea' }}">
                            <i class="{{ $account->fa_icon ?? 'fas fa-university' }}"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold">{{ $account->name ?? 'Unknown Account' }}</div>
                            <div class="progress-bar">
                                @php
                                    $maxBalance = $data['accountBalances']->max('current_balance') ?? 1;
                                    $percentage = $maxBalance > 0 ? (($account->current_balance ?? 0) / $maxBalance) * 100 : 0;
                                @endphp
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar"
                                         style="width: {{ $percentage }}%; background: linear-gradient(90deg, {{ $account->icon_color ?? '#667eea' }}, {{ $account->icon_color ?? '#764ba2' }})">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="fw-bold text-end">
                            ₹{{ number_format($account->current_balance ?? 0, 2) }}
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-university fa-3x mb-3"></i>
                        <p>No accounts found</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Weekly Cash Flow -->
    <div class="row">
        <div class="col-12">
            <div class="chart-container">
                <h5 class="chart-title">
                    <i class="fas fa-chart-bar me-2"></i>
                    Weekly Cash Flow
                </h5>
                <div class="chart-wrapper">
                    <canvas id="weeklyFlowChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    try {
        // Safely get data with fallbacks
        const monthlyTrendData = @json($data['monthlyTrend'] ?? ['months' => [], 'income' => [], 'expenses' => []]);
        const expensesData = @json($data['expensesByCategory'] ?? []);
        const incomeVsExpenseData = @json($data['incomeVsExpense'] ?? ['days' => [], 'income' => [], 'expenses' => []]);

        // Monthly Trend Chart
        const monthlyCtx = document.getElementById('monthlyTrendChart');
        if (monthlyCtx && monthlyTrendData.months && monthlyTrendData.months.length > 0) {
            new Chart(monthlyCtx, {
                type: 'line',
                data: {
                    labels: monthlyTrendData.months,
                    datasets: [{
                        label: 'Income',
                        data: monthlyTrendData.income || [],
                        borderColor: '#11998e',
                        backgroundColor: 'rgba(17, 153, 142, 0.1)',
                        fill: true,
                        tension: 0.4
                    }, {
                        label: 'Expenses',
                        data: monthlyTrendData.expenses || [],
                        borderColor: '#ff5f6d',
                        backgroundColor: 'rgba(255, 95, 109, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₹' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        // Expense Pie Chart
        const expenseCtx = document.getElementById('expensePieChart');
        if (expenseCtx && expensesData && expensesData.length > 0) {
            new Chart(expenseCtx, {
                type: 'doughnut',
                data: {
                    labels: expensesData.map(item => item.category || 'Unknown'),
                    datasets: [{
                        data: expensesData.map(item => parseFloat(item.amount) || 0),
                        backgroundColor: [
                            '#667eea',
                            '#11998e',
                            '#ff5f6d',
                            '#ffc371',
                            '#4facfe',
                            '#a8edea'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }

        // Weekly Flow Chart
        const weeklyCtx = document.getElementById('weeklyFlowChart');
        if (weeklyCtx && incomeVsExpenseData.days && incomeVsExpenseData.days.length > 0) {
            new Chart(weeklyCtx, {
                type: 'bar',
                data: {
                    labels: incomeVsExpenseData.days,
                    datasets: [{
                        label: 'Income',
                        data: incomeVsExpenseData.income || [],
                        backgroundColor: 'rgba(17, 153, 142, 0.8)',
                    }, {
                        label: 'Expenses',
                        data: incomeVsExpenseData.expenses || [],
                        backgroundColor: 'rgba(255, 95, 109, 0.8)',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₹' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

    } catch (error) {
        console.error('Error initializing charts:', error);
        // Hide chart containers if there's an error
        document.querySelectorAll('.chart-wrapper').forEach(wrapper => {
            wrapper.innerHTML = '<div class="text-center py-4 text-muted"><p>Chart data unavailable</p></div>';
        });
    }
});
</script>
@stop
