<nav id="sidebar">
    <div class="sidebar-header">
        <i class="bx bx-flask text-primary display-6"></i>
        <div>
            <h4>PT SOFTWARE</h4>
            <small class="text-muted" style="font-size: 11px;">ISO 17043 Portal</small>
        </div>
    </div>

    <ul class="list-unstyled components">
        <p>Main Menu</p>
        
        <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <a href="{{ route('admin.dashboard') }}">
                <i class="bx bx-grid-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <p>Management</p>

        <li class="{{ request()->routeIs('admin.programs.*') ? 'active' : '' }}">
            <a href="{{ route('admin.programs.index') }}">
                <i class="bx bx-layer"></i>
                <span>PT Programs</span>
            </a>
        </li>

        <li class="{{ request()->routeIs('admin.participants.*') ? 'active' : '' }}">
            <a href="{{ route('admin.participants.index') }}">
                <i class="bx bx-group"></i>
                <span>Participants</span>
            </a>
        </li>

        <p>Upcoming Modules</p>

        <li class="opacity-50">
            <a href="javascript:void(0)" onclick="alert('Module under development (Phase 5)')">
                <i class="bx bx-box"></i>
                <span>Sample Dispatches</span>
            </a>
        </li>

        <li class="opacity-50">
            <a href="javascript:void(0)" onclick="alert('Module under development (Phase 6)')">
                <i class="bx bx-file-find"></i>
                <span>Observations</span>
            </a>
        </li>

        <li class="opacity-50">
            <a href="javascript:void(0)" onclick="alert('Module under development (Phase 7)')">
                <i class="bx bx-line-chart"></i>
                <span>Statistical Engine</span>
            </a>
        </li>

        <li class="opacity-50">
            <a href="javascript:void(0)" onclick="alert('Module under development (Phase 8)')">
                <i class="bx bx-certification"></i>
                <span>Reports & Certs</span>
            </a>
        </li>
    </ul>
</nav>
