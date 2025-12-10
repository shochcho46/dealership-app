@extends('layouts.app')

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Capital Overview</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Capital Overview</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h1 class="mt-3 mb-4">Capital Health Monitor</h1>

                <!-- Capital Status Alert -->
                @if($capitalStatus == 'balanced')
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <h5><i class="mdi mdi-check-circle"></i> Capital Status: Balanced</h5>
                        <p class="mb-0">Your capital is well managed. Available capital: <strong>৳{{ number_format($availableCapital, 2) }}</strong></p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @else
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h5><i class="mdi mdi-alert-circle"></i> Capital Status: Deficit</h5>
                        <p class="mb-0">You have a capital deficit of: <strong>৳{{ number_format($capitalDifference, 2) }}</strong></p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Summary Cards Row 1 -->
                <div class="row mb-3">
                    <div class="col-lg-3 col-6">
                        <div class="small-box text-bg-primary">
                            <div class="inner">
                                <h3>৳{{ number_format($totalInvestment, 2) }}</h3>
                                <p>Total Investment</p>
                            </div>
                            <div class="small-box-icon">
                                <i class="mdi mdi-currency-usd"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box text-bg-info">
                            <div class="inner">
                                <h3>৳{{ number_format($bankBalance, 2) }}</h3>
                                <p>Bank Balance</p>
                            </div>
                            <div class="small-box-icon">
                                <i class="mdi mdi-bank"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box text-bg-warning">
                            <div class="inner">
                                <h3>৳{{ number_format($capitalUsed, 2) }}</h3>
                                <p>Capital Used</p>
                            </div>
                            <div class="small-box-icon">
                                <i class="mdi mdi-cash-minus"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box text-bg-{{ $capitalStatus == 'balanced' ? 'success' : 'danger' }}">
                            <div class="inner">
                                <h3>৳{{ number_format($availableCapital, 2) }}</h3>
                                <p>Available Capital</p>
                            </div>
                            <div class="small-box-icon">
                                <i class="mdi mdi-cash-check"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bank Details Card -->
                <div class="card card-primary card-outline mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="mdi mdi-bank"></i> Bank Account Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="info-box bg-success">
                                    <span class="info-box-icon"><i class="mdi mdi-arrow-down-bold"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Credit</span>
                                        <span class="info-box-number">৳{{ number_format($bankCredit, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box bg-danger">
                                    <span class="info-box-icon"><i class="mdi mdi-arrow-up-bold"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Total Debit</span>
                                        <span class="info-box-number">৳{{ number_format($bankDebit, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-box bg-info">
                                    <span class="info-box-icon"><i class="mdi mdi-scale-balance"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Bank Balance</span>
                                        <span class="info-box-number">৳{{ number_format($bankBalance, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Capital Breakdown -->
                <div class="card card-primary card-outline mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="mdi mdi-chart-pie"></i> Capital Allocation Breakdown</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Category</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-end">Percentage</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Total Investment (Source)</strong></td>
                                        <td class="text-end text-primary"><strong>৳{{ number_format($totalInvestment, 2) }}</strong></td>
                                        <td class="text-end">100%</td>
                                    </tr>
                                    <tr class="table-secondary">
                                        <td colspan="3"><strong>Capital Usage:</strong></td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">Assets (Type: Purchase)</td>
                                        <td class="text-end">৳{{ number_format($totalAssets, 2) }}</td>
                                        <td class="text-end">{{ $totalInvestment > 0 ? number_format(($totalAssets / $totalInvestment) * 100, 2) : 0 }}%</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">Stock Value (Available)</td>
                                        <td class="text-end">৳{{ number_format($availableStockValue, 2) }}</td>
                                        <td class="text-end">{{ $totalInvestment > 0 ? number_format(($availableStockValue / $totalInvestment) * 100, 2) : 0 }}%</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">Due Amount (Purchase Price)</td>
                                        <td class="text-end">৳{{ number_format($totalDue, 2) }}</td>
                                        <td class="text-end">{{ $totalInvestment > 0 ? number_format(($totalDue / $totalInvestment) * 100, 2) : 0 }}%</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">Damage Amount</td>
                                        <td class="text-end text-danger">৳{{ number_format($totalDamage, 2) }}</td>
                                        <td class="text-end">{{ $totalInvestment > 0 ? number_format(($totalDamage / $totalInvestment) * 100, 2) : 0 }}%</td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4">Lost Amount</td>
                                        <td class="text-end text-danger">৳{{ number_format($totalLost, 2) }}</td>
                                        <td class="text-end">{{ $totalInvestment > 0 ? number_format(($totalLost / $totalInvestment) * 100, 2) : 0 }}%</td>
                                    </tr>
                                    <tr class="table-warning">
                                        <td><strong>Total Capital Used</strong></td>
                                        <td class="text-end"><strong>৳{{ number_format($capitalUsed, 2) }}</strong></td>
                                        <td class="text-end"><strong>{{ $totalInvestment > 0 ? number_format(($capitalUsed / $totalInvestment) * 100, 2) : 0 }}%</strong></td>
                                    </tr>
                                    <tr class="table-{{ $capitalStatus == 'balanced' ? 'success' : 'danger' }}">
                                        <td><strong>Available Capital (Investment + Bank - Used)</strong></td>
                                        <td class="text-end"><strong>৳{{ number_format($availableCapital, 2) }}</strong></td>
                                        <td class="text-end"><strong>{{ $totalInvestment > 0 ? number_format(($availableCapital / $totalInvestment) * 100, 2) : 0 }}%</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Due Analysis Card -->
                <div class="card card-info card-outline mb-3">
                    <div class="card-header">
                        <h3 class="card-title"><i class="mdi mdi-information"></i> Due Amount Analysis</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <strong>Due Amount (Purchase Price):</strong>
                                <p class="text-muted">৳{{ number_format($totalDue, 2) }}</p>
                                <small class="text-muted">This is the amount customers owe based on purchase cost</small>
                            </div>
                            <div class="col-md-4">
                                <strong>Due Amount (Actual Sale Price):</strong>
                                <p class="text-muted">৳{{ number_format($totalActualDuePrice, 2) }}</p>
                                <small class="text-muted">This is the actual amount customers need to pay</small>
                            </div>
                            <div class="col-md-4">
                                <strong>Expected Profit from Due:</strong>
                                <p class="text-success">৳{{ number_format($totalActualDuePrice - $totalDue, 2) }}</p>
                                <small class="text-muted">Profit margin on outstanding orders</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formula Explanation -->
                <div class="card card-secondary card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="mdi mdi-calculator"></i> Capital Calculation Formula</h3>
                    </div>
                    <div class="card-body">
                        <h5>Formula Used:</h5>
                        <div class="alert alert-light">
                            <code>
                                Available Capital = (Total Investment + Bank Balance) - (Assets + Stock Value + Due Amount + Damage + Lost)
                            </code>
                        </div>
                        <h6>Breakdown:</h6>
                        <ul>
                            <li><strong>Total Investment:</strong> Sum of all investor contributions</li>
                            <li><strong>Bank Balance:</strong> Bank Credit (deposits) - Bank Debit (withdrawals)</li>
                            <li><strong>Assets:</strong> Sum of assets with Type = 1 (Purchase/Investment)</li>
                            <li><strong>Stock Value:</strong> Sum of (Purchase Price × Quantity) for available stock</li>
                            <li><strong>Due Amount:</strong> Outstanding payments from customers (calculated using purchase price)</li>
                            <li><strong>Damage:</strong> Value of damaged products (purchase price based)</li>
                            <li><strong>Lost:</strong> Value of lost products (purchase price based)</li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('custome-css')
<style>
    .info-box {
        min-height: 90px;
    }
    .small-box {
        border-radius: 0.25rem;
    }
</style>
@endpush
