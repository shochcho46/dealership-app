# API Documentation

## Base URL
```
http://your-domain.com/api
```

## Authentication
All API requests (except login) require authentication using Bearer token.

**Header:**
```
Authorization: Bearer {your_access_token}
Accept: application/json
Content-Type: application/json
```

---

## 1. Authentication APIs

### 1.1 Admin Login
**Endpoint:** `POST /v1/admin/login`

**Description:** Login admin user and receive access token

**Request Body:**
```json
{
    "email_or_phone": "admin@example.com",
    "password": "password123"
}
```

**OR with phone:**
```json
{
    "email_or_phone": "01712345678",
    "password": "password123"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "admin": {
            "id": 1,
            "name": "Admin User",
            "email": "admin@example.com",
            "phone": "+8801712345678",
            "status": 1,
            "status_text": "Active",
            "picture": {
                "url": "http://domain.com/storage/profile.jpg",
                "thumb_url": "http://domain.com/storage/profile_thumb.jpg"
            },
            "roles": [
                {
                    "id": 1,
                    "name": "admin",
                    "guard_name": "admin"
                }
            ],
            "permissions": [
                {
                    "id": 1,
                    "name": "view-dashboard",
                    "guard_name": "admin"
                },
                {
                    "id": 2,
                    "name": "manage-products",
                    "guard_name": "admin"
                }
            ],
            "created_at": "2024-01-01 10:00:00",
            "updated_at": "2024-01-15 15:30:00"
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
        "token_type": "Bearer"
    }
}
```

**Error Response (401):**
```json
{
    "success": false,
    "message": "Invalid credentials"
}
```

**Error Response (403):**
```json
{
    "success": false,
    "message": "This account is blacklisted"
}
```

---

### 1.2 Admin Logout
**Endpoint:** `POST /v1/admin/logout`

**Headers:** 
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

---

### 1.3 Get Admin Profile
**Endpoint:** `GET /v1/admin/profile`

**Headers:** 
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Admin User",
        "email": "admin@example.com",
        "phone": "+8801712345678",
        "status": 1,
        "status_text": "Active",
        "picture": {
            "url": "http://domain.com/storage/profile.jpg",
            "thumb_url": "http://domain.com/storage/profile_thumb.jpg"
        },
        "roles": [...],
        "permissions": [...],
        "created_at": "2024-01-01 10:00:00",
        "updated_at": "2024-01-15 15:30:00"
    }
}
```

---

## 2. Product APIs

### 2.1 Search Products
**Endpoint:** `GET /v1/products/search`

**Headers:** 
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `search` (optional): Search term for product name, company, or color
- `limit` (optional): Number of results (default: 10, max: 50)

**Example Request:**
```
GET /v1/products/search?search=rice&limit=5
```

**Success Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Basmati Rice 5kg",
            "description": "Premium quality basmati rice",
            "color": {
                "id": 1,
                "name": "White"
            },
            "company": {
                "id": 1,
                "name": "ABC Foods"
            },
            "unit": {
                "id": 1,
                "name": "Kilogram"
            },
            "measurement_unit_name": "kg",
            "measurement_unit_number": "5",
            "package_unit_name": "bag",
            "package_unit_quantity": "1",
            "discount_type": "percentage",
            "discount_amount": "5.00",
            "status": 1,
            "status_text": "Active",
            "image": {
                "url": "http://domain.com/storage/product.jpg",
                "thumb_url": "http://domain.com/storage/product_thumb.jpg"
            },
            "quantity_available": 500,
            "sell_price": "850.00",
            "stocks": [
                {
                    "stock_id": 1,
                    "warehouse_id": 1,
                    "batch_id": "BATCH001",
                    "purchase_price": "700.00",
                    "sell_price": "850.00",
                    "available_quantity": 300,
                    "manufacture_date": "2024-01-01",
                    "expire_date": "2025-01-01"
                },
                {
                    "stock_id": 2,
                    "warehouse_id": 1,
                    "batch_id": "BATCH002",
                    "purchase_price": "720.00",
                    "sell_price": "850.00",
                    "available_quantity": 200,
                    "manufacture_date": "2024-02-01",
                    "expire_date": "2025-02-01"
                }
            ],
            "created_at": "2024-01-01 10:00:00",
            "updated_at": "2024-01-15 15:30:00"
        }
    ],
    "count": 1
}
```

