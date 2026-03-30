@extends('layouts.app')

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">View Financial Report</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.financialReportIndex') }}">Financial Reports</a></li>
                    <li class="breadcrumb-item active">View</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">Report Details</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.financialReportIndex') }}" class="btn btn-secondary btn-sm">
                        <i class="mdi mdi-arrow-left"></i> Back
                    </a>
                    <a href="{{ route('admin.financialReportEdit', $financialReport) }}" class="btn btn-primary btn-sm">
                        <i class="mdi mdi-pencil"></i> Edit
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Report Period -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h5 class="border-bottom pb-2 mb-3">Report Period</h5>
                    </div>
                    <div class="col-md-6">
                        <strong>Start Date:</strong>
                        <p class="text-muted">{{ $financialReport->start_date->format('d M Y') }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>End Date:</strong>
                        <p class="text-muted">{{ $financialReport->end_date->format('d M Y') }}</p>
                    </div>
                </div>

                <!-- Financial Summary -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h5 class="border-bottom pb-2 mb-3">Financial Summary</h5>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-info text-white">
                            <span class="info-box-icon"><i class="mdi mdi-cash-multiple"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Sales</span>
                                <span class="info-box-number">৳{{ number_format($financialReport->total_sales, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-success text-white">
                            <span class="info-box-icon"><i class="mdi mdi-cash-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Actual Collected</span>
                                <span class="info-box-number">৳{{ number_format($financialReport->actual_collected_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-warning text-white">
                            <span class="info-box-icon"><i class="mdi mdi-clock-alert"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Amount to Collect</span>
                                <span class="info-box-number">৳{{ number_format($financialReport->outstanding_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-primary text-white">
                            <span class="info-box-icon"><i class="mdi mdi-percent"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Collection %</span>
                                <span class="info-box-number">{{ number_format($financialReport->collection_percentage, 2) }}%</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-warning text-white">
                            <span class="info-box-icon"><i class="mdi mdi-cart"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Purchase</span>
                                <span class="info-box-number">৳{{ number_format($financialReport->total_purchase, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-danger text-white">
                            <span class="info-box-icon"><i class="mdi mdi-currency-usd-off"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Expense</span>
                                <span class="info-box-number">৳{{ number_format($financialReport->total_expense, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-secondary text-white">
                            <span class="info-box-icon"><i class="mdi mdi-sale"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Discount Amount</span>
                                <span class="info-box-number">৳{{ number_format($financialReport->discount_amount, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-dark text-white">
                            <span class="info-box-icon"><i class="mdi mdi-currency-usd-off"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Damage</span>
                                <span class="info-box-number">৳{{ number_format($financialReport->total_damage, 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box bg-dark text-white">
                            <span class="info-box-icon"><i class="mdi mdi-currency-usd-off"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Total Lost</span>
                                <span class="info-box-number">৳{{ number_format($financialReport->total_lost, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Costs -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h5 class="border-bottom pb-2 mb-3">Additional Costs</h5>
                    </div>
                    <div class="col-md-6">
                        <strong>Total Lost Amount:</strong>
                        <p class="text-muted">৳{{ number_format($financialReport->total_lost_amount, 2) }}</p>
                    </div>
                    <div class="col-md-6">
                        <strong>Total Damage Amount:</strong>
                        <p class="text-muted">৳{{ number_format($financialReport->total_damage_amount, 2) }}</p>
                    </div>
                </div>

                <!-- Profit Summary -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h5 class="border-bottom pb-2 mb-3">Profit Summary</h5>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box bg-success">
                            <span class="info-box-icon"><i class="mdi mdi-chart-line"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Current Profit (Based on Collected)</span>
                                <span class="info-box-number">৳{{ number_format($financialReport->total_profit, 2) }}</span>
                                <small class="text-muted">Actual collected - all expenses</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-box bg-primary">
                            <span class="info-box-icon"><i class="mdi mdi-cash-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Expected Profit (If All Collected)</span>
                                <span class="info-box-number">৳{{ number_format($financialReport->expected_profit, 2) }}</span>
                                <small class="text-muted">Total sales - all expenses</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profit Distribution -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h5 class="border-bottom pb-2 mb-3">Profit Distribution</h5>
                    </div>
                    <div class="col-md-4">
                        <strong>Profit for Shareholders:</strong>
                        <p class="text-muted">৳{{ number_format($financialReport->profit_for_shareholders, 2) }}</p>
                    </div>
                    <div class="col-md-4">
                        <strong>Profit for Sadaqah:</strong>
                        <p class="text-muted">৳{{ number_format($financialReport->profit_for_sadaqah, 2) }}</p>
                    </div>
                    <div class="col-md-4">
                        <strong>Profit to Retain:</strong>
                        <p class="text-muted">৳{{ number_format($financialReport->profit_to_retain, 2) }}</p>
                    </div>
                </div>

                <!-- Remarks -->
                @if($financialReport->remarks)
                <div class="row mb-4">
                    <div class="col-md-12">
                        <h5 class="border-bottom pb-2 mb-3">Remarks</h5>
                        <p class="text-muted">{{ $financialReport->remarks }}</p>
                    </div>
                </div>
                @endif

                <!-- Metadata -->
                <div class="row">
                    <div class="col-md-12">
                        <h5 class="border-bottom pb-2 mb-3">Additional Information</h5>
                    </div>
                    <div class="col-md-3">
                        <strong>Created By:</strong>
                        <p class="text-muted">{{ $financialReport->creator->name ?? 'System' }}</p>
                    </div>
                    <div class="col-md-3">
                        <strong>Created At:</strong>
                        <p class="text-muted">{{ $financialReport->created_at->format('d M Y h:i A') }}</p>
                    </div>
                    <div class="col-md-3">
                        <strong>Last Updated:</strong>
                        <p class="text-muted">{{ $financialReport->updated_at->format('d M Y h:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
