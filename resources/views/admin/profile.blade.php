@extends('layouts.app')

@push('custome-css')
<style>
    .profile-img-container {
        position: relative;
        width: 150px;
        height: 150px;
        margin: 0 auto;
    }
    .profile-img-container img {
        width: 150px;
        height: 150px;
        object-fit: cover;
        border-radius: 50%;
        border: 4px solid #f4f4f4;
    }
    .profile-img-container .upload-btn {
        position: absolute;
        bottom: 5px;
        right: 5px;
        background: #007bff;
        color: white;
        border-radius: 50%;
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
</style>
@endpush

@section('content')

<div class="app-content-header">
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mb-0">My Profile</h3>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Profile</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row">
            <!-- Profile Information Card -->
            <div class="col-md-6">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Profile Information</h3>
                    </div>
                    <form action="{{ route('admin.updateProfile') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <!-- Profile Picture -->
                            <div class="form-group text-center mb-4">
                                <div class="profile-img-container">
                                    <img id="profilePreview"
                                         src="{{ $admin->hasMedia('profile_picture') ? $admin->getFirstMediaUrl('profile_picture') : asset('assets/img/usr.gif') }}"
                                         alt="Profile Picture">
                                    <label for="profile_picture" class="upload-btn">
                                        <i class="bi bi-camera"></i>
                                    </label>
                                </div>
                                <input type="file"
                                       id="profile_picture"
                                       name="profile_picture"
                                       class="d-none"
                                       accept="image/*">
                                @error('profile_picture')
                                    <div class="text-danger mt-2">{{ $message }}</div>
                                @enderror
                                <small class="text-muted d-block mt-2">Click camera icon to change profile picture</small>
                            </div>

                            <!-- Name -->
                            <div class="form-group mb-3">
                                <label for="name">Name <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('name') is-invalid @enderror"
                                       id="name"
                                       name="name"
                                       value="{{ old('name', $admin->name) }}"
                                       placeholder="Enter your name"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email (Read-only) -->
                            <div class="form-group mb-3">
                                <label for="email">Email</label>
                                <input type="email"
                                       class="form-control"
                                       id="email"
                                       value="{{ $admin->email }}"
                                       readonly
                                       disabled>
                                <small class="text-muted">Email cannot be changed</small>
                            </div>

                            <!-- Phone -->
                            <div class="form-group mb-3">
                                <label for="phone">Phone</label>
                                <input type="text"
                                       class="form-control @error('phone') is-invalid @enderror"
                                       id="phone"
                                       name="phone"
                                       value="{{ old('phone', $admin->phone) }}"
                                       placeholder="Enter your phone number">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update Profile
                            </button>
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Change Password Card -->
            <div class="col-md-6">
                <div class="card card-warning card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Change Password</h3>
                    </div>
                    <form action="{{ route('admin.updateProfilePassword') }}" method="POST">
                        @csrf
                        <div class="card-body">
                            <!-- Current Password -->
                            <div class="form-group mb-3">
                                <label for="old_password">Current Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password"
                                           class="form-control @error('old_password') is-invalid @enderror"
                                           id="old_password"
                                           name="old_password"
                                           placeholder="Enter current password"
                                           required>
                                    <button class="btn btn-outline-secondary" type="button" id="toggleOldPassword">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @error('old_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- New Password -->
                            <div class="form-group mb-3">
                                <label for="new_password">New Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password"
                                           class="form-control @error('new_password') is-invalid @enderror"
                                           id="new_password"
                                           name="new_password"
                                           placeholder="Enter new password (min 6 characters)"
                                           required>
                                    <button class="btn btn-outline-secondary" type="button" id="toggleNewPassword">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @error('new_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted">Password must be at least 6 characters</small>
                            </div>

                            <!-- Confirm New Password -->
                            <div class="form-group mb-3">
                                <label for="new_password_confirmation">Confirm New Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password"
                                           class="form-control @error('new_password_confirmation') is-invalid @enderror"
                                           id="new_password_confirmation"
                                           name="new_password_confirmation"
                                           placeholder="Confirm new password"
                                           required>
                                    <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @error('new_password_confirmation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                <strong>Password Requirements:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>Minimum 6 characters</li>
                                    <li>Must be different from current password</li>
                                    <li>Both passwords must match</li>
                                </ul>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" class="btn btn-warning">
                                <i class="bi bi-shield-lock"></i> Change Password
                            </button>
                            <button type="reset" class="btn btn-secondary">
                                <i class="bi bi-arrow-counterclockwise"></i> Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('custome-js')
<script>
    // Profile picture preview
    document.getElementById('profile_picture').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profilePreview').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    });

    // Toggle password visibility
    function togglePassword(inputId, buttonId) {
        const input = document.getElementById(inputId);
        const button = document.getElementById(buttonId);
        const icon = button.querySelector('i');

        button.addEventListener('click', function() {
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        });
    }

    togglePassword('old_password', 'toggleOldPassword');
    togglePassword('new_password', 'toggleNewPassword');
    togglePassword('new_password_confirmation', 'toggleConfirmPassword');
</script>
@endpush