---

### 2.2 Get Product Details
**Endpoint:** `GET /v1/products/{id}`

**Headers:** 
```
Authorization: Bearer {token}
```

**Example Request:**
```
GET /v1/products/1
```

**Success Response (200):** Same structure as product in search response

**Error Response (404):**
```json
{
    "success": false,
    "message": "Product not found"
}
```

---

## 3. Vendor APIs

### 3.1 Search Vendors
**Endpoint:** `GET /v1/vendors/search`

**Headers:** 
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `search` (optional): Search term for shop name, contact person, email, or mobile
- `limit` (optional): Number of results (default: 10, max: 50)

**Example Request:**
```
GET /v1/vendors/search?search=ABC&limit=5
```

**Success Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "uuid": "550e8400-e29b-41d4-a716-446655440000",
            "shop_name": "ABC Trading",
            "contact_person": "John Doe",
            "email": "abc@example.com",
            "mobile": "+8801712345678",
            "country": {
                "id": 18,
                "name": "Bangladesh",
                "iso": "BD"
            },
            "address": {
                "full_address": "123 Main Street, Dhaka, Bangladesh",
                "lat": "23.8103",
                "long": "90.4125"
            },
            "status": 1,
            "status_text": "Active",
            "image": {
                "url": "http://domain.com/storage/vendor.jpg",
                "thumb_url": "http://domain.com/storage/vendor_thumb.jpg"
            },
            "due_balance": "25000.00",
            "old_due": "5000.00",
            "total_credit": "75000.00",
            "total_debit": "100000.00",
            "created_at": "2024-01-01 10:00:00",
            "updated_at": "2024-01-15 15:30:00"
        }
    ],
    "count": 1
}
```

---

### 3.2 Create Vendor
**Endpoint:** `POST /v1/vendors`

**Headers:** 
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body (Form Data):**
- `shop_name` (required): Vendor shop name
- `contact_person` (required): Contact person name
- `email` (optional): Email address (must be unique)
- `mobile` (required): Mobile number (must be unique)
- `country_id` (required): Country ID
- `full_address` (required): Full address
- `lat` (optional): Latitude
- `long` (optional): Longitude
- `status` (optional): Status (1 = active, 0 = inactive, default: 1)
- `image` (optional): Vendor image file (jpeg, png, jpg, gif, webp, max: 2MB)

**Example Request (Form Data):**
```
POST /v1/vendors

shop_name: ABC Trading Company
contact_person: John Doe
email: abc@example.com
mobile: +8801712345678
country_id: 18
full_address: 123 Main Street, Dhaka, Bangladesh
lat: 23.8103
long: 90.4125
status: 1
image: [file]
```

**Success Response (201):**
```json
{
    "success": true,
    "message": "Vendor created successfully",
    "data": {
        "id": 1,
        "uuid": "550e8400-e29b-41d4-a716-446655440000",
        "shop_name": "ABC Trading Company",
        "contact_person": "John Doe",
        "email": "abc@example.com",
        "mobile": "+8801712345678",
        "country": {
            "id": 18,
            "name": "Bangladesh",
            "iso": "BD"
        },
        "address": {
            "full_address": "123 Main Street, Dhaka, Bangladesh",
            "lat": "23.8103",
            "long": "90.4125"
        },
        "status": 1,
        "status_text": "Active",
        "image": {
            "url": "http://domain.com/storage/vendor.jpg",
            "thumb_url": "http://domain.com/storage/vendor_thumb.jpg"
        },
        "due_balance": "0.00",
        "old_due": "0.00",
        "total_credit": "0.00",
        "total_debit": "0.00",
        "created_at": "2024-01-01 10:00:00",
        "updated_at": "2024-01-01 10:00:00"
    }
}
```

**Error Response (422):**
```json
{
    "success": false,
    "message": "Validation error",
    "errors": {
        "shop_name": ["The shop name field is required."],
        "mobile": ["The mobile has already been taken."]
    }
}
```

---

### 3.3 Get Vendor Details
**Endpoint:** `GET /v1/vendors/{id}`

**Headers:** 
```
Authorization: Bearer {token}
```

**Example Request:**
```
GET /v1/vendors/1
```

**Success Response (200):** Same structure as vendor in search response

**Error Response (404):**
```json
{
    "success": false,
    "message": "Vendor not found"
}
```

---

### 3.4 Update Vendor
**Endpoint:** `PUT /v1/vendors/{id}` or `POST /v1/vendors/{id}` (with `_method=PUT` for form-data)

**Headers:** 
```
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

