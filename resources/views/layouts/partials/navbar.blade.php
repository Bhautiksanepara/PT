<header class="top-navbar">
    <div class="search-box">
        <i class="bx bx-search"></i>
        <input type="text" class="form-control border-0" placeholder="Search programs, participants...">
    </div>

    <div class="d-flex align-items-center gap-3">
        <!-- Notification Dropdown -->
        <div class="dropdown">
            <button class="btn btn-light btn-icon rounded-circle position-relative p-2" type="button" data-bs-toggle="dropdown">
                <i class="bx bx-bell fs-5 text-secondary"></i>
                <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="width: 280px;">
                <li><h6 class="dropdown-header border-bottom py-2">Notifications</h6></li>
                <li><a class="dropdown-item py-2 small" href="#"><i class="bx bx-info-circle text-primary me-2"></i>New lab registered: Apex Analytical</a></li>
                <li><a class="dropdown-item py-2 small" href="#"><i class="bx bx-check-circle text-success me-2"></i>PT-CHEM-2026-01 is now Active</a></li>
            </ul>
        </div>

        <div class="vr h-100 mx-1"></div>

        <!-- User Profile Dropdown -->
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center gap-2 text-decoration-none dropdown-toggle text-dark" data-bs-toggle="dropdown">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 36px; height: 36px;">
                    {{ substr(Auth::guard('admin')->user()->full_name ?? 'Admin', 0, 1) }}
                </div>
                <div class="d-none d-md-block text-start">
                    <div class="fw-semibold small" style="line-height: 1.2;">{{ Auth::guard('admin')->user()->full_name ?? 'Administrator' }}</div>
                    <small class="text-muted" style="font-size: 11px;">System Admin</small>
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm mt-2">
                <li><a class="dropdown-item" href="#"><i class="bx bx-user me-2 text-muted"></i> Profile Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="{{ route('admin.logout') }}" method="POST" id="logout-form">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger">
                            <i class="bx bx-log-out me-2"></i> Log Out
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
