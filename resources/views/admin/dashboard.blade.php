@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Laboratory Information Dashboard</h4>
        <p class="text-muted small mb-0">Overview of PT Programs, Participants, Dispatches & Revenue</p>
    </div>
    <div>
        <a href="{{ route('admin.programs.create') }}" class="btn btn-primary shadow-sm btn-sm px-3">
            <i class="bx bx-plus me-1"></i> Create PT Program
        </a>
    </div>
</div>

<!-- Row 1: 6 Stat Cards -->
<div class="row g-3 mb-4">
    <!-- Total Participants -->
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="card stat-card blue h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <div style="min-width: 0;">
                        <div class="text-muted small fw-semibold text-truncate">Participants</div>
                        <h3 class="fw-bold mb-0 mt-1">{{ number_format($totalParticipants) }}</h3>
                    </div>
                    <div class="stat-icon bg-blue-light flex-shrink-0">
                        <i class="bx bx-group"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Active Programs -->
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="card stat-card green h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <div style="min-width: 0;">
                        <div class="text-muted small fw-semibold text-truncate">Active Programs</div>
                        <h3 class="fw-bold mb-0 mt-1 text-success">{{ number_format($activePrograms) }}</h3>
                    </div>
                    <div class="stat-icon bg-green-light flex-shrink-0">
                        <i class="bx bx-layer"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Revenue -->
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="card stat-card purple h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center gap-1">
                    <div style="min-width: 0;">
                        <div class="text-muted small fw-semibold text-truncate">Revenue</div>
                        <h5 class="fw-bold mb-0 mt-1 text-purple" style="font-size: 1.1rem; white-space: nowrap;">
                            ₹{{ $totalRevenue == floor($totalRevenue) ? number_format($totalRevenue) : number_format($totalRevenue, 2) }}
                        </h5>
                    </div>
                    <div class="stat-icon bg-purple-light flex-shrink-0" style="width: 38px; height: 38px; font-size: 1.15rem;">
                        <i class="bx bx-wallet"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Dispatches -->
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="card stat-card orange h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <div style="min-width: 0;">
                        <div class="text-muted small fw-semibold text-truncate">Dispatches Pending</div>
                        <h3 class="fw-bold mb-0 mt-1 text-warning">{{ number_format($pendingDispatches) }}</h3>
                    </div>
                    <div class="stat-icon bg-orange-light flex-shrink-0">
                        <i class="bx bx-package"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Submitted Observations -->
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="card stat-card teal h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <div style="min-width: 0;">
                        <div class="text-muted small fw-semibold text-truncate">Observations</div>
                        <h3 class="fw-bold mb-0 mt-1 text-info">{{ number_format($submittedObservations) }}</h3>
                    </div>
                    <div class="stat-icon bg-teal-light flex-shrink-0">
                        <i class="bx bx-file-find"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Reports -->
    <div class="col-xl-2 col-lg-4 col-md-6">
        <div class="card stat-card red h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <div style="min-width: 0;">
                        <div class="text-muted small fw-semibold text-truncate">Reports Pending</div>
                        <h3 class="fw-bold mb-0 mt-1 text-danger">{{ number_format($pendingReports) }}</h3>
                    </div>
                    <div class="stat-icon bg-red-light flex-shrink-0">
                        <i class="bx bx-time-five"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Row 2: Charts -->