**Request Body (Form Data):**
- `shop_name` (required): Vendor shop name
- `contact_person` (required): Contact person name
- `email` (optional): Email address (must be unique except current)
- `mobile` (required): Mobile number (must be unique except current)
- `country_id` (required): Country ID
- `full_address` (required): Full address
- `lat` (optional): Latitude
- `long` (optional): Longitude
- `status` (optional): Status (1 = active, 0 = inactive)
- `image` (optional): New vendor image file (replaces old one)
- `_method` (optional): PUT (use when sending via POST)

**Example Request (Form Data):**
```
POST /v1/vendors/1

_method: PUT
shop_name: ABC Trading Company Ltd
contact_person: John Doe
email: abc@example.com
mobile: +8801712345678
country_id: 18
full_address: 456 New Address, Dhaka, Bangladesh
status: 1
image: [new_file]
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Vendor updated successfully",
    "data": {
        "id": 1,
        "uuid": "550e8400-e29b-41d4-a716-446655440000",
        "shop_name": "ABC Trading Company Ltd",
        "contact_person": "John Doe",
        "email": "abc@example.com",
        "mobile": "+8801712345678",
        "country": {
            "id": 18,
            "name": "Bangladesh",
            "iso": "BD"
        },
        "address": {
            "full_address": "456 New Address, Dhaka, Bangladesh",
            "lat": "23.8103",
            "long": "90.4125"
        },
        "status": 1,
        "status_text": "Active",
        "image": {
            "url": "http://domain.com/storage/new_vendor.jpg",
            "thumb_url": "http://domain.com/storage/new_vendor_thumb.jpg"
        },
        "due_balance": "25000.00",
        "old_due": "5000.00",
        "total_credit": "75000.00",
        "total_debit": "100000.00",
        "created_at": "2024-01-01 10:00:00",
        "updated_at": "2024-01-15 15:30:00"
    }
}
```

**Error Response (404):**
```json
{
    "success": false,
    "message": "Vendor not found"
}
```

**Error Response (422):**
```json
{
    "success": false,
    "message": "Validation error",
    "errors": {
        "email": ["The email has already been taken."]
    }
}
```

---

## 4. Order APIs

### 4.1 Create Order
**Endpoint:** `POST /v1/orders`

**Headers:** 
```
Authorization: Bearer {token}
```

**Request Body:**
```json
{
    "vendor_id": 1,
    "place_by": 2,
    "items": [
        {
            "product_id": 1,
            "quantity": 10,
            "sell_price": 850.00,
            "discount_price": 50.00
        },
        {
            "product_id": 2,
            "quantity": 5,
            "sell_price": 1200.00,
            "discount_price": 0
        }
    ]
}
```

