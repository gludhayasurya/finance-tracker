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

    /* Custom Dropdown Styles */
    .custom-dropdown {
        position: relative;
        display: inline-block;
        margin-right: 15px;
        margin-bottom: 10px;
    }

    .dropdown-toggle {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        padding: 12px 20px;
        border-radius: 10px;
        color: white;
        font-weight: 500;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: all 0.3s ease;
        min-width: 180px;
        justify-content: space-between;
    }

    .dropdown-toggle:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    .dropdown-toggle.entry-type {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .dropdown-toggle.entry-type:hover {
        box-shadow: 0 5px 15px rgba(17, 153, 142, 0.4);
    }

    .dropdown-menu {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        z-index: 1000;
        max-height: 300px;
        overflow-y: auto;
        opacity: 0;
        transform: translateY(-10px);
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .dropdown-menu.show {
        opacity: 1;
        transform: translateY(0);
        visibility: visible;
    }

    .dropdown-item {
        padding: 12px 20px;
        color: #333;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: background-color 0.3s ease;
        border-bottom: 1px solid #f0f0f0;
    }

    .dropdown-item:hover {
        background-color: #f8f9fa;
        color: #333;
        text-decoration: none;
    }

    .dropdown-item.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .dropdown-item:last-child {
        border-bottom: none;
    }

    .dropdown-icon {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        color: white;
        flex-shrink: 0;
    }

    .filters-section {
        background: white;
        padding: 20px;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        margin-bottom: 30px;
    }

    .filters-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .apply-filters-btn {
        background: linear-gradient(135deg, #ff5f6d 0%, #ffc371 100%);
        border: none;
        padding: 12px 25px;
        border-radius: 10px;
        color: white;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-left: 15px;
    }

    .apply-filters-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(255, 95, 109, 0.4);
    }

    @media (max-width: 768px) {
        .custom-dropdown {
            width: 100%;
            margin-right: 0;
            margin-bottom: 15px;
        }
        
        .dropdown-toggle {
            width: 100%;
        }
        
        .apply-filters-btn {
            width: 100%;
            margin-left: 0;
            margin-top: 15px;
        }
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

    <!-- Filters Section -->
    <div class="filters-section">
        <div class="filters-title">
            <i class="fas fa-filter"></i>
            Filter Dashboard Data
        </div>
        <form method="GET" action="{{ route('dashboard') }}" id="filterForm">
            <div class="row align-items-end">
                <div class="col-md-4">
                    <!-- Bank Dropdown -->
                    <div class="custom-dropdown">
                        <button type="button" class="dropdown-toggle" id="bankDropdown">
                            @if($data['selectedBankId'] === 'all')
                                <span><i class="fas fa-university"></i> All Banks</span>
                            @else
                                @php
                                    $selectedBank = $data['banks']->where('id', $data['selectedBankId'])->first();
                                @endphp
                                <span>
                                    <i class="{{ $selectedBank->fa_icon ?? 'fas fa-university' }}" style="color: {{ $selectedBank->icon_color ?? '#667eea' }}"></i>
                                    {{ $selectedBank->name ?? 'Unknown Bank' }}
                                </span>
                            @endif
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu" id="bankMenu">
                            <a href="#" class="dropdown-item {{ $data['selectedBankId'] === 'all' ? 'active' : '' }}" data-value="all">
                                <div class="dropdown-icon" style="background: linear-gradient(135deg, #667eea, #764ba2)">
                                    <i class="fas fa-university"></i>
                                </div>
                                <span>All Banks</span>
                            </a>
                            @foreach($data['banks'] as $bank)
                                <a href="#" class="dropdown-item {{ $data['selectedBankId'] == $bank->id ? 'active' : '' }}" data-value="{{ $bank->id }}">
                                    <div class="dropdown-icon" style="background-color: {{ $bank->icon_color ?? '#667eea' }}">
                                        <i class="{{ $bank->fa_icon ?? 'fas fa-university' }}"></i>
                                    </div>
                                    <span>{{ $bank->name }}</span>
                                </a>
                            @endforeach
                        </div>
                        <input type="hidden" name="bank_id" value="{{ $data['selectedBankId'] }}" id="selectedBankId">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <!-- Entry Type Dropdown -->
                    <div class="custom-dropdown">
                        <button type="button" class="dropdown-toggle entry-type" id="entryTypeDropdown">
                            <span>
                                <i class="fas fa-{{ $data['entryType'] === 'manual' ? 'edit' : 'file-alt' }}"></i>
                                {{ $data['entryType'] === 'manual' ? 'Manual Entries' : 'Statement Entries' }}
                            </span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu" id="entryTypeMenu">
                            <a href="#" class="dropdown-item {{ $data['entryType'] === 'statement' ? 'active' : '' }}" data-value="statement">
                                <div class="dropdown-icon" style="background: linear-gradient(135deg, #11998e, #38ef7d)">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                                <span>Statement Entries</span>
                            </a>
                            <a href="#" class="dropdown-item {{ $data['entryType'] === 'manual' ? 'active' : '' }}" data-value="manual">
                                <div class="dropdown-icon" style="background: linear-gradient(135deg, #ff5f6d, #ffc371)">
                                    <i class="fas fa-edit"></i>
                                </div>
                                <span>Manual Entries</span>
                            </a>
                        </div>
                        <input type="hidden" name="entry_type" value="{{ $data['entryType'] }}" id="selectedEntryType">
                    </div>
                </div>
                
                <div class="col-md-4">
                    <button type="submit" class="apply-filters-btn">
                        <i class="fas fa-sync-alt me-2"></i>
                        Apply Filters
                    </button>
                </div>
            </div>
        </form>
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
    // Global variables for charts
    let monthlyTrendChart = null;
    let expensePieChart = null;
    let weeklyFlowChart = null;
    
    // Dropdown functionality with AJAX
    const dropdowns = document.querySelectorAll('.custom-dropdown');
    
    dropdowns.forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        const menu = dropdown.querySelector('.dropdown-menu');
        const hiddenInput = dropdown.querySelector('input[type="hidden"]');
        const items = menu.querySelectorAll('.dropdown-item');
        
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Close other dropdowns
            dropdowns.forEach(otherDropdown => {
                if (otherDropdown !== dropdown) {
                    otherDropdown.querySelector('.dropdown-menu').classList.remove('show');
                }
            });
            
            menu.classList.toggle('show');
        });
        
        items.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                
                const value = this.getAttribute('data-value');
                const text = this.querySelector('span').textContent;
                const iconElement = this.querySelector('i');
                const icon = iconElement ? iconElement.outerHTML : '';
                
                // Update hidden input
                hiddenInput.value = value;
                
                // Update toggle button
                const toggleSpan = toggle.querySelector('span');
                if (toggleSpan) {
                    toggleSpan.innerHTML = icon + ' ' + text;
                }
                
                // Update active state
                items.forEach(i => i.classList.remove('active'));
                this.classList.add('active');
                
                // Close dropdown
                menu.classList.remove('show');
                
                // Trigger AJAX update
                updateDashboard();
            });
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        dropdowns.forEach(dropdown => {
            dropdown.querySelector('.dropdown-menu').classList.remove('show');
        });
    });

    // AJAX function to update dashboard
    function updateDashboard() {
        const bankId = document.getElementById('selectedBankId').value;
        const entryType = document.getElementById('selectedEntryType').value;
        
        // Show loading state
        showLoadingState();
        
        // Make AJAX request
        fetch(`{{ route('dashboard') }}?bank_id=${bankId}&entry_type=${entryType}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            updateDashboardData(data);
            hideLoadingState();
        })
        .catch(error => {
            console.error('Error updating dashboard:', error);
            hideLoadingState();
            showErrorMessage('Failed to update dashboard. Please try again.');
        });
    }
    
    // Show loading state
    function showLoadingState() {
        const loadingOverlay = document.createElement('div');
        loadingOverlay.id = 'loading-overlay';
        loadingOverlay.innerHTML = `
            <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
                        background: rgba(255,255,255,0.8); z-index: 9999; 
                        display: flex; align-items: center; justify-content: center;">
                <div style="text-align: center;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <div style="margin-top: 10px; font-weight: 500;">Updating Dashboard...</div>
                </div>
            </div>
        `;
        document.body.appendChild(loadingOverlay);
    }
    
    // Hide loading state
    function hideLoadingState() {
        const loadingOverlay = document.getElementById('loading-overlay');
        if (loadingOverlay) {
            loadingOverlay.remove();
        }
    }
    
    // Show error message
    function showErrorMessage(message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger alert-dismissible fade show';
        alertDiv.style.position = 'fixed';
        alertDiv.style.top = '20px';
        alertDiv.style.right = '20px';
        alertDiv.style.zIndex = '10000';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.body.appendChild(alertDiv);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
    
    // Update dashboard data
    function updateDashboardData(data) {
        try {
            // Update metric cards
            updateMetricCards(data);
            
            // Update cash flow section
            updateCashFlowSection(data);
            
            // Update charts
            updateCharts(data);
            
            // Update recent transactions
            updateRecentTransactions(data);
            
            // Update account balances
            updateAccountBalances(data);
            
            // Update expense categories list
            updateExpenseCategories(data);
            
        } catch (error) {
            console.error('Error updating dashboard data:', error);
            showErrorMessage('Error updating dashboard display.');
        }
    }
    
    // Update metric cards
    function updateMetricCards(data) {
        const cards = {
            '.dashboard-card.balance .card-value': data.totalBalance || 0,
            '.dashboard-card.income .card-value': data.totalIncome || 0,
            '.dashboard-card.expense .card-value': data.totalExpenses || 0,
            '.dashboard-card.accounts .card-value': data.activeAccounts || 0
        };
        
        Object.entries(cards).forEach(([selector, value]) => {
            const element = document.querySelector(selector);
            if (element) {
                if (selector.includes('accounts')) {
                    element.textContent = value;
                } else {
                    element.textContent = '₹' + parseFloat(value).toLocaleString('en-IN', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
            }
        });
    }
    
    // Update cash flow section
    function updateCashFlowSection(data) {
        const cashFlow = data.cashFlow || {};
        
        // Current month cash flow
        const currentElement = document.querySelector('.col-md-4:first-child .h4');
        if (currentElement && cashFlow.current !== undefined) {
            currentElement.textContent = '₹' + Math.abs(cashFlow.current).toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            currentElement.style.color = cashFlow.current >= 0 ? '#11998e' : '#ff5f6d';
        }
        
        // Previous month cash flow
        const prevElement = document.querySelector('.col-md-4:nth-child(2) .h5');
        if (prevElement && cashFlow.previous !== undefined) {
            prevElement.textContent = '₹' + Math.abs(cashFlow.previous).toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
        
        // Change percentage
        const changeElement = document.querySelector('.col-md-4:last-child .h5');
        if (changeElement && cashFlow.change_percent !== undefined) {
            const changePercent = Math.abs(cashFlow.change_percent);
            changeElement.innerHTML = `
                <i class="fas fa-${cashFlow.change_percent >= 0 ? 'arrow-up' : 'arrow-down'} me-1"></i>
                ${changePercent}%
            `;
            changeElement.className = `h5 mb-1 ${cashFlow.change_percent >= 0 ? 'trend-up' : 'trend-down'}`;
        }
    }
    
    // Update charts
    function updateCharts(data) {
        // Monthly Trend Chart
        if (monthlyTrendChart) {
            monthlyTrendChart.destroy();
        }
        
        const monthlyCtx = document.getElementById('monthlyTrendChart');
        if (monthlyCtx && data.monthlyTrend && data.monthlyTrend.months) {
            monthlyTrendChart = new Chart(monthlyCtx, {
                type: 'line',
                data: {
                    labels: data.monthlyTrend.months,
                    datasets: [{
                        label: 'Income',
                        data: data.monthlyTrend.income || [],
                        borderColor: '#11998e',
                        backgroundColor: 'rgba(17, 153, 142, 0.1)',
                        fill: true,
                        tension: 0.4
                    }, {
                        label: 'Expenses',
                        data: data.monthlyTrend.expenses || [],
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
        if (expensePieChart) {
            expensePieChart.destroy();
        }
        
        const expenseCtx = document.getElementById('expensePieChart');
        if (expenseCtx && data.expensesByCategory && data.expensesByCategory.length > 0) {
            expensePieChart = new Chart(expenseCtx, {
                type: 'doughnut',
                data: {
                    labels: data.expensesByCategory.map(item => item.category || 'Unknown'),
                    datasets: [{
                        data: data.expensesByCategory.map(item => parseFloat(item.amount) || 0),
                        backgroundColor: [
                            '#667eea',
                            '#11998e',
                            '#ff5f6d',
                            '#ffc371',
                            '#4facfe',
                            '#00f2fe',
                            '#a8edea'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    cutout: '50%'
                }
            });
        }
        
        // Weekly Flow Chart
        if (weeklyFlowChart) {
            weeklyFlowChart.destroy();
        }
        
        const weeklyCtx = document.getElementById('weeklyFlowChart');
        if (weeklyCtx && data.incomeVsExpense && data.incomeVsExpense.days) {
            weeklyFlowChart = new Chart(weeklyCtx, {
                type: 'bar',
                data: {
                    labels: data.incomeVsExpense.days,
                    datasets: [{
                        label: 'Income',
                        data: data.incomeVsExpense.income || [],
                        backgroundColor: 'rgba(17, 153, 142, 0.8)',
                        borderColor: '#11998e',
                        borderWidth: 1
                    }, {
                        label: 'Expenses',
                        data: data.incomeVsExpense.expenses || [],
                        backgroundColor: 'rgba(255, 95, 109, 0.8)',
                        borderColor: '#ff5f6d',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
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
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
        }
    }
    
    // Update recent transactions
    function updateRecentTransactions(data) {
        const transactionsContainer = document.querySelector('.col-lg-7 .chart-container');
        if (!transactionsContainer) return;
        
        const transactionsList = transactionsContainer.querySelector('.recent-transaction')?.parentNode;
        if (!transactionsList) return;
        
        // Clear existing transactions (except the header)
        const existingTransactions = transactionsList.querySelectorAll('.recent-transaction');
        existingTransactions.forEach(t => t.remove());
        
        if (data.recentTransactions && data.recentTransactions.length > 0) {
            data.recentTransactions.forEach(transaction => {
                const isIncome = (transaction.deposit || 0) > 0;
                const amount = isIncome ? transaction.deposit : transaction.withdrawal;
                const transactionEl = document.createElement('div');
                transactionEl.className = 'recent-transaction';
                transactionEl.innerHTML = `
                    <div class="transaction-icon ${isIncome ? 'income' : 'expense'}">
                        <i class="fas fa-${isIncome ? 'plus' : 'minus'}"></i>
                    </div>
                    <div class="transaction-details">
                        <div class="fw-bold">${transaction.particulars || transaction.extracted_particular || 'Unknown Transaction'}</div>
                        <div class="text-muted small">
                            ${transaction.bank?.name || 'Unknown Bank'} • ${new Date(transaction.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}
                        </div>
                    </div>
                    <div class="transaction-amount ${isIncome ? 'income' : 'expense'}">
                        ${isIncome ? '+' : '-'}₹${parseFloat(amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                    </div>
                `;
                transactionsList.appendChild(transactionEl);
            });
        } else {
            const noDataEl = document.createElement('div');
            noDataEl.className = 'text-center py-4 text-muted';
            noDataEl.innerHTML = `
                <i class="fas fa-inbox fa-3x mb-3"></i>
                <p>No recent transactions found</p>
            `;
            transactionsList.appendChild(noDataEl);
        }
    }
    
    // Update account balances
    function updateAccountBalances(data) {
        const accountsContainer = document.querySelector('.col-lg-5 .chart-container');
        if (!accountsContainer) return;
        
        const accountsList = accountsContainer.querySelector('.account-balance-item')?.parentNode;
        if (!accountsList) return;
        
        // Clear existing accounts (except the header)
        const existingAccounts = accountsList.querySelectorAll('.account-balance-item');
        existingAccounts.forEach(a => a.remove());
        
        if (data.accountBalances && data.accountBalances.length > 0) {
            const maxBalance = Math.max(...data.accountBalances.map(acc => acc.current_balance || 0));
            
            data.accountBalances.forEach(account => {
                const percentage = maxBalance > 0 ? ((account.current_balance || 0) / maxBalance) * 100 : 0;
                const accountEl = document.createElement('div');
                accountEl.className = 'account-balance-item';
                accountEl.innerHTML = `
                    <div class="account-icon" style="background-color: ${account.icon_color || '#667eea'}">
                        <i class="${account.fa_icon || 'fas fa-university'}"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="fw-bold">${account.name || 'Unknown Account'}</div>
                        <div class="progress-bar">
                            <div class="progress">
                                <div class="progress-bar" role="progressbar"
                                     style="width: ${percentage}%; background: linear-gradient(90deg, ${account.icon_color || '#667eea'}, ${account.icon_color || '#764ba2'})">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="fw-bold text-end">
                        ₹${parseFloat(account.current_balance || 0).toLocaleString('en-IN', { minimumFractionDigits: 2 })}
                    </div>
                `;
                accountsList.appendChild(accountEl);
            });
        } else {
            const noDataEl = document.createElement('div');
            noDataEl.className = 'text-center py-4 text-muted';
            noDataEl.innerHTML = `
                <i class="fas fa-university fa-3x mb-3"></i>
                <p>No accounts found</p>
            `;
            accountsList.appendChild(noDataEl);
        }
    }
    
    // Update expense categories list
    function updateExpenseCategories(data) {
        const expenseCategoriesContainer = document.querySelector('.col-lg-4 .mt-3');
        if (!expenseCategoriesContainer) return;
        
        expenseCategoriesContainer.innerHTML = '';
        
        if (data.topExpenseCategories && data.topExpenseCategories.length > 0) {
            data.topExpenseCategories.forEach(category => {
                const categoryEl = document.createElement('div');
                categoryEl.className = 'd-flex justify-content-between align-items-center mb-2';
                categoryEl.innerHTML = `
                    <span class="text-muted">${category.category || 'Unknown'}</span>
                    <span class="fw-bold">₹${parseFloat(category.amount || 0).toLocaleString('en-IN')}</span>
                `;
                expenseCategoriesContainer.appendChild(categoryEl);
            });
        } else {
            expenseCategoriesContainer.innerHTML = `
                <div class="text-center py-3 text-muted">
                    <p>No expense data available</p>
                </div>
            `;
        }
    }

    // Initial chart setup
    try {
        // Safely get data with fallbacks
        const monthlyTrendData = @json($data['monthlyTrend'] ?? ['months' => [], 'income' => [], 'expenses' => []]);
        const expensesData = @json($data['expensesByCategory'] ?? []);
        const incomeVsExpenseData = @json($data['incomeVsExpense'] ?? ['days' => [], 'income' => [], 'expenses' => []]);

        // Monthly Trend Chart
        const monthlyCtx = document.getElementById('monthlyTrendChart');
        if (monthlyCtx && monthlyTrendData.months && monthlyTrendData.months.length > 0) {
            monthlyTrendChart = new Chart(monthlyCtx, {
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
            expensePieChart = new Chart(expenseCtx, {
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
                            '#00f2fe',
                            '#a8edea'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    cutout: '50%'
                }
            });
        }

        // Weekly Flow Chart
        const weeklyCtx = document.getElementById('weeklyFlowChart');
        if (weeklyCtx && incomeVsExpenseData.days && incomeVsExpenseData.days.length > 0) {
            weeklyFlowChart = new Chart(weeklyCtx, {
                type: 'bar',
                data: {
                    labels: incomeVsExpenseData.days,
                    datasets: [{
                        label: 'Income',
                        data: incomeVsExpenseData.income || [],
                        backgroundColor: 'rgba(17, 153, 142, 0.8)',
                        borderColor: '#11998e',
                        borderWidth: 1
                    }, {
                        label: 'Expenses',
                        data: incomeVsExpenseData.expenses || [],
                        backgroundColor: 'rgba(255, 95, 109, 0.8)',
                        borderColor: '#ff5f6d',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top'
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
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    }
                }
            });
        }

    } catch (error) {
        console.error('Error initializing charts:', error);
        
        // Show fallback message for charts that failed to load
        const chartContainers = document.querySelectorAll('.chart-wrapper');
        chartContainers.forEach(container => {
            const canvas = container.querySelector('canvas');
            if (canvas) {
                try {
                    const chartInstance = Chart.getChart(canvas);
                    if (!chartInstance) {
                        container.innerHTML = `
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-chart-line fa-3x mb-3"></i>
                                <p>Chart data is not available</p>
                            </div>
                        `;
                    }
                } catch (e) {
                    container.innerHTML = `
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-chart-line fa-3x mb-3"></i>
                            <p>Chart data is not available</p>
                        </div>
                    `;
                }
            }
        });
    }
});

// Additional utility functions for dashboard
function formatCurrency(amount) {
    return '₹' + parseFloat(amount).toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function animateCounter(element, targetValue, duration = 1000) {
    const startValue = 0;
    const increment = targetValue / (duration / 16);
    let currentValue = startValue;
    
    const timer = setInterval(() => {
        currentValue += increment;
        if (currentValue >= targetValue) {
            currentValue = targetValue;
            clearInterval(timer);
        }
        element.textContent = formatCurrency(currentValue);
    }, 16);
}

// Counter animation (separate to avoid conflicts)
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const counters = document.querySelectorAll('.card-value');
        counters.forEach(counter => {
            const originalText = counter.textContent;
            const value = originalText.replace(/[₹,]/g, '');
            if (!isNaN(value) && value !== '' && parseFloat(value) > 0) {
                counter.setAttribute('data-original-value', originalText);
                if (!counter.hasAttribute('data-animated')) {
                    counter.setAttribute('data-animated', 'true');
                    animateCounter(counter, parseFloat(value), 1500);
                }
            }
        });
    }, 500);
});

// Handle responsive behavior
window.addEventListener('resize', function() {
    Chart.instances.forEach(function(chart) {
        if (chart && typeof chart.resize === 'function') {
            chart.resize();
        }
    });
});

// Refresh dashboard data
function refreshDashboard() {
    window.location.reload();
}
</script>
@endsection