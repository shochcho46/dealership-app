# DEALERSHIP APP - DOCUMENTATION INDEX

## 📚 Available Documentation Files

This project includes comprehensive documentation covering all aspects of the Dealership App. Below is a guide to navigate the documentation.

---

## 📖 MAIN DOCUMENTATION FILES

### 1. **PROJECT_ARCHITECTURE_GUIDE.md** ⭐ [START HERE]
**Complete comprehensive guide covering:**
- Project overview and structure
- Modular architecture explanation
- Complete product-to-order-to-return workflow
- Database schema with relationships
- View hierarchy and template extension
- Controllers and business logic
- Stock management system
- Detailed step-by-step workflows
- Implementation patterns

**When to Read**: Get complete project understanding, understand overall architecture

---

### 2. **DATABASE_SCHEMA.md** ⭐ [FOR DATABASE WORK]
**Detailed database documentation:**
- Complete ER diagram (ASCII art)
- Table-by-table specifications
- Field types and constraints
- Relationships and foreign keys
- Scopes and methods per model
- Critical operations impact analysis
- Transaction flow documentation

**When to Read**: Work with database, understand data structures, debug data issues

---

### 3. **QUICK_REFERENCE.md** ⭐ [FOR DAILY CODING]
**Quick lookup reference guide:**
- Directory structure map
- Key models and relationships
- Database tables cheat sheet
- Stock quantity formula
- Order status flow
- Controller method reference
- Routes quick reference
- Blade template structure
- Code pattern examples
- Common queries
- API endpoints (AJAX)
- Troubleshooting checklist

**When to Read**: Daily development, quick lookups, remember method names

---

## 🎯 WHAT EACH FILE ANSWERS

| Question | File | Section |
|----------|------|---------|
| How is the project organized? | PROJECT_ARCHITECTURE_GUIDE | Project Structure & Modules |
| What models exist and how do they relate? | DATABASE_SCHEMA | Entity Relationship Diagram |
| What are the table names and fields? | DATABASE_SCHEMA | Table Details & Field Reference |
| How does an order get created? | PROJECT_ARCHITECTURE_GUIDE | Workflow 2: Order Creation |
| What is smart stock allocation? | PROJECT_ARCHITECTURE_GUIDE | Stock Management System |
| How does damage/return work? | PROJECT_ARCHITECTURE_GUIDE | Workflow 4: Damage/Return |
| What's the vendor balance calculation? | QUICK_REFERENCE | Key Formulas & Calculations |
| How are views structured? | PROJECT_ARCHITECTURE_GUIDE | View Hierarchy & Template Extension |
| What routes are available? | QUICK_REFERENCE | Routes Quick Reference |
| What's the OrderController.store() method? | QUICK_REFERENCE | Controller Method Reference |
| How to calculate stock availability? | QUICK_REFERENCE | Stock Quantity Formula |
| What AJAX endpoints exist? | QUICK_REFERENCE | API Endpoints (AJAX) |
| Database changed, what needs updating? | DATABASE_SCHEMA | Critical Operations & Their Impact |
| How to add a new feature? | PROJECT_ARCHITECTURE_GUIDE | Implementation Sequence for New Features |
| Error during order creation? | QUICK_REFERENCE | Troubleshooting Checklist |

---

## 🔄 WORKFLOWS DOCUMENTATION

All workflows are documented in **PROJECT_ARCHITECTURE_GUIDE.md** with step-by-step breakdowns:

### Core Workflows
1. **Product Creation to Stock Entry**
   - Creating products
   - Adding inventory

2. **Order Creation with Smart Allocation**
   - Vendor selection
   - Product selection
   - Smart batch allocation algorithm
   - Frozen stock reservation

3. **Order Status Update → Payment Creation**
   - Froze → Sold conversion
   - Vendor account creation (Debit)
   - Payment collection setup

4. **Damage/Return Processing**
   - Damage reporting
   - Return processing
   - Vendor credit creation
   - Stock adjustments

5. **Vendor Payment Collection**
   - Payment recording
   - Vendor account update (Credit)
   - Balance calculation

6. **Invoice & Reporting**
   - Invoice generation
   - Vendor statements
   - Excel exports

---

## 🗂️ DIRECTORY STRUCTURE

For detailed directory structure, see: **QUICK_REFERENCE.md** → "DIRECTORY QUICK MAP"

Key folders:
- `Modules/Product/` - Main product module
- `Modules/Product/app/Models/` - Data models
- `Modules/Product/app/Http/Controllers/` - Business logic
- `Modules/Product/database/migrations/` - Database structure
- `Modules/Product/resources/views/` - UI templates
- `resources/views/layouts/` - Base templates

---

## 🔑 KEY CONCEPTS TO UNDERSTAND

### 1. **Modular Architecture**
See: PROJECT_ARCHITECTURE_GUIDE → Project Structure & Modules

The app uses Laravel-Modules package to organize code into independent modules. The Product module is self-contained with its own:
- Controllers
- Models
- Views
- Routes
- Migrations
- Tests