**Success Response (201):**
```json
{
    "success": true,
    "message": "Order created successfully",
    "data": {
        "id": 1,
        "invoice_id": "SSE-09-05-26-1234-AB-1",
        "admin": {
            "id": 1,
            "name": "Admin User",
            "email": "admin@example.com"
        },
        "vendor": {
            "id": 1,
            "shop_name": "ABC Trading",
            "contact_person": "John Doe",
            "mobile": "+8801712345678",
            "email": "abc@example.com",
            "full_address": "123 Main Street, Dhaka",
            "due_balance": "25000.00"
        },
        "placed_by": {
            "id": 2,
            "name": "Sales Rep",
            "email": "sales@example.com"
        },
        "order_status": {
            "id": 2,
            "name": "Confirmed",
            "color": "#28a745"
        },
        "total_amount": "14450.00",
        "paid_amount": "0.00",
        "due_amount": "14450.00",
        "total_quantity": 15,
        "total_discount_amount": "50.00",
        "total_return_quantity": 0,
        "total_damage_quantity": 0,
        "total_lost_quantity": 0,
        "payment_status": 0,
        "payment_status_text": "Unpaid",
        "total_profit": "2250.00",
        "can_be_cancelled": true,
        "items": [
            {
                "id": 1,
                "product": {
                    "id": 1,
                    "name": "Basmati Rice 5kg",
                    "image": "http://domain.com/storage/product.jpg"
                },
                "quantity": 10,
                "purchase_price": "700.00",
                "sell_price": "850.00",
                "total_price": "8450.00",
                "discount_price": "50.00",
                "profit": "1450.00"
            },
            {
                "id": 2,
                "product": {
                    "id": 2,
                    "name": "Premium Oil 5L",
                    "image": "http://domain.com/storage/product2.jpg"
                },
                "quantity": 5,
                "purchase_price": "1000.00",
                "sell_price": "1200.00",
                "total_price": "6000.00",
                "discount_price": "0.00",
                "profit": "1000.00"
            }
        ],
        "paid_at": null,
        "created_at": "2024-05-09 14:30:00",
        "updated_at": "2024-05-09 14:30:00"
    }
}
```

**Error Response (422):**
```json
{
    "success": false,
    "message": "Validation error",
    "errors": {
        "vendor_id": ["The vendor id field is required."],
        "items": ["The items field must have at least 1 items."]
    }
}
```

**Error Response (500):**
```json
{
    "success": false,
    "message": "Error creating order",
    "error": "Insufficient stock for Basmati Rice 5kg. Still need: 5"
}
```

---

### 4.2 Update Order
**Endpoint:** `PUT /v1/orders/{id}`

**Headers:** 
```
Authorization: Bearer {token}
```

**Request Body:** Same as Create Order

**Success Response (200):**
```json
{
    "success": true,
    "message": "Order updated successfully",
    "data": {
        // Same structure as order response
    }
}
```

**Error Response (403):**
```json
{
    "success": false,
    "message": "This order cannot be updated. It has been confirmed or is in a final state."
}
```

**Error Response (404):**
```json
{
    "success": false,
    "message": "Order not found"
}
```

---

### 4.3 Cancel Order
**Endpoint:** `POST /v1/orders/{id}/cancel`

**Headers:** 
```
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Order cancelled successfully and stock restored."
}
```

**Error Response (403):**
```json
{
    "success": false,
    "message": "This order cannot be cancelled."
}
```

---

### 4.4 Get Order Details
**Endpoint:** `GET /v1/orders/{id}`

**Headers:** 
```
Authorization: Bearer {token}
```

**Example Request:**
```
GET /v1/orders/1
```

**Success Response (200):** Same structure as order in create response

**Error Response (404):**
```json
{
    "success": false,
    "message": "Order not found"
}
```

---

### 4.5 Get Orders by Placed By User
**Endpoint:** `GET /v1/orders/by-placed-by`

**Headers:** 
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `place_by` (required): Admin ID who placed the orders
- `status_filter` (optional): Filter by order status ID
- `vendor_filter` (optional): Filter by vendor ID
- `date_from` (optional): Start date (YYYY-MM-DD)
- `date_to` (optional): End date (YYYY-MM-DD)
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Items per page (default: 15, max: 100)

**Example Request:**
```
GET /v1/orders/by-placed-by?place_by=2&status_filter=2&page=1&per_page=10
```

**Success Response (200):**
```json
{
    "success": true,
    "data": [
        {
            // Order object (same structure as single order)
        },
        {
            // Another order object
        }
    ],
    "meta": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 10,
        "total": 45
    }
}
```

**Error Response (422):**
```json
{
    "success": false,
    "message": "Validation error",
    "errors": {
        "place_by": ["The place by field is required."]
    }
}
```

---

## 5. DSR Collection APIs

### 5.1 List DSR Collections
**Endpoint:** `GET /v1/dsr-collections`

**Description:** Get a paginated list of DSR collections with optional filters

