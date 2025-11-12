@extends('layouts.app')

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">Expense List Management</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Expense Lists</li>
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
                    <h1 class="mt-3">Expense Lists</h1>
                    <div class="text-end">
                        <a href="{{ route('admin.expenseListCreate') }}" class="btn btn-primary">
                            <span class="mdi mdi-plus"></span> Add New Expense
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

                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">All Expenses</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover" id="expenseListTable">
                                <thead>
                                    <tr>
                                        <th style="width: 5%">#</th>
                                        <th style="width: 12%">Expense Head</th>
                                        <th style="width: 15%">Title</th>
                                        <th style="width: 20%">Description</th>
                                        <th style="width: 10%">Amount (৳)</th>
                                        <th style="width: 10%">Expense Date</th>
                                        <th style="width: 10%">Reference No</th>
                                        <th style="width: 8%">Status</th>
                                        <th style="width: 10%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($expenseLists as $expenseList)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <a href="{{ route('admin.expenseHeadEdit', $expenseList->expenseHead->id) }}"
                                                   class="text-decoration-none" title="View Expense Head">
                                                    {{ $expenseList->expenseHead->title }}
                                                </a>
                                            </td>
                                            <td>{{ $expenseList->title }}</td>
                                            <td>
                                                @if($expenseList->description)
                                                    <span title="{{ $expenseList->description }}">
                                                        {{ Str::limit($expenseList->description, 50) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">No description</span>
                                                @endif
                                            </td>
                                            <td class="text-end">৳{{ number_format($expenseList->amount, 2) }}</td>
                                            <td>{{ $expenseList->expense_date->format('d M Y') }}</td>
                                            <td>
                                                @if($expenseList->reference_no)
                                                    {{ $expenseList->reference_no }}
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($expenseList->status == 1)
                                                    <span class="badge bg-success">Active</span>
                                                @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.expenseListEdit', $expenseList->id) }}"
                                                   class="btn btn-sm btn-warning" title="Edit">
                                                    <span class="mdi mdi-pencil"></span>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteModal{{ $expenseList->id }}"
                                                        title="Delete">
                                                    <span class="mdi mdi-delete"></span>
                                                </button>

                                                <!-- Delete Modal -->
                                                <div class="modal fade" id="deleteModal{{ $expenseList->id }}" tabindex="-1"
                                                     aria-labelledby="deleteModalLabel{{ $expenseList->id }}" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="deleteModalLabel{{ $expenseList->id }}">
                                                                    Confirm Delete
                                                                </h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                Are you sure you want to delete the expense "<strong>{{ $expenseList->title }}</strong>"?
                                                                This action cannot be undone.
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <form action="{{ route('admin.expenseListDestroy', $expenseList->id) }}"
                                                                      method="POST" style="display: inline;">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center">No expense records found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if($expenseLists->isNotEmpty())
                                <tfoot>
                                    <tr>
                                        <th colspan="4" class="text-end">Total:</th>
                                        <th class="text-end">৳{{ number_format($expenseLists->sum('amount'), 2) }}</th>
                                        <th colspan="4"></th>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#expenseListTable').DataTable({
            "order": [[5, "desc"]], // Sort by expense date descending
            "pageLength": 25,
            "columnDefs": [
                { "orderable": false, "targets": [8] } // Disable sorting on Actions column
            ]
        });
    });
</script>
@endsection
