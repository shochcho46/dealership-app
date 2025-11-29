# Admin User Management CRUD Documentation

## Overview

The Admin User Management system provides a complete CRUD (Create, Read, Update, Delete) interface for managing administrative users in the dealership application. It integrates with Laravel Spatie Role & Permission package for role-based access control.

## Features

- **Create Admin Users**: Add new admin users with role assignment
- **Read Admin Users**: View all admins with their roles and status
- **Update Admin Users**: Edit admin details with optional password change
- **Delete Admin Users**: Remove admin users (with protection against deleting the last admin)
- **Role Assignment**: Assign roles during creation and update
- **Status Management**: Activate/Deactivate admin users
- **View Details**: View complete admin user information with assigned permissions

## File Locations

### Backend Files

```
app/
├── Models/
│   └── Admin.php (Updated with HasRoles trait)
├── Http/
│   └── Controllers/
│       └── Admin/
│           └── AdminUserController.php (New)
```

### Routes

```
routes/admin.php (Updated with admin user routes)
```

### Views

```
resources/views/admin/admin-user/
├── index.blade.php (List all admins)
├── create.blade.php (Create new admin)
├── edit.blade.php (Edit admin)
└── show.blade.php (View admin details)
```

### Sidebar Navigation

```
resources/views/layouts/adminsidebar.blade.php (Updated)
```

## Detailed Implementation

### 1. Admin Model (app/Models/Admin.php)

**Key Changes:**
- Added `use HasRoles` trait from Spatie
- Set `$guard_name = 'admin'` for admin guard
- Added helper methods:
  - `getDisplayNameAttribute()` - Returns name with email
  - `isActive()` - Checks if admin is active
  - `getRolesStringAttribute()` - Returns comma-separated roles

**Usage:**
```php
$admin = Admin::find(1);
$admin->assignRole('super-admin');
$admin->hasRole('super-admin'); // true/false
$admin->hasPermission('create-users'); // true/false
```

### 2. AdminUserController (app/Http/Controllers/Admin/AdminUserController.php)

#### Methods Overview

**index()**
- Lists all admin users with pagination (20 per page)
- Includes roles relationship
- Returns view with paginated admins

**create()**
- Displays form to create new admin
- Loads all roles for dropdown

**store()**
- Validates input (name, email, password, phone, role)
- Password is bcrypted
- Assigns role to the newly created admin
- Returns success message with role name

**edit($admin)**
- Displays edit form pre-filled with current data
- Loads all roles for dropdown
- Shows current selected roles

**update($admin)**
- Validates input (optional password field)
- Only updates password if provided (allows update without password)
- Syncs roles (replaces with new role selection)
- Preserves existing data if not changed

**destroy($admin)**
- Prevents deletion of the last admin user
- Soft delete is not used (hard delete)
- Returns success message with admin name

**show($admin)**
- Displays complete admin details
- Shows assigned roles and permissions
- Read-only view

**toggleStatus($admin)**
- Toggles active/inactive status
- Returns with status change message

### 3. Routes (routes/admin.php)

```php
Route::controller(AdminUserController::class)->group(function () {
    Route::get('user/index', 'index')->name('admin.adminUserIndex');
    Route::get('user/create', 'create')->name('admin.adminUserCreate');
    Route::post('user/store', 'store')->name('admin.adminUserStore');
    Route::get('user/{admin}/edit', 'edit')->name('admin.adminUserEdit');
    Route::put('user/{admin}/update', 'update')->name('admin.adminUserUpdate');
    Route::get('user/{admin}', 'show')->name('admin.adminUserShow');
    Route::delete('user/{admin}/delete', 'destroy')->name('admin.adminUserDestroy');
    Route::put('user/{admin}/toggle-status', 'toggleStatus')->name('admin.adminUserToggleStatus');
});
```

### 4. Views

#### index.blade.php
- Lists all admins in table format
- Shows: ID, Name, Email, Phone, Roles, Status
- Action buttons: View, Edit, Toggle Status, Delete
- Delete confirmation modal
- Pagination links

