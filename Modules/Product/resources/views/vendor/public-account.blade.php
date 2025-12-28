<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $vendor->shop_name }} - Account Statement</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.materialdesignicons.com/7.2.96/css/materialdesignicons.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .account-summary-card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .account-summary-card:hover {
            transform: translateY(-5px);
        }

        .summary-icon {
            font-size: 2rem;
            opacity: 0.8;
        }

        .summary-amount {
            font-size: 1.4rem;
            word-break: break-word;
        }

        .summary-title {
            font-size: 0.75rem;
        }

        .invoice-link {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
        }

        .invoice-link:hover {
            text-decoration: underline;
        }

        .vendor-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
        }

        .date-filter-card {
            border-radius: 10px;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 15px;
        }

        .section-title {
            font-weight: 600;
            color: #495057;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Vendor Info Header -->
        <div class="vendor-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">{{ $vendor->shop_name }}</h1>
                    <p class="mb-0 fs-5">
                        <i class="mdi mdi-phone"></i> {{ $vendor->mobile }}
                        @if($vendor->email)
                            <span class="mx-2">|</span>
                            <i class="mdi mdi-email"></i> {{ $vendor->email }}
                        @endif
                    </p>
                    @if($vendor->full_address)
                        <p class="mb-0 mt-2">
                            <i class="mdi mdi-map-marker"></i> {{ $vendor->full_address }}
                        </p>
                    @endif
                </div>
                <div class="col-md-4 text-end">
                    <h5 class="mb-1">Account Statement</h5>
                    <p class="mb-0">{{ now()->format('d M, Y h:i A') }}</p>
                </div>
            </div>
        </div>

        <!-- Date Filter -->
        <div class="card date-filter-card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('vendor.publicAccount', $vendor->uuid) }}" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="start_date" class="form-label fw-bold">Start Date</label>
                        <input type="date" class="form-control" id="start_date" name="start_date"
                               value="{{ $startDate }}" required>
                    </div>
                    <div class="col-md-4">
                        <label for="end_date" class="form-label fw-bold">End Date</label>
                        <input type="date" class="form-control" id="end_date" name="end_date"
                               value="{{ $endDate }}" required>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-filter"></i> Filter
                        </button>
                        <a href="{{ route('vendor.publicAccount', $vendor->uuid) }}" class="btn btn-secondary">
                            <i class="mdi mdi-refresh"></i> Reset
                        </a>

                    </div>
                </form>
            </div>
        </div>

        <!-- Period Summary Cards -->
        <h5 class="section-title"><i class="mdi mdi-calendar-range"></i> Period Summary ({{ date('d M, Y', strtotime($startDate)) }} - {{ date('d M, Y', strtotime($endDate)) }})</h5>
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card account-summary-card text-white bg-danger">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-white mb-1 summary-title">Period Debit</h6><br>
                                <h5 class="mb-0 summary-amount">৳{{ number_format($totalDebit, 2) }}</h5>
                            </div>
                            <i class="mdi mdi-arrow-up-bold summary-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card account-summary-card text-white bg-success">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-white mb-1 summary-title">Period Credit</h6><br>
                                <h5 class="mb-0 summary-amount">৳{{ number_format($totalCredit, 2) }}</h5>
                            </div>
                            <i class="mdi mdi-arrow-down-bold summary-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card account-summary-card text-white {{ $balance >= 0 ? 'bg-info' : 'bg-warning' }}">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-white mb-1 summary-title">Period Balance</h6><br>
                                <h5 class="mb-0 summary-amount">৳{{ number_format($balance, 2) }}</h5>
                            </div>
                            <i class="mdi mdi-calculator summary-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card account-summary-card text-white bg-secondary">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-white mb-1 summary-title">Period Records</h6><br>
                                <h5 class="mb-0 summary-amount">{{ $accounts->count() }}</h5>
                            </div>
                            <i class="mdi mdi-file-document-multiple summary-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- All-Time Summary Cards -->
        <h5 class="section-title"><i class="mdi mdi-clock-outline"></i> All-Time Summary</h5>
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card account-summary-card text-white" style="background: linear-gradient(135deg, #d63031 0%, #e17055 100%);">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-white mb-1 summary-title">All-Time Debit</h6><br>
                                <h5 class="mb-0 summary-amount">৳{{ number_format($allTimeDebit, 2) }}</h5>
                            </div>
                            <i class="mdi mdi-trending-up summary-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card account-summary-card text-white" style="background: linear-gradient(135deg, #00b894 0%, #00cec9 100%);">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-white mb-1 summary-title">All-Time Credit</h6><br>
                                <h5 class="mb-0 summary-amount">৳{{ number_format($allTimeCredit, 2) }}</h5>
                            </div>
                            <i class="mdi mdi-trending-down summary-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card account-summary-card text-white {{ $allTimeBalance >= 0 ? 'bg-primary' : 'bg-dark' }}">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-white mb-1 summary-title">Overall Balance</h6><br>
                                <h5 class="mb-0 summary-amount">৳{{ number_format($allTimeBalance, 2) }}</h5>
                            </div>
                            <i class="mdi mdi-wallet summary-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card account-summary-card text-white" style="background: linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%);">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-white mb-1 summary-title">Total Transactions</h6><br>
                                <h5 class="mb-0 summary-amount">{{ number_format($totalTransactions) }}</h5>
                            </div>
                            <i class="mdi mdi-chart-line summary-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="mdi mdi-history"></i> Transaction History ({{ $accounts->count() }} records)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="">
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Invoice/Order</th>
                                <th>Type</th>
                                <th>Payment Method</th>
                                <th>Amount</th>

                            </tr>
                        </thead>
                        <tbody>
                            @forelse($accounts as $account)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $account->collection_date ? $account->collection_date->format('d M, Y') : $account->created_at->format('d M, Y') }}</td>
                                    <td>
                                        @if($account->order)
                                            <a href="{{ route('invoices.preview', $account->order->id) }}"
                                               class="invoice-link"
                                               target="_blank"
                                               title="Click to view invoice">
                                                <i class="mdi mdi-file-document"></i>
                                                {{ $account->order->invoice_id }}
                                            </a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $account->type_badge_class }}">
                                            {{ $account->type_text }}
                                        </span>
                                    </td>
                                    <td>{{ $account->paymentMethod->account_name ?? 'N/A' }}</td>
                                    <td>
                                        <strong class="{{ $account->type == 1 ? 'text-danger' : 'text-success' }}">
                                            {{ $account->type == 1 ? '-' : '+' }}৳{{ number_format($account->amount, 2) }}
                                        </strong>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="mdi mdi-information-outline" style="font-size: 2rem;"></i>
                                        <p class="mb-0 mt-2">No transactions found for the selected date range.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($accounts->count() > 0)
                            <tfoot class="table-secondary">
                                <tr>
                                    <th colspan="4" class="text-end">Totals:</th>
                                    <th colspan="2">
                                        <span class="text-danger">Debit: ৳{{ number_format($totalDebit, 2) }}</span>
                                        <span class="mx-2">|</span>
                                        <span class="text-success">Credit: ৳{{ number_format($totalCredit, 2) }}</span>
                                        <span class="mx-2">|</span>
                                        <span class="{{ $balance >= 0 ? 'text-primary' : 'text-warning' }}">
                                            Balance: ৳{{ number_format($balance, 2) }}
                                        </span>
                                    </th>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
                 <div class="mt-3">
                    {{ $accounts->links() }}
                </div>
            </div>
        </div>

        <div class="text-center mt-4 mb-3">
            <p class="text-muted">
                <i class="mdi mdi-information"></i>

                 This is an automated account statement. For any queries, please contact us.<br><br>
                @if ($businessDetail->hasMedia('logo'))
                    <img src="{{ $businessDetail->getFirstMediaUrl('logo') ?? '' }}" alt={{ $businessDetail->name ?? 'Our Business' }} style="max-height: 60px; margin-top: 10px;"><br>
                @endif
                 {{ $businessDetail->company_name ?? 'Our Business' }}<br>
                {{ $businessDetail->address ?? '' }}<br>
                Hotline one: {{ $businessDetail->mobile_one ?? 'N/A' }}, Hotline 2: {{ $businessDetail->mobile_two ?? 'N/A' }}<br>
                Website: <b>{{ $businessDetail->website ?? 'https://www.bddealership.com' }}</b><br>




            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Set max date to today for date inputs
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('start_date').setAttribute('max', today);
            document.getElementById('end_date').setAttribute('max', today);
        });


    </script>
</body>
</html>
