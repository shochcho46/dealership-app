@extends('layouts.app')

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Financial Reports</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Financial Reports</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h1 class="mt-3">Financial Summary Reports</h1>
                    <div class="text-end">
                        <a href="{{ route('admin.financialReportCreate') }}" class="btn btn-primary">
                            <span class="mdi mdi-plus"></span> Add New Report
                        </a>
                    </div>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                        <!-- Filter Section -->
                <div class="card card-primary card-outline mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Filter Reports</h3>
                    </div>
                    <form action="{{ route('admin.financialReportIndex') }}" method="GET">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="filter_start_date">Start Date</label>
                                        <input type="date"
                                               class="form-control"
                                               id="filter_start_date"
                                               name="filter_start_date"
                                               value="{{ request('filter_start_date') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="filter_end_date">End Date</label>
                                        <input type="date"
                                               class="form-control"
                                               id="filter_end_date"
                                               name="filter_end_date"
                                               value="{{ request('filter_end_date') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <div>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="mdi mdi-filter"></i> Filter
                                            </button>
                                            <a href="{{ route('admin.financialReportIndex') }}" class="btn btn-secondary">
                                                <i class="mdi mdi-refresh"></i> Reset
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Summary Cards -->
                <div class="row mb-3">
                    <div class="col-lg-3 col-6">
                        <div class="small-box text-bg-info">
                            <div class="inner">
                                <h3>৳{{ number_format($totals->sum_sales ?? 0, 2) }}</h3>
                                <p>Total Sales</p>
                            </div>
                            <div class="small-box-icon">
                                <i class="mdi mdi-cash-multiple"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box text-bg-warning">
                            <div class="inner">
                                <h3>৳{{ number_format($totals->sum_purchase ?? 0, 2) }}</h3>
                                <p>Total Purchase</p>
                            </div>
                            <div class="small-box-icon">
                                <i class="mdi mdi-cart"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box text-bg-danger">
                            <div class="inner">
                                <h3>৳{{ number_format($totals->sum_expense ?? 0, 2) }}</h3>
                                <p>Total Expense</p>
                            </div>
                            <div class="small-box-icon">
                                <i class="mdi mdi-currency-usd-off"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box text-bg-secondary">
                            <div class="inner">
                                <h3>৳{{ number_format($totals->sum_discount ?? 0, 2) }}</h3>
                                <p>Total Discount</p>
                            </div>
                            <div class="small-box-icon">
                                <i class="mdi mdi-sale"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box text-bg-dark">
                            <div class="inner">
                                <h3>৳{{ number_format($totals->sum_damage ?? 0, 2) }}</h3>
                                <p>Total Damage</p>
                            </div>
                            <div class="small-box-icon">
                                <i class="mdi mdi-currency-usd-off"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box text-bg-dark">
                            <div class="inner">
                                <h3>৳{{ number_format($totals->sum_lost ?? 0, 2) }}</h3>
                                <p>Total Lost</p>
                            </div>
                            <div class="small-box-icon">
                                <i class="mdi mdi-currency-usd-off"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="small-box text-bg-success">
                            <div class="inner">
                                <h3>৳{{ number_format($totals->sum_profit ?? 0, 2) }}</h3>
                                <p>Total Profit</p>
                            </div>
                            <div class="small-box-icon">
                                <i class="mdi mdi-chart-line"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reports Table -->
                <div class="card card-primary card-outline mb-4">
                    <div class="card-header">
                        <div class="card-title">All Financial Reports</div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Period</th>
                                        <th class="text-end">Sales</th>
                                        <th class="text-end">Purchase</th>
                                        <th class="text-end">Expense</th>
                                        <th class="text-end">Discount</th>
                                        <th class="text-end">Damage</th>
                                        <th class="text-end">Lost</th>
                                        <th class="text-end">Profit</th>
                                        <th>Created By</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($reports as $report)
                                    <tr>
                                        <td>{{  $reports->firstItem() + $loop->index }}</td>
                                        <td>
                                            <strong>{{ $report->start_date->format('d M Y') }}</strong><br>
                                            <small class="text-muted">to</small><br>
                                            <strong>{{ $report->end_date->format('d M Y') }}</strong>
                                        </td>
                                        <td class="text-end text-info">৳{{ number_format($report->total_sales, 2) }}</td>
                                        <td class="text-end text-warning">৳{{ number_format($report->total_purchase, 2) }}</td>
                                        <td class="text-end text-danger">৳{{ number_format($report->total_expense, 2) }}</td>
                                        <td class="text-end text-secondary">৳{{ number_format($report->discount_amount, 2) }}</td>
                                        <td class="text-end text-danger">৳{{ number_format($report->total_damage, 2) }}</td>
                                        <td class="text-end text-danger">৳{{ number_format($report->total_lost_amount, 2) }}</td>
                                        <td class="text-end text-success"><strong>৳{{ number_format($report->total_profit, 2) }}</strong></td>
                                        <td>
                                            {{ $report->creator->name ?? 'N/A' }}<br>
                                            <small class="text-muted">{{ $report->created_at->format('d M Y') }}</small>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.financialReportShow', $report) }}"
                                                   class="btn btn-sm btn-outline-info"
                                                   title="View">
                                                    <span class="mdi mdi-eye"></span>
                                                </a>
                                                <a href="{{ route('admin.financialReportEdit', $report) }}"
                                                   class="btn btn-sm btn-outline-primary"
                                                   title="Edit">
                                                    <span class="mdi mdi-pencil"></span>
                                                </a>
                                                <button type="button"
                                                        class="btn btn-sm btn-outline-danger delete-btn"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteModal"
                                                        data-url="{{ route('admin.financialReportDestroy', $report) }}"
                                                        title="Delete">
                                                    <span class="mdi mdi-delete"></span>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="11" class="text-center">No financial reports found</td>
                                    </tr>
                                    @endforelse
                                    @include('components.delete')
                                </tbody>
                                @if($reports->total() > 0)
                                <tfoot class="table-secondary">
                                    <tr>
                                        <th colspan="2">Total</th>
                                        <th class="text-end text-info">৳{{ number_format($totals->sum_sales ?? 0, 2) }}</th>
                                        <th class="text-end text-warning">৳{{ number_format($totals->sum_purchase ?? 0, 2) }}</th>
                                        <th class="text-end text-danger">৳{{ number_format($totals->sum_expense ?? 0, 2) }}</th>
                                        <th class="text-end text-secondary">৳{{ number_format($totals->sum_discount ?? 0, 2) }}</th>
                                        <th class="text-end text-danger">৳{{ number_format($totals->sum_damage ?? 0, 2) }}</th>
                                        <th class="text-end text-danger">৳{{ number_format($totals->sum_lost ?? 0, 2) }}</th>
                                        <th class="text-end text-success"><strong>৳{{ number_format($totals->sum_profit ?? 0, 2) }}</strong></th>
                                        <th colspan="2"></th>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                        @if($reports->hasPages())
                            <div class="mt-3">
                                {{ $reports->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
