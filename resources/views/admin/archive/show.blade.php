@extends('layouts.app')

@section('title', 'Archive Vault')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bx bx-archive text-secondary me-1"></i> Archive Vault: {{ $program->program_code }}</h4>
        <p class="text-muted small mb-0">{{ $program->program_name }} (Discipline: {{ $program->discipline }})</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.reports.master', $program->program_id) }}" class="btn btn-outline-primary btn-sm">
            <i class="bx bx-table me-1"></i> View Master Matrix
        </a>
        <a href="{{ route('admin.archive.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i> Back to Archive
        </a>
    </div>
</div>

<!-- Key Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card card-stat text-center py-3">
            <small class="text-muted fw-semibold">Registered Labs</small>
            <h3 class="fw-bold text-primary mb-0 mt-1">{{ $totalRegistrations }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat text-center py-3">
            <small class="text-muted fw-semibold">Submitted Observations</small>
            <h3 class="fw-bold text-success mb-0 mt-1">{{ $totalObservations }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat text-center py-3">
            <small class="text-muted fw-semibold">Dispatches Logged</small>
            <h3 class="fw-bold text-info mb-0 mt-1">{{ $totalDispatches }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat text-center py-3">
            <small class="text-muted fw-semibold">Financial Revenue</small>
            <h3 class="fw-bold text-dark mb-0 mt-1">₹{{ number_format($totalRevenue, 2) }}</h3>
        </div>
    </div>
</div>

<!-- Tabbed Archive Vault Content -->
<div class="card shadow-sm">
    <div class="card-header bg-light">
        <ul class="nav nav-tabs card-header-tabs" id="archiveTabs" role="tablist">
            <li class="nav-item">
                <button class="nav-link active fw-bold" id="parameters-tab" data-bs-toggle="tab" data-bs-target="#parameters" type="button">
                    <i class="bx bx-test-tube me-1"></i> Parameters & Stats
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-bold" id="labs-tab" data-bs-toggle="tab" data-bs-target="#labs" type="button">
                    <i class="bx bx-building me-1"></i> Registered Labs ({{ $program->registrations->count() }})
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-bold" id="dispatches-tab" data-bs-toggle="tab" data-bs-target="#dispatches" type="button">
                    <i class="bx bx-package me-1"></i> Dispatches
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link fw-bold" id="documents-tab" data-bs-toggle="tab" data-bs-target="#documents" type="button">
                    <i class="bx bx-file me-1"></i> Reports & Certificates
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="archiveTabsContent">
            <!-- Tab 1: Parameters -->
            <div class="tab-pane fade show active" id="parameters">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Parameter Name</th>
                                <th>Test Method</th>
                                <th>Unit</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($program->parameters as $param)
                                <tr>
                                    <td class="fw-bold text-dark">{{ $param->parameter_name }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $param->test_method }}</span></td>
                                    <td>{{ $param->unit }}</td>
                                    <td>
                                        <a href="{{ route('admin.stats.parameter', [$program->program_id, $param->parameter_id]) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="bx bx-line-chart me-1"></i> View ISO 13528 Stats
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">No parameters.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 2: Registered Labs -->
            <div class="tab-pane fade" id="labs">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Registration #</th>
                                <th>Laboratory Name</th>
                                <th>NABL Cert #</th>
                                <th>Sample Code</th>
                                <th>Payment Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($program->registrations as $reg)
                                <tr>
                                    <td class="fw-bold text-primary">{{ $reg->registration_number }}</td>
                                    <td class="fw-bold text-dark">{{ $reg->lab->laboratory_name ?? 'N/A' }}</td>
                                    <td>{{ $reg->lab->nabl_certificate_number ?? 'N/A' }}</td>
                                    <td>
                                        @if($reg->sample)
                                            <span class="badge bg-light text-dark border"><i class="bx bx-qr-scan me-1"></i> {{ $reg->sample->sample_code }}</span>
                                        @else
                                            <span class="badge bg-secondary">Unassigned</span>
                                        @endif
                                    </td>
                                    <td class="fw-bold text-success">₹{{ number_format($reg->payment->final_amount ?? 0, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-3">No registrations.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 3: Dispatches -->
            <div class="tab-pane fade" id="dispatches">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Sample Code</th>
                                <th>Laboratory Name</th>
                                <th>Courier Partner</th>
                                <th>Tracking Number</th>
                                <th>Dispatch Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($program->registrations as $reg)
                                @if($reg->sample && $reg->sample->dispatch)
                                    @php $disp = $reg->sample->dispatch; @endphp
                                    <tr>
                                        <td class="fw-bold text-primary">{{ $reg->sample->sample_code }}</td>
                                        <td class="fw-bold text-dark">{{ $reg->lab->laboratory_name ?? 'N/A' }}</td>
                                        <td>{{ $disp->courier_name }}</td>
                                        <td class="font-monospace bg-light p-1 rounded">{{ $disp->tracking_number }}</td>
                                        <td>{{ $disp->dispatch_date }}</td>
                                    </tr>
                                @endif
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-3">No dispatch records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab 4: Documents -->
            <div class="tab-pane fade" id="documents">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Registration #</th>
                                <th>Laboratory Name</th>
                                <th>Archived Evaluation Report</th>
                                <th>Archived Certificate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($program->registrations as $reg)
                                <tr>
                                    <td class="fw-bold text-primary">{{ $reg->registration_number }}</td>
                                    <td class="fw-bold text-dark">{{ $reg->lab->laboratory_name ?? 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('admin.reports.individual', [$program->program_id, $reg->registration_id]) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="bx bx-file me-1"></i> Open Evaluation Report
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.reports.certificate', [$program->program_id, $reg->registration_id]) }}" class="btn btn-outline-success btn-sm">
                                            <i class="bx bx-award me-1"></i> Open Certificate
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">No documents available.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
