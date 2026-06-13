# API Setup Guide

This guide will help you set up and test the new API functionality in your Laravel Inventory Management System.

## Prerequisites

✅ Laravel Passport is already installed
✅ Admin model has `HasApiTokens` trait
✅ API guard is configured in `config/auth.php`

## Setup Steps

### 1. Update Admin Model (Already Done)

The Admin model has been updated with the `HasApiTokens` trait:

```php
use Laravel\Passport\HasApiTokens;

class Admin extends Authenticatable implements HasMedia
{
    use HasFactory, Notifiable, HasApiTokens, HasRoles, InteractsWithMedia;
    // ...
}
```

### 2. Run Passport Installation (If Not Already Done)

If you haven't already installed Passport keys, run:

```bash
php artisan passport:install
```

This will generate encryption keys and create personal access and password grant clients.

### 3. Clear Cache

Clear the application cache to ensure all changes take effect:

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### 4. Verify Routes

Check that all API routes are registered:

```bash
php artisan route:list --path=api
```

You should see routes like:
- `POST api/v1/admin/login`
- `POST api/v1/admin/logout`
- `GET api/v1/products/search`
- `GET api/v1/vendors/search`
- `POST api/v1/orders`
- etc.

## Created Files Structure

### Admin Module
```
Modules/Admin/
├── app/
│   └── Http/
│       ├── Controllers/
│       │   └── Api/
│       │       └── AuthController.php
│       └── Resources/
│           └── AdminResource.php
└── routes/
    └── api.php (updated)
```

### Product Module
```
Modules/Product/
├── app/
│   └── Http/
│       ├── Controllers/
│       │   └── Api/
│       │       ├── ProductController.php
│       │       ├── VendorController.php
│       │       └── OrderController.php
│       └── Resources/
│           ├── ProductResource.php
│           ├── VendorResource.php
│           └── OrderResource.php
└── routes/
    └── api.php (updated)
```

## API Endpoints Summary

### Authentication
- `POST /api/v1/admin/login` - Login and get token
- `POST /api/v1/admin/logout` - Logout (revoke token)
- `GET /api/v1/admin/profile` - Get authenticated user profile

### Products
- `GET /api/v1/products/search?search=rice&limit=10` - Search products with suggestions
- `GET /api/v1/products/{id}` - Get product details

### Vendors
- `GET /api/v1/vendors/search?search=ABC&limit=10` - Search vendors with suggestions
- `GET /api/v1/vendors/{id}` - Get vendor details

### Orders
- `POST /api/v1/orders` - Create new order
- `PUT /api/v1/orders/{id}` - Update order (only if not confirmed)
- `GET /api/v1/orders/{id}` - Get order details
- `GET /api/v1/orders/by-placed-by?place_by=2` - Get orders by user who placed them
- `POST /api/v1/orders/{id}/cancel` - Cancel order (only if not confirmed)

## Testing with Postman

### Method 1: Import Collection File

1. Open Postman
2. Click "Import" button
3. Select the file: `Inventory_API.postman_collection.json`
4. Update environment variables:
   - `base_url`: Your API URL (e.g., `http://localhost/api` or `http://inventory.test/api`)
   - `token`: Will be set automatically after login

### Method 2: Manual Testing

1. **Login Request:**
   ```
   POST http://your-domain.com/api/v1/admin/login
   
   Headers:
   Content-Type: application/json
   Accept: application/json
   
   Body:
   {
       "email_or_phone": "admin@example.com",
       "password": "your_password"
   }
   ```

2. **Copy the token from response:**
   ```json
   {
       "data": {
           "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
       }
   }
   ```

3. **Use token in subsequent requests:**
   ```
   GET http://your-domain.com/api/v1/products/search?search=rice
   
   Headers:
   Authorization: Bearer {your_token}
   Accept: application/json
   ```

## Testing Flow

1. **Login** → Get admin details with roles, permissions, and token
2. **Search Products** → Type product name, get suggestions with image, price, quantity
3. **Search Vendors** → Type vendor name, get suggestions with address and due balance
4. **Create Order** → Place order with multiple products
5. **Get Orders by User** → Filter orders by the user who placed them
6. **Update Order** → Edit order before it's confirmed
7. **Cancel Order** → Cancel order if needed
8. **Logout** → Revoke token

## Key Features

### 1. Authentication
- Uses Laravel Passport for OAuth2 authentication
- Returns admin profile with roles and permissions
- Includes profile picture URLs

### 2. Product Search
- Search by product name, company, or color
- Returns product images (original and thumbnail)
- Shows available quantity from all stocks
- Includes sell price and stock details

### 3. Vendor Search
- Search by shop name, contact person, email, or mobile
- Returns vendor images
- Shows current due balance and old due
- Includes full address information

### 4. Order Management
- **Create Order:**
  - Support for multiple items with "add more" functionality
  - Automatic stock allocation (smart allocation from highest price)
  - Calculates totals, discounts, and profits
  
- **Update Order:**
  - Checks if order can be edited (not confirmed)
  - Restores old stock and allocates new stock
  - Updates all order details
  
- **Get by Placed By:**
  - Filter by user who placed the order
  - Filter by status, vendor, date range
  - Paginated results

### 5. Smart Stock Allocation
- Automatically allocates stock from available inventory
- Uses FIFO with highest sell price first
- Handles multiple warehouses and batches
- Prevents overselling by checking available quantity

## Error Handling

All APIs return consistent error responses:

```json
{
    "success": false,
    "message": "Error description",
    "errors": {
        "field": ["Validation error message"]
    }
}
```

## Security Notes

1. All authenticated endpoints require Bearer token
2. Tokens are revoked on logout
3. Order updates are restricted based on order status
4. Stock validation prevents overselling
5. Role and permission checks are included in responses

## Common Issues & Solutions

### Issue: "Unauthenticated" Error
**Solution:** Make sure you're sending the token in the Authorization header:
```
Authorization: Bearer {your_token}
```

### Issue: "Route not found"
**Solution:** Clear route cache:
```bash
php artisan route:clear
php artisan optimize:clear
```

### Issue: Token not being generated
**Solution:** Ensure Passport is properly installed:
```bash
php artisan passport:install --force
```

### Issue: "Insufficient stock" error
**Solution:** This is a business logic protection. Check available stock quantity before placing order.

## Next Steps

1. Test all endpoints using Postman
2. Integrate with your mobile app or frontend
3. Add additional validation as needed
4. Consider adding rate limiting for production
5. Set up API documentation tool like Swagger (optional)

## Documentation Files

- `API_DOCUMENTATION.md` - Complete API documentation with examples
- `Inventory_API.postman_collection.json` - Importable Postman collection
- `API_SETUP_GUIDE.md` - This setup guide

## Support

For any issues or questions:
1. Check the error logs: `storage/logs/laravel.log`
2. Verify database connections
3. Ensure all migrations are run
4. Check that required data exists (products, vendors, admins)

---

**Happy Testing! 🚀**
