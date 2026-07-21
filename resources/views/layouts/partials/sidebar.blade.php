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

        <li class="{{ request()->routeIs('admin.plans.*') ? 'active' : '' }}">
            <a href="{{ route('admin.plans.index') }}">
                <i class="bx bx-task"></i>
                <span>Official PT Plans</span>
            </a>
        </li>

        <li class="{{ request()->routeIs('admin.batches.*') ? 'active' : '' }}">
            <a href="{{ route('admin.batches.index') }}">
                <i class="bx bx-box"></i>
                <span>Sample Production</span>
            </a>
        </li>

        <li class="{{ request()->routeIs('admin.samples.*') ? 'active' : '' }}">
            <a href="{{ route('admin.samples.index') }}">
                <i class="bx bx-qr-scan"></i>
                <span>Sample Assignment</span>
            </a>
        </li>

        <li class="{{ request()->routeIs('admin.dispatches.*') ? 'active' : '' }}">
            <a href="{{ route('admin.dispatches.index') }}">
                <i class="bx bx-package"></i>
                <span>Sample Dispatches</span>
            </a>
        </li>

        <li class="{{ request()->routeIs('admin.observations.*') ? 'active' : '' }}">
            <a href="{{ route('admin.observations.index') }}">
                <i class="bx bx-test-tube"></i>
                <span>Observations</span>
            </a>
        </li>

        <li class="{{ request()->routeIs('admin.stats.*') ? 'active' : '' }}">
            <a href="{{ route('admin.stats.index') }}">
                <i class="bx bx-line-chart"></i>
                <span>Statistical Analysis</span>
            </a>
        </li>

        <li class="{{ request()->routeIs('admin.participants.*') ? 'active' : '' }}">
            <a href="{{ route('admin.participants.index') }}">
                <i class="bx bx-group"></i>
                <span>Participants</span>
            </a>
        </li>

        <li class="{{ request()->routeIs('admin.referrals.*') ? 'active' : '' }}">
            <a href="{{ route('admin.referrals.index') }}">
                <i class="bx bx-purchase-tag-alt"></i>
                <span>Referral Codes</span>
            </a>
        </li>

        <li class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
            <a href="{{ route('admin.reports.index') }}">
                <i class="bx bx-award"></i>
                <span>Reports & Certs</span>
            </a>
        </li>

        <li class="{{ request()->routeIs('admin.archive.*') ? 'active' : '' }}">
            <a href="{{ route('admin.archive.index') }}">
                <i class="bx bx-archive"></i>
                <span>Historical Archive</span>
            </a>
        </li>
    </ul>
</nav>
