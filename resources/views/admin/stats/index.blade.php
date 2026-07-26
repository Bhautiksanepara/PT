@extends('layouts.app')

@section('title', 'Statistical Analysis Engine')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Statistical Engine — ISO 13528 / ISO 17043</h4>
        <p class="text-muted small mb-0">Automated calculation of Robust Mean (x*), Robust SD (s*), and Participant Z-Scores</p>
    </div>
    <a href="{{ route('admin.stats.export-list', request()->all()) }}" class="btn btn-outline-success btn-sm shadow-sm fw-semibold">
        <i class="bx bx-download me-1"></i> Export (CSV/Excel)
    </a>
</div>

<!-- Search & Filters Card -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form action="{{ route('admin.stats.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-9">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bx bx-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search PT Program Code or Name..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3 d-flex gap-1">
                <button type="submit" class="btn btn-secondary btn-sm w-100"><i class="bx bx-filter-alt me-1"></i> Search</button>
                @if(request()->has('search'))
                    <a href="{{ route('admin.stats.index') }}" class="btn btn-light btn-sm"><i class="bx bx-reset"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Programs Accordion / Table -->
<div class="row g-4">
    @forelse($programs as $program)
        <div class="col-12">
            <div class="card shadow-sm border-start border-primary border-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-primary me-2">{{ $program->program_code }}</span>
                        <span class="fw-bold text-dark fs-6">{{ $program->program_name }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-light text-dark border">{{ $program->parameters->count() }} Parameters</span>
                        @if($program->program_status === 'completed')
                            <span class="badge bg-success py-2 px-3"><i class="bx bx-lock-alt me-1"></i> Frozen & Locked into DB</span>
                        @else
                            <form action="{{ route('admin.stats.freeze', $program->program_id) }}" method="POST" onsubmit="return confirm('Freezing this scheme will calculate ISO 13528 Robust Mean & SD, lock all observations, populate statistical_results & reports tables, and complete the scheme. Proceed?');">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm text-dark fw-bold shadow-sm">
                                    <i class="bx bx-lock-alt me-1"></i> Freeze & Lock Scheme Evaluation
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Parameter Name</th>
                                    <th>Test Method</th>
                                    <th>Unit</th>
                                    <th>Submissions</th>
                                    <th>ISO 13528 Status</th>
                                    <th class="text-end">Statistical Analysis</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($program->parameters as $param)
                                    @php
                                        $obsCount = $param->observations->count();
                                    @endphp
                                    <tr>
                                        <td class="fw-bold text-dark"><i class="bx bx-test-tube me-1 text-primary"></i> {{ $param->parameter_name }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $param->test_method }}</span></td>
                                        <td>{{ $param->unit }}</td>
                                        <td><span class="badge bg-info text-dark rounded-pill">{{ $obsCount }} Results</span></td>
                                        <td>
                                            @if($obsCount >= 3)
                                                <span class="badge badge-soft-success"><i class="bx bx-check-circle me-1"></i> Ready for Analysis</span>
                                            @elseif($obsCount > 0)
                                                <span class="badge badge-soft-warning"><i class="bx bx-time me-1"></i> In Sufficient Submissions</span>
                                            @else
                                                <span class="badge badge-soft-secondary">No Results Yet</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.stats.parameter', [$program->program_id, $param->parameter_id]) }}" class="btn btn-outline-primary btn-sm">
                                                <i class="bx bx-line-chart me-1"></i> Evaluate ISO 13528 Z-Scores
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-3">No parameters defined for this program.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center text-muted py-5">
            <i class="bx bx-line-chart display-4 text-muted d-block mb-2"></i>
            No PT Programs found for statistical analysis.
        </div>
    @endforelse
</div>

@if($programs->hasPages())
    <div class="mt-4">
        {{ $programs->links() }}
    </div>
@endif
@endsection