<div class="row g-3 mb-4">
    <!-- PT Programs Distribution Chart -->
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bx bx-doughnut-chart me-1 text-primary"></i> PT Program Status Distribution</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <div id="programStatusChart" style="width: 100%; min-height: 280px;"></div>
            </div>
        </div>
    </div>

    <!-- Revenue & Registration Trend -->
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="mb-0 fw-bold"><i class="bx bx-trending-up me-1 text-success"></i> PT Performance & Revenue Overview</h6>
                <form action="{{ route('admin.dashboard') }}" method="GET" class="d-flex align-items-center gap-1">
                    <select name="chart_range" class="form-select form-select-sm shadow-sm" onchange="this.form.submit()" style="width: auto; min-width: 140px;">
                        <option value="12_months" {{ $chartRange === '12_months' ? 'selected' : '' }}>Last 12 Months</option>
                        @foreach($availableYears as $yearVal)
                            <option value="{{ $yearVal }}" {{ (string)$chartRange === (string)$yearVal ? 'selected' : '' }}>Year {{ $yearVal }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="card-body">
                <div id="revenueTrendChart" style="width: 100%; min-height: 280px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Row 3: Recent Activity & Notifications -->
<div class="row g-3">
    <!-- Recent Registrations -->
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold"><i class="bx bx-list-check me-1 text-primary"></i> Recent Program Registrations</h6>
                <a href="{{ route('admin.participants.index') }}" class="btn btn-link btn-sm text-decoration-none">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Reg No</th>
                                <th>Laboratory</th>
                                <th>Program Code</th>
                                <th>Payment</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentRegistrations as $reg)
                                <tr>
                                    <td class="fw-semibold text-primary">{{ $reg->registration_number }}</td>
                                    <td>{{ $reg->laboratory_name }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $reg->program_code }}</span></td>
                                    <td>
                                        @if(($reg->payment_status ?? '') === 'success')
                                            <span class="badge badge-soft-success">Paid</span>
                                        @elseif(($reg->payment_status ?? '') === 'pending')
                                            <span class="badge badge-soft-warning">Pending</span>
                                        @else
                                            <span class="badge badge-soft-secondary">Unpaid</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">{{ \Carbon\Carbon::parse($reg->registered_at)->format('d M Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No recent registrations found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Shortcuts & Info -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0 fw-bold"><i class="bx bx-rocket me-1 text-warning"></i> Quick Management Links</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('admin.programs.create') }}" class="btn btn-outline-primary text-start p-3">
                        <i class="bx bx-plus-circle me-2 fs-5"></i> Create New PT Program
                    </a>
                    <a href="{{ route('admin.programs.index') }}" class="btn btn-outline-secondary text-start p-3">
                        <i class="bx bx-layer me-2 fs-5"></i> Manage All Programs
                    </a>
                    <a href="{{ route('admin.participants.index') }}" class="btn btn-outline-success text-start p-3">
                        <i class="bx bx-group me-2 fs-5"></i> View Registered Laboratories
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // 1. Program Status Donut Chart
        var statusOptions = {
            series: [
                {{ $programStatusCounts['draft'] }},
                {{ $programStatusCounts['open'] }},
                {{ $programStatusCounts['closed'] }},
                {{ $programStatusCounts['completed'] }}
            ],
            labels: ['Draft', 'Open (Active)', 'Closed', 'Completed'],
            chart: {
                type: 'donut',
                height: 280
            },
            colors: ['#94a3b8', '#10b981', '#ef4444', '#3b82f6'],
            legend: {
                position: 'bottom'
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: { width: 200 },
                    legend: { position: 'bottom' }
                }
            }]
        };
        var statusChart = new ApexCharts(document.querySelector("#programStatusChart"), statusOptions);
        statusChart.render();

        // 2. Revenue & Registrations Trend Chart (REAL DYNAMIC DATA)
        var trendOptions = {
            series: [{
                name: 'Registrations',
                type: 'column',
                data: @json($monthlyRegistrations)
            }, {
                name: 'Revenue (₹)',
                type: 'line',
                data: @json($monthlyRevenue)
            }],
            chart: {
                height: 280,
                type: 'line',
            },
            stroke: {
                width: [0, 3],
                curve: 'smooth'
            },
            colors: ['#3b82f6', '#10b981'],
            xaxis: {
                categories: @json($monthsLabels),
            },
            yaxis: [{
                title: { text: 'Registrations' },
            }, {
                opposite: true,
                title: { text: 'Revenue (₹)' }
            }]
        };
        var trendChart = new ApexCharts(document.querySelector("#revenueTrendChart"), trendOptions);
        trendChart.render();
    });
</script>
@endpush
