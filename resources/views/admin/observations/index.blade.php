@extends('layouts.app')

@section('title', 'Observation Management Registry')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Observation Management Registry</h4>
        <p class="text-muted small mb-0">Central repository of all submitted participant laboratory test results</p>
    </div>
    <a href="{{ route('admin.observations.export', request()->all()) }}" class="btn btn-success btn-sm shadow-sm">
        <i class="bx bx-download me-1"></i> Export Observations (CSV)
    </a>
</div>

<!-- Search & Filters Card -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form action="{{ route('admin.observations.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bx bx-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search Sample, Lab, Result Value, Method..." value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-md-4">
                <select name="program_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Filter PT Program --</option>
                    @foreach($programs as $prog)
                        <option value="{{ $prog->program_id }}" {{ request('program_id') == $prog->program_id ? 'selected' : '' }}>{{ $prog->program_code }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4 d-flex gap-1">
                <button type="submit" class="btn btn-secondary btn-sm w-100"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                @if(request()->hasAny(['search', 'program_id']))
                    <a href="{{ route('admin.observations.index') }}" class="btn btn-light btn-sm"><i class="bx bx-reset"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Observations Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Observation ID</th>
                        <th>Sample Code</th>
                        <th>Participant Laboratory</th>
                        <th>Parameter</th>
                        <th>Test Method</th>
                        <th>Result Value</th>
                        <th>Status</th>
                        <th>Submitted At</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($observations as $obs)
                        <tr>
                            <td class="fw-bold text-primary">OBS-{{ str_pad($obs->observation_id, 4, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <span class="fw-bold text-dark"><i class="bx bx-qr-scan me-1"></i> {{ $obs->sample->sample_code ?? 'N/A' }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $obs->lab->laboratory_name ?? 'N/A' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $obs->parameter->parameter_name ?? 'N/A' }}</div>
                                <small class="text-muted">{{ $obs->parameter->program->program_code ?? '' }}</small>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $obs->test_method ?? 'N/A' }}</span></td>
                            <td>
                                <span class="fw-bold text-success fs-6">{{ $obs->result_value }}</span>
                                <small class="text-muted ms-1">{{ $obs->unit }}</small>
                            </td>
                            <td>
                                @if($obs->is_locked)
                                    <span class="badge badge-soft-danger"><i class="bx bx-lock-alt me-1"></i> Locked</span>
                                @else
                                    <span class="badge badge-soft-success"><i class="bx bx-check me-1"></i> Submitted</span>
                                @endif
                            </td>
                            <td class="small text-muted">{{ \Carbon\Carbon::parse($obs->submitted_at)->format('d M Y, h:i A') }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.observations.show', $obs->observation_id) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bx bx-show me-1"></i> View Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="bx bx-test-tube display-4 text-muted d-block mb-2"></i>
                                No observation results submitted yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($observations->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $observations->links() }}
        </div>
    @endif
</div>
@endsection