**Headers:** 
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `vendor_id` (optional): Filter by vendor ID
- `payment_method_id` (optional): Filter by payment method ID
- `date_from` (optional): Start date (YYYY-MM-DD)
- `date_to` (optional): End date (YYYY-MM-DD)
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Items per page (default: 15, max: 100)

**Example Request:**
```
GET /v1/dsr-collections?vendor_id=1&date_from=2026-07-01&date_to=2026-07-31&page=1&per_page=15
```

**Success Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "vendor": {
                "id": 1,
                "shop_name": "ABC Trading",
                "contact_person": "John Doe",
                "mobile": "+8801712345678",
                "email": "abc@example.com",
                "full_address": "123 Main Street, Dhaka",
                "due_balance": "25000.00"
            },
            "payment_method": {
                "id": 1,
                "name": "Cash",
                "account_name": null
            },
            "amount": "5000.00",
            "collection_date": "2026-07-21",
            "note": "Cash collection from vendor",
            "created_by": {
                "id": 1,
                "name": "Admin User",
                "email": "admin@example.com"
            },
            "deposite_by": {
                "id": 2,
                "name": "Sales Rep",
                "email": "sales@example.com"
            },
            "created_at": "2026-07-21 14:30:00",
            "updated_at": "2026-07-21 14:30:00"
        }
    ],
    "meta": {
        "current_page": 1,
        "last_page": 3,
        "per_page": 15,
        "total": 42,
        "filtered_total_amount": "125000.00",
        "total_all_time": "350000.00"
    }
}
```

**Error Response (422):**
```json
{
    "success": false,
    "message": "Validation error",
    "errors": {
        "vendor_id": ["The vendor id must be a valid vendor."]
    }
}
```

---

### 5.2 Create DSR Collection
**Endpoint:** `POST /v1/dsr-collections`

**Description:** Create a new DSR collection record (vendor payment without invoice)

**Headers:** 
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "vendor_id": 1,
    "payment_method_id": 1,
    "amount": 5000.00,
    "collection_date": "2026-07-21",
    "note": "Cash collection from vendor"
}
```

**Field Descriptions:**
- `vendor_id` (required): ID of the vendor making the payment
- `payment_method_id` (required): ID of payment method (Cash, Bank, etc.)
- `amount` (required): Payment amount (minimum: 0.01)
- `collection_date` (required): Date of collection (YYYY-MM-DD)
- `note` (optional): Additional notes (max: 1000 characters)

**Success Response (201):**
```json
{
    "success": true,
    "message": "Collection recorded successfully",
    "data": {
        "id": 1,
        "vendor": {
            "id": 1,
            "shop_name": "ABC Trading",
            "contact_person": "John Doe",
            "mobile": "+8801712345678",
            "email": "abc@example.com",
            "full_address": "123 Main Street, Dhaka",
            "due_balance": "20000.00"
        },
        "payment_method": {
            "id": 1,
            "name": "Cash",
            "account_name": null
        },
        "amount": "5000.00",
        "collection_date": "2026-07-21",
        "note": "Cash collection from vendor",
        "created_by": {
            "id": 1,
            "name": "Admin User",
            "email": "admin@example.com"
        },
        "deposite_by": {
            "id": 1,
            "name": "Admin User",
            "email": "admin@example.com"
        },
        "created_at": "2026-07-21 14:30:00",
        "updated_at": "2026-07-21 14:30:00"
    }
}
```

**Note:** If SMS notifications are enabled (`config('app.collection_sms') == 1`), an SMS will be sent to the vendor's mobile number.

**Error Response (422):**
```json
{
    "success": false,
    "message": "Validation error",
    "errors": {
        "vendor_id": ["The vendor id field is required."],
        "amount": ["The amount must be at least 0.01."]
    }
}
```

---

### 5.3 Get DSR Collection Details
**Endpoint:** `GET /v1/dsr-collections/{id}`

**Description:** Get detailed information about a specific DSR collection

**Headers:** 
```
Authorization: Bearer {token}
```

**Example Request:**
```
GET /v1/dsr-collections/1
```

