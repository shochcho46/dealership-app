@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Profit Distribution</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.profitDistributeIndex') }}">Profit Distribution</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Distribution Information</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.profitDistributeIndex') }}" class="btn btn-secondary btn-sm">
                            <i class="mdi mdi-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <form action="{{ route('admin.profitDistributeUpdate', $profitDistribute->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="title">Title <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control @error('title') is-invalid @enderror"
                                           id="title"
                                           name="title"
                                           value="{{ old('title', $profitDistribute->title) }}"
                                           placeholder="Enter distribution title"
                                           required>
                                    @error('title')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="total_amount">Total Amount <span class="text-danger">*</span></label>
                                    <input type="number"
                                           class="form-control @error('total_amount') is-invalid @enderror"
                                           id="total_amount"
                                           name="total_amount"
                                           step="0.01"
                                           value="{{ old('total_amount', $profitDistribute->total_amount) }}"
                                           placeholder="Enter total amount"
                                           required>
                                    @error('total_amount')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="month">Month <span class="text-danger">*</span></label>
                                    <select class="form-control @error('month') is-invalid @enderror"
                                            id="month"
                                            name="month"
                                            required>
                                        <option value="">Select Month</option>
                                        <option value="1" {{ old('month', $profitDistribute->month) == 1 ? 'selected' : '' }}>January</option>
                                        <option value="2" {{ old('month', $profitDistribute->month) == 2 ? 'selected' : '' }}>February</option>
                                        <option value="3" {{ old('month', $profitDistribute->month) == 3 ? 'selected' : '' }}>March</option>
                                        <option value="4" {{ old('month', $profitDistribute->month) == 4 ? 'selected' : '' }}>April</option>
                                        <option value="5" {{ old('month', $profitDistribute->month) == 5 ? 'selected' : '' }}>May</option>
                                        <option value="6" {{ old('month', $profitDistribute->month) == 6 ? 'selected' : '' }}>June</option>
                                        <option value="7" {{ old('month', $profitDistribute->month) == 7 ? 'selected' : '' }}>July</option>
                                        <option value="8" {{ old('month', $profitDistribute->month) == 8 ? 'selected' : '' }}>August</option>
                                        <option value="9" {{ old('month', $profitDistribute->month) == 9 ? 'selected' : '' }}>September</option>
                                        <option value="10" {{ old('month', $profitDistribute->month) == 10 ? 'selected' : '' }}>October</option>
                                        <option value="11" {{ old('month', $profitDistribute->month) == 11 ? 'selected' : '' }}>November</option>
                                        <option value="12" {{ old('month', $profitDistribute->month) == 12 ? 'selected' : '' }}>December</option>
                                    </select>
                                    @error('month')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="year">Year <span class="text-danger">*</span></label>
                                    <input type="number"
                                           class="form-control @error('year') is-invalid @enderror"
                                           id="year"
                                           name="year"
                                           min="2020"
                                           max="2100"
                                           value="{{ old('year', $profitDistribute->year) }}"
                                           placeholder="Enter year"
                                           required>
                                    @error('year')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox"
                                               class="custom-control-input"
                                               id="status"
                                               name="status"
                                               value="1"
                                               {{ old('status', $profitDistribute->status) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="status">Active</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-content-save"></i> Update
                        </button>
                        <a href="{{ route('admin.profitDistributeIndex') }}" class="btn btn-secondary">
                            <i class="mdi mdi-cancel"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