#### create.blade.php
- Form fields: Name, Email, Password, Confirm Password, Phone, Role, Status
- All fields have validation error display
- Role dropdown populated from database
- Status checkbox (checked by default)

#### edit.blade.php
- Same fields as create, but with current values
- Password field is **optional** (leave blank to keep current)
- Confirms password only if password is entered
- Shows role pre-selected

#### show.blade.php
- Read-only display of admin details
- Shows all information in definition list format
- Displays assigned roles and permissions
- Edit and Back buttons

### 5. Sidebar Navigation

**New Menu Item:** User Management
- Icon: Account Multiple (mdi-account-multiple)
- Submenu: Admin Users

**Location:** Between Dashboard and Role & Permission

## Validation Rules

### Store (Create)
```php
'name' => 'required|string|max:255'
'email' => 'required|email|unique:admins,email'
'password' => 'required|string|min:8|confirmed'
'phone' => 'nullable|string|max:20'
'role' => 'required|exists:roles,id'
'status' => 'nullable|boolean'
```

### Update
```php
'name' => 'required|string|max:255'
'email' => 'required|email|unique:admins,email,' . $admin->id
'password' => 'nullable|string|min:8|confirmed'  // Optional for update
'phone' => 'nullable|string|max:20'
'role' => 'required|exists:roles,id'
'status' => 'nullable|boolean'
```

## Role Assignment

### During Creation
```php
$role = Role::find($validated['role']);
$admin->assignRole($role);
```

### During Update
```php
$role = Role::find($validated['role']);
$admin->syncRoles($role);
```

**Difference:**
- `assignRole()`: Adds role to existing roles
- `syncRoles()`: Replaces all roles (used for single role scenario)

## Usage Examples

### Create Admin with Role
```php
$admin = Admin::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => bcrypt('password'),
    'phone' => '+8801234567890',
    'status' => 1
]);
$admin->assignRole('admin');
```

### Check Admin Permissions
```php
$admin = Admin::find(1);
if ($admin->hasPermission('create-users')) {
    // Can create users
}
```

### Get Admin Roles
```php
$admin = Admin::find(1);
$roles = $admin->roles; // Collection of roles
$roleNames = $admin->roles->pluck('name'); // ['admin', 'user']
```

## Security Features

1. **Password Hashing**: All passwords are bcrypted
2. **Email Uniqueness**: Prevents duplicate admin emails
3. **Role Validation**: Can only assign existing roles
4. **Last Admin Protection**: Cannot delete the only admin
5. **Status Control**: Can activate/deactivate without deletion
6. **Password Confirmation**: Required match on creation

## Error Handling

All methods include try-catch blocks with:
- Custom error messages
- User-friendly feedback
- Input preservation on validation failure

## Navigation

**From Dashboard:**
1. Sidebar → User Management → Admin Users
2. Or direct URL: `/admin/user/index`

**From Admin Users List:**
- Create New: Button at top right
- View: Eye icon
- Edit: Pencil icon
- Toggle Status: Check/Block icon
- Delete: Trash icon

## Database Considerations

Requires standard `admins` table with:
- id
- name
- email
- password
- phone
- otp
- status
- created_at
- updated_at

Also requires Spatie role-permission tables:
- roles
- permissions
- model_has_roles
- model_has_permissions

## Future Enhancements

- Bulk actions (delete, status change)
- Advanced filtering (by role, status, date)
- Admin activity logging
- Login history
- Two-factor authentication
- Profile management
- Custom permissions per admin
- Department/Team assignment

## Troubleshooting

**Issue:** Role dropdown is empty
- **Solution:** Ensure roles are created in Role Management section first

**Issue:** Error updating without password
- **Solution:** Password field is optional - leave blank to keep current password

**Issue:** Cannot delete admin
- **Solution:** System prevents deleting the last admin user for security

**Issue:** Changed password but can't login
- **Solution:** Verify password field is correctly confirmed

## Related Documentation

- [Role & Permission Documentation](./ROLE_PERMISSION.md)
- [Database Schema](./DATABASE_SCHEMA.md)
- [Quick Reference](./QUICK_REFERENCE.md)
