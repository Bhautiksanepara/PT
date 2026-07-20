<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Dashboard') | PT Software</title>

    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Boxicons CSS -->
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <style>
        :root {
            --sidebar-bg: #0f172a;
            --sidebar-hover: #1e293b;
            --sidebar-active: #2563eb;
            --sidebar-text: #94a3b8;
            --sidebar-text-active: #ffffff;
            --topbar-bg: #ffffff;
            --body-bg: #f8fafc;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--body-bg);
            color: #334155;
            min-height: 100vh;
        }

        /* Layout Structure */
        #wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
        }

        /* Sidebar Styling */
        #sidebar {
            min-width: 260px;
            max-width: 260px;
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            transition: all 0.3s;
            min-height: 100vh;
            z-index: 1000;
        }

        #sidebar .sidebar-header {
            padding: 20px 24px;
            background: #090d16;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        #sidebar .sidebar-header h4 {
            color: #ffffff;
            font-weight: 700;
            font-size: 1.15rem;
            margin: 0;
            letter-spacing: 0.5px;
        }

        #sidebar ul.components {
            padding: 15px 10px;
        }

        #sidebar ul p {
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            padding: 10px 15px 5px 15px;
            margin: 0;
            letter-spacing: 1px;
        }

        #sidebar ul li a {
            padding: 11px 16px;
            font-size: 0.9rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--sidebar-text);
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 4px;
            transition: all 0.2s ease;
        }

        #sidebar ul li a i {
            font-size: 1.25rem;
        }

        #sidebar ul li a:hover {
            color: #ffffff;
            background: var(--sidebar-hover);
        }

        #sidebar ul li.active > a {
            color: var(--sidebar-text-active);
            background: var(--sidebar-active);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        /* Content Area */
        #content {
            width: 100%;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Navbar */
        .top-navbar {
            background: var(--topbar-bg);
            border-bottom: 1px solid #e2e8f0;
            padding: 12px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .search-box {
            position: relative;
            width: 320px;
        }

        .search-box input {
            padding-left: 38px;
            border-radius: 20px;
            background-color: #f1f5f9;
            border: 1px solid #e2e8f0;
            font-size: 0.875rem;
        }

        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 1.1rem;
        }

        /* Main Container */
        .main-content {
            padding: 28px;
            flex: 1;
        }

        /* Card Customization */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            margin-bottom: 24px;
            background: #ffffff;
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid #f1f5f9;
            padding: 18px 24px;
            font-weight: 600;
        }

        .card-body {
            padding: 24px;
        }

        /* Stat Cards */
        .stat-card {
            border-left: 4px solid;
            transition: transform 0.2s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-card.blue { border-color: #3b82f6; }
        .stat-card.green { border-color: #10b981; }
        .stat-card.purple { border-color: #8b5cf6; }
        .stat-card.orange { border-color: #f59e0b; }
        .stat-card.teal { border-color: #14b8a6; }
        .stat-card.red { border-color: #ef4444; }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .bg-blue-light { background: #dbeafe; color: #1d4ed8; }
        .bg-green-light { background: #d1fae5; color: #047857; }
        .bg-purple-light { background: #ede9fe; color: #6d28d9; }
        .bg-orange-light { background: #fef3c7; color: #b45309; }
        .bg-teal-light { background: #ccfbf1; color: #0f766e; }
        .bg-red-light { background: #fee2e2; color: #b91c1c; }

        /* Badge Customization */
        .badge-soft-success { background: #d1fae5; color: #065f46; font-weight: 600; }
        .badge-soft-warning { background: #fef3c7; color: #92400e; font-weight: 600; }
        .badge-soft-danger { background: #fee2e2; color: #991b1b; font-weight: 600; }
        .badge-soft-info { background: #e0f2fe; color: #075985; font-weight: 600; }
        .badge-soft-secondary { background: #f1f5f9; color: #475569; font-weight: 600; }

        /* Tables */
        .table > :not(caption) > * > * {
            padding: 12px 16px;
            vertical-align: middle;
        }

        .table th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            font-weight: 600;
            background-color: #f8fafc;
        }
    </style>

    @stack('styles')
</head>
<body>
    <div id="wrapper">
        <!-- Sidebar -->
        @include('layouts.partials.sidebar')

        <!-- Page Content -->
        <div id="content">
            <!-- Navbar -->
            @include('layouts.partials.navbar')

            <!-- Main Content Area -->
            <div class="main-content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </div>

            <!-- Footer -->
            <footer class="bg-white border-top py-3 px-4 text-center text-muted small">
                &copy; {{ date('Y') }} PT Software — Proficiency Testing Management System. All rights reserved.
            </footer>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    @stack('scripts')
</body>
</html>
