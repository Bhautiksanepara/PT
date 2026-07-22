<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Participant Portal') - PT Software</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Boxicons -->
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <!-- Google Fonts (Inter) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #334155;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .user-navbar {
            background-color: #0f172a;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            letter-spacing: 0.5px;
            color: #ffffff !important;
        }

        .main-container {
            flex: 1;
            padding: 32px 0;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            background: #ffffff;
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid #f1f5f9;
            padding: 18px 24px;
        }

        .badge-soft-success { background: #d1fae5; color: #065f46; font-weight: 600; }
        .badge-soft-warning { background: #fef3c7; color: #92400e; font-weight: 600; }
        .badge-soft-danger { background: #fee2e2; color: #991b1b; font-weight: 600; }
        .badge-soft-info { background: #e0f2fe; color: #075985; font-weight: 600; }

        footer {
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
            padding: 20px 0;
            font-size: 0.85rem;
            color: #64748b;
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- User Navbar -->
    <nav class="navbar navbar-expand-lg user-navbar py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('user.dashboard') }}">
                <div class="bg-primary text-white p-2 rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                    <i class="bx bx-flask fs-4"></i>
                </div>
                <div>
                    <span class="fs-5 fw-bold">PT SOFTWARE</span>
                    <small class="d-block text-white-50 micro-text" style="font-size: 0.7rem;">PARTICIPANT PORTAL</small>
                </div>
            </a>

            <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse" data-bs-target="#userNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="userNavbar">
                @auth('lab')
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                        <li class="nav-item">
                            <a class="nav-link text-white fw-semibold {{ request()->routeIs('user.dashboard') ? 'active border-bottom border-primary border-2' : 'text-white-50' }}" href="{{ route('user.dashboard') }}">
                                <i class="bx bx-grid-alt me-1"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white fw-semibold {{ request()->routeIs('user.dispatches.*') ? 'active border-bottom border-primary border-2' : 'text-white-50' }}" href="{{ route('user.dispatches.index') }}">
                                <i class="bx bx-package me-1"></i> Sample Dispatches
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white fw-semibold {{ request()->routeIs('user.observations.*') || request()->routeIs('lab.observations.*') ? 'active border-bottom border-primary border-2' : 'text-white-50' }}" href="{{ route('user.observations.index') }}">
                                <i class="bx bx-vial me-1"></i> Test Observations
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white fw-semibold {{ request()->routeIs('user.reports.*') || request()->routeIs('lab.reports.*') ? 'active border-bottom border-primary border-2' : 'text-white-50' }}" href="{{ route('user.reports.index') }}">
                                <i class="bx bx-award me-1"></i> Reports & Certs
                            </a>
                        </li>
                    </ul>

                    <div class="d-flex align-items-center gap-3">
                        <div class="text-end d-none d-md-block">
                            <div class="fw-bold text-white small">{{ Auth::guard('lab')->user()->laboratory_name }}</div>
                            <small class="text-white-50 micro-text font-monospace">{{ Auth::guard('lab')->user()->username }}</small>
                        </div>

                        <form action="{{ route('user.logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-light btn-sm px-3">
                                <i class="bx bx-log-out me-1"></i> Logout
                            </button>
                        </form>
                    </div>
                @else
                    <div class="ms-auto d-flex gap-2">
                        <a href="{{ route('user.login') }}" class="btn btn-outline-light btn-sm px-3">Log In</a>
                        <a href="{{ route('user.register') }}" class="btn btn-primary btn-sm px-3">Register Lab</a>
                    </div>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-container">
        <div class="container">
            <!-- Flash Messages -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bx bx-check-circle me-1 fs-5 align-middle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="bx bx-error-circle me-1 fs-5 align-middle"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="mt-auto">
        <div class="container text-center">
            <p class="mb-0">&copy; {{ date('Y') }} ISO/IEC 17043 Proficiency Testing Software. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Form Submission Disabling (Form submit buttons)
            document.addEventListener('submit', function (event) {
                var form = event.target;
                if (form && form.tagName === 'FORM' && form.getAttribute('target') !== '_blank') {
                    var submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
                    submitButtons.forEach(function (btn) {
                        btn.disabled = true;
                        if (btn.tagName === 'BUTTON') {
                            var originalHtml = btn.innerHTML;
                            btn.setAttribute('data-original-html', originalHtml);
                            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processing...';
                        }
                    });

                    // Fallback timeout to re-enable if page does not redirect/reload
                    setTimeout(function() {
                        submitButtons.forEach(function (btn) {
                            btn.disabled = false;
                            if (btn.tagName === 'BUTTON' && btn.getAttribute('data-original-html')) {
                                btn.innerHTML = btn.getAttribute('data-original-html');
                            }
                        });
                    }, 4000);
                }
            });

            // 2. Direct Navigation Buttons (Link/Action buttons)
            document.addEventListener('click', function (event) {
                var btn = event.target.closest('.btn, button');
                if (!btn) return;

                // We only show "Loading..." for actual navigation link anchors
                if (btn.tagName === 'A') {
                    var href = btn.getAttribute('href');
                    
                    // Exclude modal/collapse toggles, anchor hash links, javascript scripts, or target blanks
                    if (!href || href.startsWith('#') || href.startsWith('javascript:') || 
                        btn.getAttribute('data-bs-toggle') || btn.getAttribute('data-bs-dismiss') || 
                        btn.getAttribute('target') === '_blank' || btn.classList.contains('no-loader')) {
                        return;
                    }

                    if (btn.classList.contains('clicked-loading')) {
                        event.preventDefault();
                        return;
                    }

                    btn.classList.add('clicked-loading');
                    btn.classList.add('disabled');
                    btn.style.pointerEvents = 'none';

                    var originalHtml = btn.innerHTML;
                    btn.setAttribute('data-original-html', originalHtml);
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Loading...';

                    // Safety timeout to restore button state
                    setTimeout(function() {
                        btn.classList.remove('clicked-loading');
                        btn.classList.remove('disabled');
                        btn.style.pointerEvents = 'auto';
                        if (btn.getAttribute('data-original-html')) {
                            btn.innerHTML = btn.getAttribute('data-original-html');
                        }
                    }, 5000);
                }
            });
        });
    </script>

    @stack('scripts')
</body>
</html>
