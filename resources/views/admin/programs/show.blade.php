@extends('layouts.app')

@section('title', 'Program Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">{{ $program->program_code }}</h4>
        <p class="text-muted small mb-0">{{ $program->program_name }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.samples.program', $program->program_id) }}" class="btn btn-warning btn-sm shadow-sm text-dark fw-semibold">
            <i class="bx bx-barcode me-1"></i> Assign Samples
        </a>
        <a href="{{ route('admin.dispatches.program', $program->program_id) }}" class="btn btn-primary btn-sm shadow-sm fw-semibold">
            <i class="bx bx-package me-1"></i> Dispatch Samples
        </a>
        <a href="{{ route('admin.plans.show', $program->program_id) }}" class="btn btn-success btn-sm shadow-sm fw-semibold">
            <i class="bx bx-task me-1"></i> Official PT Plan
        </a>
        <a href="{{ route('admin.programs.edit', $program->program_id) }}" class="btn btn-outline-primary btn-sm">
            <i class="bx bx-edit me-1"></i> Edit
        </a>
        <a href="{{ route('admin.programs.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i> Back
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Overview Card -->
        <div class="card mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">Program Summary & Specifications</h6>
                <div>
                    @if($program->program_status === 'open')
                        <span class="badge bg-success">Open</span>
                    @elseif($program->program_status === 'closed')
                        <span class="badge bg-danger">Closed</span>
                    @else
                        <span class="badge bg-secondary">{{ ucfirst($program->program_status) }}</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <small class="text-muted d-block">Discipline</small>
                        <span class="fw-semibold text-dark">{{ $program->discipline ?? 'N/A' }}</span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Scheme Code</small>
                        <span class="fw-semibold text-dark">{{ $program->scheme_code ?? 'N/A' }}</span>
                    </div>
                    <div class="col-md-4">
                        <small class="text-muted d-block">Program Fee</small>
                        <span class="fw-bold text-success fs-5">₹{{ number_format($program->program_fee, 2) }}</span>
                    </div>
                    <div class="col-12 border-top pt-3">
                        <small class="text-muted d-block">Description</small>
                        <p class="mb-0 text-dark small">{{ $program->description ?? 'No description provided.' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Test Parameters Card -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="fw-bold mb-0">Test Parameters ({{ $program->parameters->count() }})</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Parameter Name</th>
                                <th>Test Method</th>
                                <th>Unit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($program->parameters as $index => $param)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="fw-semibold text-dark">{{ $param->parameter_name }}</td>
                                    <td><code>{{ $param->test_method ?? 'Standard' }}</code></td>
                                    <td><span class="badge bg-light text-dark border">{{ $param->unit ?? '-' }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-3">No test parameters configured.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Registered Laboratories -->
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="fw-bold mb-0">Registered Laboratories ({{ $program->registrations->count() }})</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Reg No</th>
                                <th>Laboratory Name</th>
                                <th>Contact Email</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($program->registrations as $reg)
                                <tr>
                                    <td class="fw-semibold text-primary">{{ $reg->registration_number }}</td>
                                    <td class="fw-semibold">{{ $reg->lab->laboratory_name ?? 'Unknown' }}</td>
                                    <td>{{ $reg->lab->email ?? 'N/A' }}</td>
                                    <td>
                                        @if($reg->status === 'confirmed')
                                            <span class="badge badge-soft-success">Confirmed</span>
                                        @else
                                            <span class="badge badge-soft-warning">{{ ucfirst($reg->status) }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">No laboratories registered yet for this program.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline Sidebar Column -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="fw-bold mb-0"><i class="bx bx-time-five me-1 text-warning"></i> Program Timeline</h6>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted">Reg. Start Date:</span>
                        <span class="fw-semibold">{{ $program->registration_start_date ? \Carbon\Carbon::parse($program->registration_start_date)->format('d M Y') : 'TBD' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted">Reg. End Date:</span>
                        <span class="fw-semibold">{{ $program->registration_end_date ? \Carbon\Carbon::parse($program->registration_end_date)->format('d M Y') : 'TBD' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted">Sample Dispatch Date:</span>
                        <span class="fw-semibold">{{ $program->dispatch_date ? \Carbon\Carbon::parse($program->dispatch_date)->format('d M Y') : 'TBD' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted">Submission Deadline:</span>
                        <span class="fw-semibold text-danger">{{ $program->submission_deadline ? \Carbon\Carbon::parse($program->submission_deadline)->format('d M Y') : 'TBD' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted">Report Release Date:</span>
                        <span class="fw-semibold text-success">{{ $program->report_date ? \Carbon\Carbon::parse($program->report_date)->format('d M Y') : 'TBD' }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-light">
                <h6 class="fw-bold mb-0">Metadata</h6>
            </div>
            <div class="card-body small">
                <p class="mb-1 text-muted">Created By: <strong class="text-dark">{{ $program->creator->full_name ?? 'System Admin' }}</strong></p>
                <p class="mb-0 text-muted">Created At: <strong class="text-dark">{{ \Carbon\Carbon::parse($program->created_at)->format('d M Y, h:i A') }}</strong></p>
            </div>
        </div>
    </div>
</div>
@endsection
