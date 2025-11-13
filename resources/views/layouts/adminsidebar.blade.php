<aside class="app-sidebar bg-white shadow" data-bs-theme="light"> <!--begin::Sidebar Brand-->
    <div class="sidebar-brand"> <!--begin::Brand Link-->
        <a href="{{ route('admin.dashboard') }}" class="brand-link">

                <span class="brand-text fw-light">PARK</span> <!--end::Brand Text-->
            </a> <!--end::Brand Link--> </div>
    <!--end::Sidebar Brand--> <!--begin::Sidebar Wrapper-->
    <div class="sidebar-wrapper">
        <nav class="mt-2"> <!--begin::Sidebar Menu-->
            <ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" role="menu" data-accordion="false">

                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link">
                        <i class="nav-icon bi bi-border"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                <li class="nav-item {{ request()->is('admin/user/*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('admin/user/*') ? 'active' : '' }}">
                        <span class="nav-icon mdi mdi-account-multiple"></span>
                        <p>
                            User Management
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.adminUserIndex') }}" class="nav-link {{ request()->is('admin/user/index') || request()->is('admin/user/*/edit') || request()->is('admin/user/create') ? 'active' : '' }}">
                                <i class="nav-icon mdi mdi-account-tie"></i>
                                <p>Admin Users</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item {{ request()->is('admin/role/permission/*') ? 'menu-open' : '' }}"> <a href="#" class="nav-link {{ request()->is('admin/role/permission/*') ? 'active' : '' }}">  <span class="nav-icon mdi mdi-shield-lock"></span>
                        <p>
                           Role & Permission
                            <i class="nav-arrow bi bi-chevron-right"></i>

                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item"> <a href="{{ route('admin.roleIndex') }}" class="nav-link {{ request()->is('admin/role/permission/role/*') ? 'active' : '' }}"> <i
                                    class="nav-icon mdi mdi-sort-variant-lock"></i>
                                <p>Role List</p>
                            </a> </li>
                        <li class="nav-item"> <a href="{{ route('admin.permissionIndex') }}" class="nav-link {{ request()->is('admin/role/permission/permission/*') ? 'active' : '' }}"> <i
                                    class="nav-icon mdi mdi-axis-lock"></i>
                                <p>Permission List</p>
                            </a> </li>
                    </ul>
                </li>




                <li class="nav-header">INVENTORY MANAGEMENT</li>

                <!-- Products Section -->
                <li class="nav-item {{ request()->is('admin/product/*') || request()->is('admin/color*') || request()->is('admin/unit*') || request()->is('admin/company*') || request()->is('admin/brand*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('admin/product/*') || request()->is('admin/color*') || request()->is('admin/unit*') || request()->is('admin/company*') || request()->is('admin/brand*') ? 'active' : '' }}">
                        <i class="nav-icon mdi mdi-package-variant-closed"></i>
                        <p>
                            Products
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">

                        <li class="nav-item">
                            <a href="{{ route('admin.companyIndex') }}" class="nav-link {{ request()->is('admin/company/*') ? 'active' : '' }}">
                             <i class="nav-icon mdi mdi-domain"></i>
                                <p>Companies</p>
                            </a> </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.brandIndex') }}" class="nav-link {{ request()->is('admin/brand/*') ? 'active' : '' }}">
                             <i class="nav-icon mdi mdi-watermark"></i>
                                <p>Brands</p>
                        </a> </li>

                        <li class="nav-item">
                            <a href="{{ route('admin.productIndex') }}" class="nav-link {{ request()->is('admin/product/*') ? 'active' : '' }}">
                                <i class="nav-icon mdi mdi-package"></i>
                                <p>Products</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.colorIndex') }}" class="nav-link {{ request()->is('admin/color*') ? 'active' : '' }}">
                                <i class="nav-icon mdi mdi-palette"></i>
                                <p>Colors</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.unitIndex') }}" class="nav-link {{ request()->is('admin/unit*') ? 'active' : '' }}">
                                <i class="nav-icon mdi mdi-scale-balance"></i>
                                <p>Units</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Stock Management -->
                <li class="nav-item {{ request()->is('admin/stock/*') || request()->is('admin/warehouse/*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('admin/stock/*') || request()->is('admin/warehouse/*') ? 'active' : '' }}">
                        <i class="nav-icon mdi mdi-warehouse"></i>
                        <p>
                            Stock Management
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.stockIndex') }}" class="nav-link {{ request()->is('admin/stock/*') ? 'active' : '' }}">
                                <i class="nav-icon mdi mdi-package-variant"></i>
                                <p>Stock Overview</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.warehouseIndex') }}" class="nav-link {{ request()->is('admin/warehouse/*') ? 'active' : '' }}">
                                <i class="nav-icon mdi mdi-home-city"></i>
                                <p>Warehouses</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Vendors -->
                <li class="nav-item">
                    <a href="{{ route('admin.vendorIndex') }}" class="nav-link {{ request()->is('admin/vendor/*') ? 'active' : '' }}">
                        <i class="nav-icon mdi mdi-account-group"></i>
                        <p>Vendors</p>
                    </a>
                </li>

                <!-- Orders Section -->
                <li class="nav-item {{ request()->is('admin/order/*') || request()->is('order/*') || request()->is('admin/order-status/*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('admin/order/*') || request()->is('order/*') || request()->is('admin/order-status/*') ? 'active' : '' }}">
                        <i class="nav-icon mdi mdi-cart"></i>
                        <p>
                            Orders
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('orders.index') }}" class="nav-link {{ request()->is('admin/order/index') ? 'active' : '' }}">
                                <i class="nav-icon mdi mdi-format-list-bulleted"></i>
                                <p>All Orders</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('orders.create') }}" class="nav-link {{ request()->is('admin/order/create') ? 'active' : '' }}">
                                <i class="nav-icon mdi mdi-plus-circle"></i>
                                <p>Create Order</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.orderStatusIndex') }}" class="nav-link {{ request()->is('admin/order-status/*') ? 'active' : '' }}">
                                <i class="nav-icon mdi mdi-progress-check"></i>
                                <p>Order Status</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Financial Section -->
                <li class="nav-item {{ request()->is('admin/payment-collection/*') || request()->is('admin/invoice/*') || request()->is('admin/payment-method/*') || request()->is('admin/expense-head/*') || request()->is('admin/expense-list/*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('admin/payment-collection/*') || request()->is('admin/invoice/*') || request()->is('admin/payment-method/*') || request()->is('admin/expense-head/*') || request()->is('admin/expense-list/*') ? 'active' : '' }}">
                        <i class="nav-icon mdi mdi-finance"></i>
                        <p>
                            Financial
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('payment-collections.index') }}" class="nav-link {{ request()->is('admin/payment-collection/*') ? 'active' : '' }}">
                                <i class="nav-icon mdi mdi-cash-multiple"></i>
                                <p>Payment Collection</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('invoices.index') }}" class="nav-link {{ request()->is('admin/invoice/*') ? 'active' : '' }}">
                                <i class="nav-icon mdi mdi-file-document"></i>
                                <p>Invoices</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.paymentMethodIndex') }}" class="nav-link {{ request()->is('admin/payment-method/*') ? 'active' : '' }}">
                                <i class="nav-icon mdi mdi-credit-card"></i>
                                <p>Payment Methods</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.expenseHeadIndex') }}" class="nav-link {{ request()->is('admin/expense-head/*') ? 'active' : '' }}">
                                <i class="nav-icon mdi mdi-format-list-text"></i>
                                <p>Expense Heads</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.expenseListIndex') }}" class="nav-link {{ request()->is('admin/expense-list/*') ? 'active' : '' }}">
                                <i class="nav-icon mdi mdi-currency-usd"></i>
                                <p>Expense Lists</p>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Reports & Issues -->
                <li class="nav-item {{ request()->is('damage-return-lost/*') ? 'menu-open' : '' }}">
                    <a href="#" class="nav-link {{ request()->is('damage-return-lost/*') ? 'active' : '' }}">
                        <i class="nav-icon mdi mdi-alert-circle"></i>
                        <p>
                            Reports & Issues
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('damage-return-lost.index') }}" class="nav-link {{ request()->is('damage-return-lost/*') ? 'active' : '' }}">
                                <i class="nav-icon mdi mdi-package-down"></i>
                                <p>Damage/Return/Lost</p>
                            </a>
                        </li>
                    </ul>
                </li>

            </ul> <!--end::Sidebar Menu-->
        </nav>
    </div> <!--end::Sidebar Wrapper-->
</aside>