### 2. **Smart Stock Allocation** ⭐
See: PROJECT_ARCHITECTURE_GUIDE → Stock Management System

When an order is created, the system:
1. Finds all available stocks for the product
2. Orders by sell_price DESC (profit optimization)
3. Allocates from highest to lowest
4. Creates order_item_stocks records linking items to specific batches
5. Freezes quantities to reserve them

### 3. **Frozen vs Sold Stock** ⭐
See: QUICK_REFERENCE → Stock Quantity Formula

- **froze_quantity**: Reserved for pending orders (not available for others)
- **sold_quantity**: Confirmed as shipped/delivered
- When order ships: froze → sold conversion happens
- When order cancelled: froze quantities restored

### 4. **Vendor Financial Tracking** ⭐
See: QUICK_REFERENCE → Key Formulas & Calculations

- Type 1 (Debit): When order shipped to vendor (they owe us)
- Type 2 (Credit): When payment received (debt reduced)
- Balance = SUM(Credits) - SUM(Debits)
- Negative balance = Vendor owes us

### 5. **Damage/Return/Lost Processing** ⭐
See: QUICK_REFERENCE → Troubleshooting Checklist

When items are damaged/returned/lost:
1. Quantity distributed among order_item_stocks
2. Stock quantities updated
3. If return: Vendor gets credit (refund)
4. If damage/lost: No refund (vendor's responsibility)

---

## 📱 COMMON DEVELOPMENT TASKS

### Adding a New Route
1. See: QUICK_REFERENCE → Routes Quick Reference
2. Add to: `Modules/Product/routes/web.php`
3. Follow the controller naming pattern

### Creating a New Model
1. See: DATABASE_SCHEMA → Table Details
2. Create migration first
3. Create model class
4. Define relationships
5. Add scopes and accessors as needed

### Adding a New View
1. See: PROJECT_ARCHITECTURE_GUIDE → View Hierarchy
2. Create file in: `Modules/Product/resources/views/[feature]/`
3. Extend: `@extends('layouts.app')`
4. Use: `@push('custome-css')` and `@push('custome-js')`

### Debugging Stock Issues
1. See: QUICK_REFERENCE → Stock Quantity Formula
2. Check: `quantity - (sold + damage + stolen + froze)`
3. Verify: All order_item_stocks linked to stocks
4. Confirm: froze→sold conversions completed

### Fixing Order-Related Issues
1. See: QUICK_REFERENCE → Troubleshooting Checklist
2. Verify: Vendor exists and is active
3. Check: Stock availability
4. Confirm: Order status flow correct
5. Validate: All calculations in order_item_stocks

---

## 🔍 MODEL RELATIONSHIPS QUICK LOOKUP

See: QUICK_REFERENCE → Key Models & Relationships

### Main Chain
```
Product → Stock → OrderItemStock ← OrderItem ← Order
                                     ↓
                                   Vendor
                                     ↓
                                VendorAccount
```

### Related Models
```
Order ←→ DamageReturnLost
Order ←→ OrderStatus
Order ←→ Admin
Vendor ←→ Country
```

---

## 🚀 IMPLEMENTATION CHECKLIST

When implementing a new feature:

- [ ] Read PROJECT_ARCHITECTURE_GUIDE for context
- [ ] Check DATABASE_SCHEMA for existing tables/relationships
- [ ] Review QUICK_REFERENCE for similar features
- [ ] Create/modify migrations (database first)
- [ ] Create/modify models with relationships
- [ ] Create controller methods with validation
- [ ] Add routes in modules routes file
- [ ] Create/modify Blade views
- [ ] Test with actual data
- [ ] Check calculations and conversions
- [ ] Verify all relationships working
- [ ] Document the feature

---

## 🆘 TROUBLESHOOTING GUIDE

For specific issues, see:

| Issue | See |
|-------|-----|
| Order not creating | QUICK_REFERENCE → Troubleshooting Checklist |
| Stock allocation wrong | PROJECT_ARCHITECTURE_GUIDE → Stock Management |
| Wrong profit calculation | DATABASE_SCHEMA → ORDER_ITEM_STOCKS |
| Vendor balance incorrect | QUICK_REFERENCE → Vendor Balance |
| Can't cancel order | PROJECT_ARCHITECTURE_GUIDE → Workflows |
| Damage quantity not updating | QUICK_REFERENCE → Damage/Return Processing |
| Invoice not generating | PROJECT_ARCHITECTURE_GUIDE → Workflows → Invoice |
| Views not rendering | PROJECT_ARCHITECTURE_GUIDE → View Hierarchy |
| Routes not working | QUICK_REFERENCE → Routes Quick Reference |

---

## 📊 DATA FLOW DIAGRAMS

All visual diagrams are in the documentation files:

- **High-level workflow**: PROJECT_ARCHITECTURE_GUIDE → Core Workflow
- **Order creation flow**: PROJECT_ARCHITECTURE_GUIDE → Workflow 2
- **Status update flow**: PROJECT_ARCHITECTURE_GUIDE → Workflow 3
- **ER Diagram**: DATABASE_SCHEMA → Entity Relationship Diagram
- **Model relationships**: QUICK_REFERENCE → Key Models & Relationships
- **Template inheritance**: PROJECT_ARCHITECTURE_GUIDE → Blade Template Structure

---

## 🔧 KEY FILES IN PROJECT

### Controllers (Business Logic)
- `Modules/Product/app/Http/Controllers/OrderController.php` ⭐
- `Modules/Product/app/Http/Controllers/DamageReturnLostController.php` ⭐
- `Modules/Product/app/Http/Controllers/ProductController.php`
- `Modules/Product/app/Http/Controllers/StockController.php`
- `Modules/Product/app/Http/Controllers/VendorController.php`

### Models
- `Modules/Product/app/Models/Order.php` ⭐
- `Modules/Product/app/Models/Stock.php` ⭐
- `Modules/Product/app/Models/OrderItemStock.php` ⭐
- `Modules/Product/app/Models/VendorAccount.php` ⭐

### Views
- `Modules/Product/resources/views/order/` ⭐
- `Modules/Product/resources/views/stock/`
- `Modules/Product/resources/views/product/`
- `resources/views/layouts/app.blade.php` (Master layout)

### Routes
- `Modules/Product/routes/web.php` ⭐
- `routes/admin.php` (Admin auth)

---

## 📝 DOCUMENTATION READING ORDER

### For Understanding the Project:
1. Start: PROJECT_ARCHITECTURE_GUIDE → Project Overview
2. Then: PROJECT_ARCHITECTURE_GUIDE → Project Structure & Modules
3. Then: QUICK_REFERENCE → Directory Quick Map
4. Then: PROJECT_ARCHITECTURE_GUIDE → Core Workflow

### For Database Work:
1. Start: DATABASE_SCHEMA → Entity Relationship Diagram
2. Then: DATABASE_SCHEMA → Table Details (for your table)
3. Reference: QUICK_REFERENCE → Database Tables Cheat Sheet

### For Development:
1. Reference: QUICK_REFERENCE (daily)
2. Reference: PROJECT_ARCHITECTURE_GUIDE (for complex logic)
3. Reference: DATABASE_SCHEMA (for data structure)

### For Debugging:
1. Start: QUICK_REFERENCE → Troubleshooting Checklist
2. Then: PROJECT_ARCHITECTURE_GUIDE → Relevant Workflow
3. Then: DATABASE_SCHEMA → Critical Operations

---

## 🎓 LEARNING RESOURCES WITHIN DOCS

Each documentation file includes:
- Code examples
- Formulas and calculations
- Step-by-step workflows
- Relationship diagrams
- Algorithm explanations
- Error handling patterns
- Business logic details

---

## 📞 GETTING HELP

When you need help:

1. **Question about overall structure?**
   → Read PROJECT_ARCHITECTURE_GUIDE

2. **Question about a specific table/model?**
   → Read DATABASE_SCHEMA

3. **Need to remember method/route name?**
   → Read QUICK_REFERENCE

4. **Something broken, need to fix it?**
   → Read QUICK_REFERENCE → Troubleshooting

5. **Implementing new feature?**
   → Read PROJECT_ARCHITECTURE_GUIDE → Implementation Sequence

---

## ✅ WHAT'S COVERED IN THIS DOCUMENTATION

✅ Complete project architecture
✅ All database schemas and relationships
✅ Complete workflow from product to order to return
✅ Smart stock allocation algorithm
✅ Financial tracking (vendor accounts)
✅ View hierarchy and template extension
✅ All controllers and methods
✅ Routes and AJAX endpoints
✅ Code patterns and examples
✅ Common queries
✅ Troubleshooting guide
✅ Implementation checklist
✅ Formula and calculations

---

## 📄 FILE STATISTICS

| File | Size | Sections | Purpose |
|------|------|----------|---------|
| PROJECT_ARCHITECTURE_GUIDE.md | Large | 13 | Complete guide |
| DATABASE_SCHEMA.md | Large | 11 | Database details |
| QUICK_REFERENCE.md | Medium | 18 | Daily reference |
| DOCUMENTATION_INDEX.md | This file | - | Navigation guide |

---

## 🔄 Keep Documentation Updated

When making changes to the project:
- Update relevant documentation sections
- Add new workflow steps if business logic changes
- Update DATABASE_SCHEMA if tables are created/modified
- Update QUICK_REFERENCE if routes or methods change

---

**Documentation Version**: 1.0  
**Last Updated**: November 13, 2025  
**Maintained By**: Development Team  
**Scope**: Dealership App - Complete Project Documentation

---

**Next Steps:**
1. Open PROJECT_ARCHITECTURE_GUIDE.md for complete understanding
2. Keep QUICK_REFERENCE.md open while coding
3. Reference DATABASE_SCHEMA.md when working with data
4. Use this index to navigate between documents
