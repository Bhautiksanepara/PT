@extends('layouts.user')

@section('title', 'Phase 6 - Test Observations')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bx bx-vial me-2 text-success"></i>Test Observation Submissions</h4>
        <p class="text-muted small mb-0">Enter and manage your laboratory test parameters, test methods, measurement uncertainty (MU), and raw data uploads</p>
    </div>
    <div>
        <a href="{{ route('user.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i> Back to Dashboard
        </a>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form action="{{ route('user.observations.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-9">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bx bx-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search Sample Code, Registration #, Program Code..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3 d-flex gap-1">
                <button type="submit" class="btn btn-success btn-sm w-100"><i class="bx bx-filter-alt me-1"></i> Search</button>
                @if(request()->has('search'))
                    <a href="{{ route('user.observations.index') }}" class="btn btn-light btn-sm"><i class="bx bx-reset"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Observations Master Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Registration #</th>
                        <th>Program Code</th>
                        <th>Sample Code</th>
                        <th>Submission Deadline</th>
                        <th>Submissions</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrations as $reg)
                        @php
                            $sampleCode = $reg->sample->sample_code ?? null;
                            $deadline = $reg->program->submission_deadline ? \Carbon\Carbon::parse($reg->program->submission_deadline) : null;
                            $isPastDeadline = ($deadline && now()->greaterThan($deadline->endOfDay())) || ($reg->program->program_status === 'completed');
                            $hasSubmitted = $reg->observations->count() > 0;
                        @endphp
                        <tr>
                            <td>
                                <span class="fw-bold text-primary">{{ $reg->registration_number }}</span>
                                <small class="text-muted d-block micro-text">{{ \Carbon\Carbon::parse($reg->registered_at)->format('d M Y') }}</small>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $reg->program->program_name }}</div>
                                <span class="badge bg-light text-dark border">{{ $reg->program->program_code }}</span>
                            </td>
                            <td>
                                @if($sampleCode)
                                    <span class="badge bg-primary font-monospace"><i class="bx bx-barcode me-1"></i> {{ $sampleCode }}</span>
                                @else
                                    <span class="badge bg-light text-muted border">Pending Assignment</span>
                                @endif
                            </td>
                            <td class="small">
                                @if($deadline)
                                    <span class="{{ $isPastDeadline ? 'text-danger fw-bold' : 'text-dark' }}">
                                        {{ $deadline->format('d M Y') }}
                                    </span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($hasSubmitted)
                                    <span class="badge badge-soft-success"><i class="bx bx-check-circle me-1"></i> {{ $reg->observations->count() }} Results Saved</span>
                                @else
                                    <span class="badge bg-light text-muted border">No Results Yet</span>
                                @endif
                            </td>
                            <td>
                                @if($isPastDeadline)
                                    <span class="badge bg-danger py-2 px-3"><i class="bx bx-lock-alt me-1"></i> Locked & Closed</span>
                                @elseif($hasSubmitted)
                                    <span class="badge badge-soft-info py-2 px-3"><i class="bx bx-edit me-1"></i> Results Submitted (Editable)</span>
                                @else
                                    <span class="badge badge-soft-warning py-2 px-3"><i class="bx bx-time me-1"></i> Awaiting Results</span>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($sampleCode)
                                    @if($isPastDeadline)
                                        <a href="{{ route('user.observations.form', $reg->registration_id) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="bx bx-show me-1"></i> View Locked Form
                                        </a>
                                    @elseif($hasSubmitted)
                                        <a href="{{ route('user.observations.form', $reg->registration_id) }}" class="btn btn-sm btn-primary">
                                            <i class="bx bx-edit me-1"></i> Edit Results
                                        </a>
                                    @else
                                        <a href="{{ route('user.observations.form', $reg->registration_id) }}" class="btn btn-sm btn-success fw-bold">
                                            <i class="bx bx-edit me-1"></i> Enter Results
                                        </a>
                                    @endif
                                @else
                                    <button class="btn btn-sm btn-outline-secondary opacity-50" disabled title="Sample assignment pending by Admin">
                                        <i class="bx bx-time me-1"></i> Pending Sample
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bx bx-vial display-4 text-muted d-block mb-2"></i>
                                No active PT registrations found for observation entry.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($registrations->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $registrations->links() }}
        </div>
    @endif
</div>
@endsection
