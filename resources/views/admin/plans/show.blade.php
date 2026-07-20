@extends('layouts.app')

@section('title', 'PT Plan Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Official PT Plan: {{ $program->plan->program_number ?? $program->program_code }}</h4>
        <p class="text-muted small mb-0">{{ $program->program_name }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.plans.print', $program->program_id) }}" target="_blank" class="btn btn-success btn-sm shadow-sm">
            <i class="bx bx-printer me-1"></i> Print / Save as PDF
        </a>
        <a href="{{ route('admin.plans.create', $program->program_id) }}" class="btn btn-outline-primary btn-sm">
            <i class="bx bx-edit me-1"></i> Edit Plan
        </a>
        <a href="{{ route('admin.programs.show', $program->program_id) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i> Back to Program
        </a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Main Document Body -->
        <div class="card mb-4 border shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <i class="bx bx-flask text-primary fs-3"></i>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">PROFICIENCY TESTING SCHEME PLAN</h6>
                        <small class="text-muted">ISO/IEC 17043:2023 Compliant Document</small>
                    </div>
                </div>
                <span class="badge bg-primary fs-6">{{ $program->plan->program_number }}</span>
            </div>
            <div class="card-body">
                <!-- Grid Information -->
                <div class="row g-3 mb-4 bg-light p-3 rounded border">
                    <div class="col-md-6">
                        <small class="text-muted d-block fw-semibold">PT Program Name</small>
                        <span class="fw-bold text-dark">{{ $program->program_name }}</span>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block fw-semibold">Program Code / Scheme</small>
                        <span class="fw-bold text-dark">{{ $program->program_code }} ({{ $program->scheme_code ?? 'ISO 17043' }})</span>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block fw-semibold">PT Material / Matrix</small>
                        <span class="fw-bold text-primary">{{ $program->plan->material }}</span>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block fw-semibold">Sample Quantity per Participant</small>
                        <span class="fw-bold text-dark">{{ $program->plan->sample_quantity ?? 'N/A' }}</span>
                    </div>
                </div>

                <!-- Parameters Table -->
                <h6 class="fw-bold mb-2 text-dark"><i class="bx bx-list-check me-1 text-success"></i> 1. Scope & Parameters</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 10%;">#</th>
                                <th style="width: 40%;">Parameter Name</th>
                                <th style="width: 35%;">Test Method</th>
                                <th style="width: 15%;">Unit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($program->parameters as $index => $param)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="fw-semibold">{{ $param->parameter_name }}</td>
                                    <td><code>{{ $param->test_method ?? 'Standard Method' }}</code></td>
                                    <td>{{ $param->unit ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted">No test parameters configured.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Sample Preparation Instructions -->
                <h6 class="fw-bold mb-2 text-dark"><i class="bx bx-info-circle me-1 text-info"></i> 2. Sample Preparation & Instructions</h6>
                <div class="p-3 bg-light rounded border mb-4">
                    <pre class="mb-0 text-dark" style="font-family: inherit; white-space: pre-wrap;">{{ $program->plan->sample_preparation_instructions ?? 'No instructions provided.' }}</pre>
                </div>

                <!-- Coordinator & Signatures -->
                <div class="row g-3 pt-3 border-top">
                    <div class="col-md-6">
                        <small class="text-muted d-block fw-semibold">Assigned Program Coordinator</small>
                        <div class="fw-bold text-dark fs-6">{{ $program->plan->coordinator->full_name ?? 'System Administrator' }}</div>
                        <small class="text-muted">{{ $program->plan->coordinator->email ?? '' }}</small>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <small class="text-muted d-block fw-semibold">Plan Created Date</small>
                        <div class="fw-bold text-dark">{{ \Carbon\Carbon::parse($program->plan->created_at)->format('d M Y') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline Sidebar -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="fw-bold mb-0"><i class="bx bx-calendar me-1 text-warning"></i> Operational Timeline</h6>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted">Plan Timeline:</span>
                        <span class="fw-bold text-primary">{{ $program->plan->timeline ?? 'N/A' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted">Registration Opens:</span>
                        <span class="fw-semibold">{{ $program->registration_start_date ? \Carbon\Carbon::parse($program->registration_start_date)->format('d M Y') : 'TBD' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted">Registration Closes:</span>
                        <span class="fw-semibold">{{ $program->registration_end_date ? \Carbon\Carbon::parse($program->registration_end_date)->format('d M Y') : 'TBD' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted">Dispatch Date:</span>
                        <span class="fw-semibold">{{ $program->dispatch_date ? \Carbon\Carbon::parse($program->dispatch_date)->format('d M Y') : 'TBD' }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between px-0 py-2">
                        <span class="text-muted">Submission Deadline:</span>
                        <span class="fw-semibold text-danger">{{ $program->submission_deadline ? \Carbon\Carbon::parse($program->submission_deadline)->format('d M Y') : 'TBD' }}</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-body d-grid gap-2">
                <a href="{{ route('admin.plans.print', $program->program_id) }}" target="_blank" class="btn btn-success py-2 fw-semibold">
                    <i class="bx bx-printer me-1"></i> Print Official Document (PDF)
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