**Success Response (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "vendor": {
            "id": 1,
            "shop_name": "ABC Trading",
            "contact_person": "John Doe",
            "mobile": "+8801712345678",
            "email": "abc@example.com",
            "full_address": "123 Main Street, Dhaka",
            "due_balance": "20000.00"
        },
        "payment_method": {
            "id": 1,
            "name": "Cash",
            "account_name": null
        },
        "amount": "5000.00",
        "collection_date": "2026-07-21",
        "note": "Cash collection from vendor",
        "created_by": {
            "id": 1,
            "name": "Admin User",
            "email": "admin@example.com"
        },
        "deposite_by": {
            "id": 1,
            "name": "Admin User",
            "email": "admin@example.com"
        },
        "created_at": "2026-07-21 14:30:00",
        "updated_at": "2026-07-21 14:30:00"
    }
}
```

**Error Response (404):**
```json
{
    "success": false,
    "message": "Collection not found"
}
```

---

### 5.4 Delete DSR Collection
**Endpoint:** `DELETE /v1/dsr-collections/{id}`

**Description:** Delete a DSR collection record (SuperAdmin/admin only)

**Headers:** 
```
Authorization: Bearer {token}
```

**Example Request:**
```
DELETE /v1/dsr-collections/1
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Collection deleted successfully"
}
```

**Error Response (403):**
```json
{
    "success": false,
    "message": "You do not have permission to delete this collection"
}
```

**Error Response (404):**
```json
{
    "success": false,
    "message": "Collection not found"
}
```

---

### 5.5 Search Vendors
**Endpoint:** `GET /v1/dsr-collections/vendors/search`

**Description:** Search vendors by shop name or mobile for collection form

**Headers:** 
```
Authorization: Bearer {token}
```

**Query Parameters:**
- `search` (optional): Search term (shop name or mobile)
- `limit` (optional): Maximum results (default: 10, max: 50)

**Example Request:**
```
GET /v1/dsr-collections/vendors/search?search=ABC&limit=10
```

**Success Response (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "shop_name": "ABC Trading",
            "mobile": "+8801712345678",
            "full_address": "123 Main Street, Dhaka",
            "contact_person": "John Doe",
            "due_balance": "25000.00"
        },
        {
            "id": 2,
            "shop_name": "ABC Traders",
            "mobile": "+8801798765432",
            "full_address": "456 Commerce St, Dhaka",
            "contact_person": "Jane Smith",
            "due_balance": "15000.00"
        }
    ],
    "count": 2
}
```

**Error Response (422):**
```json
{
    "success": false,
    "message": "Validation error",
    "errors": {
        "limit": ["The limit must not be greater than 50."]
    }
}
```

---

## Error Responses

All endpoints may return the following error responses:

### 401 Unauthorized
```json
{
    "message": "Unauthenticated."
}
```

### 500 Internal Server Error
```json
{
    "success": false,
    "message": "An error occurred",
    "error": "Error details..."
}
```

---

## Postman Collection Setup

### 1. Create Environment Variables
- `base_url`: `http://your-domain.com/api`
- `token`: (Will be set automatically after login)

### 2. Pre-request Script for Authenticated Requests
Add this to folder/collection level:
```javascript
pm.request.headers.add({
    key: 'Authorization',
    value: 'Bearer ' + pm.environment.get('token')
});
pm.request.headers.add({
    key: 'Accept',
    value: 'application/json'
});
```

### 3. Test Script for Login Endpoint
Add this to the login request:
```javascript
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    pm.environment.set('token', jsonData.data.token);
}
```

---

## Testing Flow

1. **Login** → Save token
2. **Search Products** → Get product IDs
3. **Search Vendors** → Get vendor IDs
4. **Create Order** → Use product and vendor IDs
5. **Get Orders by User** → View created orders
6. **Update Order** → Modify before confirmation
7. **Cancel Order** → Cancel if needed
8. **Logout** → End session

---

## Notes

1. All monetary values are returned as strings with 2 decimal places
2. Dates are in `YYYY-MM-DD HH:MM:SS` format
3. The API uses Laravel Passport for authentication
4. Orders can only be updated/cancelled before confirmation
5. Stock is automatically allocated from available inventory
6. The system uses smart stock allocation (highest sell price first)
