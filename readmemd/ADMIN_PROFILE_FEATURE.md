# Admin Profile Feature

## Overview
This feature allows admin users to manage their profile information including personal details and password using Spatie Media Library for profile picture management.

## Features Implemented

### 1. Profile Information Update
- **Name**: Update admin name
- **Phone**: Update phone number
- **Profile Picture**: Upload and change profile picture (using Spatie Media Library)
- **Email**: Display only (cannot be changed)

### 2. Password Management
- **Old Password Verification**: Required to change password
- **New Password**: Must be at least 6 characters and different from old password
- **Password Confirmation**: Both passwords must match

### 3. Separate Forms
- Profile information and password change are in separate forms
- Each form has its own submit button
- Independent validation for each section

## Files Modified/Created

### 1. Routes (`routes/admin.php`)
```php
Route::get('profile', 'profile')->name('admin.profile');
Route::post('profile/update', 'updateProfile')->name('admin.updateProfile');
Route::post('profile/update-password', 'updateProfilePassword')->name('admin.updateProfilePassword');
```

### 2. Controller (`app/Http/Controllers/Admin/AdminController.php`)
- `profile()`: Display profile page
- `updateProfile()`: Update profile information with Spatie Media Library
- `updateProfilePassword()`: Update password with old password verification

### 3. Model (`app/Models/Admin.php`)
- Implements `HasMedia` interface
- Uses `InteractsWithMedia` trait
- Registers 'profile_picture' media collection
- Single file upload with accepted mime types: jpeg, png, jpg, gif

### 4. View (`resources/views/admin/profile.blade.php`)
- Two-column layout
- Profile information form (left)
- Password change form (right)
- Image preview functionality using Spatie Media methods
- Password visibility toggle
- Responsive design

### 5. Header (`resources/views/layouts/header.blade.php`)
- Updated admin profile link to route to profile page
- Profile picture display using Spatie Media Library methods

### 6. Media Collection
- Collection Name: `profile_picture`
- Single File: Yes
- Accepted Types: jpeg, png, jpg, gif
- Max Size: 2MB

## Installation Steps

1. **Spatie Media Library is already installed** in your project

2. **No migration needed** - Spatie Media Library uses its own `media` table

## Usage

1. **Access Profile**:
   - Click on your name in the top-right corner
   - Click "Profile" button

2. **Update Profile Information**:
   - Change name, phone number
   - Click camera icon to upload profile picture
   - Click "Update Profile" button

3. **Change Password**:
   - Enter current password
   - Enter new password (min 6 characters)
   - Confirm new password
   - Click "Change Password" button

## Validation Rules

### Profile Update
- Name: Required, max 255 characters
- Phone: Optional, max 20 characters
- Profile Picture: Optional, must be image (jpeg, jpg, png, gif), max 2MB

### Password Update
- Old Password: Required
- New Password: Required, min 6 characters, different from old password
- Confirm Password: Required, must match new password

## Security Features
- Old password verification before changing password
- Password hashing using Laravel's Hash facade
- Separate authentication guard for admin
- CSRF protection on all forms

## UI Features
- Bootstrap 5 styling
- Responsive design
- Image preview before upload
- Password visibility toggle
- Form validation with error messages
- Success/error toast notifications
- Loading indicators

## Spatie Media Library Features
- Automatic file management
- Single file per collection (old images auto-deleted)
- Media stored in `storage/app/public/media` directory
- Mime type validation
- Easy retrieval with `getFirstMediaUrl()`
- Check existence with `hasMedia()`

## Media Methods Used

### In Controller:
```php
// Upload profile picture
$admin->addMediaFromRequest('profile_picture')
    ->toMediaCollection('profile_picture');

// Clear existing profile picture
$admin->clearMediaCollection('profile_picture');
```

### In Views:
```blade
// Check if profile picture exists
@if($admin->hasMedia('profile_picture'))

// Get profile picture URL
{{ $admin->getFirstMediaUrl('profile_picture') }}
```

## Notes
- Profile pictures are managed by Spatie Media Library
- Old profile pictures are automatically deleted when new ones are uploaded
- Email field is read-only and cannot be changed
- All forms use POST method with CSRF protection
- Media files are stored in the database and linked via the `media` table
