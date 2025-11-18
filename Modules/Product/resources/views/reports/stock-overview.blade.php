@extends('layouts.app')

@section('title', 'Stock Overview Report')

@section('content')


    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h3 class="mb-0">Stock Management</h3>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-end">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Stock Overview</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="app-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">

                            <!-- Row 1: Title -->
                            <div class="row mb-3">
                                <div class="col-12">
                                    <h3 class="card-title mb-0">Stock Overview Report</h3>
                                </div>
                            </div>

                            <div class="row">
                                <form method="GET" action="{{ route('admin.reportStockOverview') }}" class="mb-0 w-100">
                                    <div class="row g-3 align-items-end">

                                        <!-- Product Filter -->
                                        <div class="col-md-3">
                                            <label class="form-label">Product</label>
                                            <select name="product_id" class="form-select select2">
                                                <option value="">All Products</option>
                                                @foreach ($productName as $nameproduct)
                                                    <option value="{{ $nameproduct->id }}"
                                                        {{ request('product_id') == $nameproduct->id ? 'selected' : '' }}>
                                                        {{ $nameproduct->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Filter + Reset Buttons -->
                                        <div class="col-md-3">
                                            <label class="form-label">Actions</label>
                                            <div class="d-flex gap-2">
                                                <button type="submit" class="btn btn-primary btn-sm">
                                                    <i class="mdi mdi-filter"></i> Filter
                                                </button>

                                                <a href="{{ route('admin.reportStockOverview') }}"
                                                    class="btn btn-secondary btn-sm">
                                                    <i class="mdi mdi-refresh"></i> Reset
                                                </a>
                                            </div>
                                        </div>

                                        <!-- Export Button (Right Aligned) -->
                                        <div class="col-md-6 text-end">
                                            <label class="form-label d-block text-end">Export</label>
                                            <button type="button" class="btn btn-success btn-sm"
                                                onclick="exportTableToExcel('stockOverviewTable', 'Stock_Overview')">
                                                <i class="mdi mdi-file-excel"></i> Export to Excel
                                            </button>
                                        </div>

                                    </div>
                                </form>
                            </div>


                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="stockOverviewTable" class="table table-bordered table-striped table-hover">
                                    <thead class="">
                                        <tr>
                                            <th>SL</th>
                                            <th>Image</th>
                                            <th>Product Name</th>
                                            <th>Total Purchase Qty</th>
                                            <th>Total Purchase Amount</th>
                                            <th>Total Sold Qty</th>
                                            <th>Total Sold Amount</th>
                                            <th>Total Damage/Lost Qty</th>
                                            <th>Available Qty</th>
                                            <th>Available Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $totalPurchaseQty = 0;
                                            $totalPurchaseAmount = 0;
                                            $totalSoldQty = 0;
                                            $totalSoldAmount = 0;
                                            $totalAvailableQty = 0;
                                            $totalAvailableAmount = 0;
                                            $totalDamageLostQty = 0;
                                        @endphp
                                        @forelse($products as $key => $product)
                                            @php
                                                $totalPurchaseQty += $product['total_purchase_qty'];
                                                $totalPurchaseAmount += $product['total_purchase_amount'];
                                                $totalSoldQty += $product['total_sold_qty'];
                                                $totalSoldAmount += $product['total_sold_amount'];
                                                $totalDamageLostQty += $product['total_damage_lost_qty'];
                                                $totalAvailableQty += $product['available_qty'];
                                                $totalAvailableAmount += $product['available_amount'];
                                            @endphp
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>
                                                    <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}"
                                                        class="img-thumbnail"
                                                        style="width: 50px; height: 50px; object-fit: cover;">
                                                </td>
                                                <td>{{ $product['name'] }}</td>
                                                <td class="text-end">{{ number_format($product['total_purchase_qty'], 0) }}
                                                </td>
                                                <td class="text-end">
                                                    ৳{{ number_format($product['total_purchase_amount'], 2) }}</td>
                                                <td class="text-end">{{ number_format($product['total_sold_qty'], 0) }}
                                                </td>
                                                <td class="text-end">৳{{ number_format($product['total_sold_amount'], 2) }}
                                                </td>
                                                <td class="text-end">
                                                    {{ number_format($product['total_damage_lost_qty'], 0) }}</td>
                                                <td class="text-end">{{ number_format($product['available_qty'], 0) }}</td>
                                                <td class="text-end">৳{{ number_format($product['available_amount'], 2) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center">No products found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                    <tfoot class="table-secondary">
                                        <tr>
                                            <th colspan="3" class="text-end">Total:</th>
                                            <th class="text-end">{{ number_format($totalPurchaseQty, 0) }}</th>
                                            <th class="text-end">৳{{ number_format($totalPurchaseAmount, 2) }}</th>
                                            <th class="text-end">{{ number_format($totalSoldQty, 0) }}</th>
                                            <th class="text-end">৳{{ number_format($totalSoldAmount, 2) }}</th>
                                            <th class="text-end">{{ number_format($totalDamageLostQty, 0) }}</th>
                                            <th class="text-end">{{ number_format($totalAvailableQty, 0) }}</th>
                                            <th class="text-end">৳{{ number_format($totalAvailableAmount, 2) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
@endpush

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/table2excel@1.0.4/dist/table2excel.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#stockOverviewTable').DataTable({
                pageLength: 25,
                order: [
                    [2, 'asc']
                ],
                columnDefs: [{
                    orderable: false,
                    targets: [1]
                }]
            });
        });

        function exportTableToExcel(tableID, filename = '') {
            var table2excel = new Table2Excel();
            table2excel.export(document.getElementById(tableID), filename);
        }
    </script>
@endpush
