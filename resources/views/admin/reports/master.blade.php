@extends('layouts.app')

@section('title', 'Master Consolidated Z-Score Matrix Report')

@push('styles')
<style>
    @media print {
        body { background: #fff !important; }
        .sidebar, .navbar, .btn-print-bar, footer { display: none !important; }
        .content-wrapper { margin: 0 !important; padding: 0 !important; }
    }
</style>
@endpush

@section('content')
<!-- Action Bar -->
<div class="d-flex justify-content-between align-items-center mb-4 btn-print-bar">
    <div>
        <h4 class="fw-bold mb-1">Master Consolidated Z-Score Matrix Report</h4>
        <p class="text-muted small mb-0">Program: {{ $program->program_code }} — {{ $program->program_name }}</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-primary btn-sm shadow-sm">
            <i class="bx bx-printer me-1"></i> Print Matrix / Save PDF
        </button>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i> Back to Directory
        </a>
    </div>
</div>

<!-- Master Matrix Document -->
<div class="card p-4 shadow-sm mb-5">
    <div class="border-bottom pb-3 mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold text-dark mb-0">MASTER PROFICIENCY CONSOLIDATED EVALUATION MATRIX</h4>
            <p class="text-muted small mb-0">ISO 17043 Comprehensive Laboratory Performance Comparison Report</p>
        </div>
        <div class="text-end">
            <span class="badge bg-primary fs-6">{{ $program->program_code }}</span>
            <small class="text-muted d-block mt-1">Discipline: {{ $program->discipline }}</small>
        </div>
    </div>

    <!-- Master Matrix Table -->
    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <thead class="table-dark text-center">
                <tr>
                    <th rowspan="2" class="align-middle">Registration #</th>
                    <th rowspan="2" class="align-middle">Sample Code</th>
                    <th rowspan="2" class="align-middle">Participating Laboratory</th>
                    <th colspan="{{ count($parameters) }}">Test Parameters & Evaluated Z-Scores</th>
                </tr>
                <tr>
                    @foreach($parameters as $param)
                        <th>
                            {{ $param->parameter_name }}
                            <small class="d-block text-white-50">(x* = {{ $paramStats[$param->parameter_id]['robust_mean'] ?? 'N/A' }})</small>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($matrix as $row)
                    <tr>
                        <td class="fw-bold text-primary">{{ $row['reg']->registration_number }}</td>
                        <td class="fw-bold text-dark text-center"><i class="bx bx-qr-scan me-1"></i> {{ $row['sample']->sample_code ?? 'N/A' }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $row['lab']->laboratory_name ?? 'N/A' }}</div>
                            <small class="text-muted">{{ $row['lab']->email ?? '' }}</small>
                        </td>
                        @foreach($parameters as $param)
                            @php
                                $eval = $row['evaluations'][$param->parameter_id] ?? null;
                            @endphp
                            <td class="text-center">
                                @if($eval)
                                    <div class="fw-bold fs-6 {{ $eval['status'] === 'satisfactory' ? 'text-success' : ($eval['status'] === 'warning' ? 'text-warning' : 'text-danger') }}">
                                        Z = {{ $eval['z_score'] > 0 ? '+' : '' }}{{ number_format($eval['z_score'], 2) }}
                                    </div>
                                    <span class="badge {{ $eval['badge_class'] }} micro-text mt-1">
                                        {{ $eval['label'] }}
                                    </span>
                                @else
                                    <span class="text-muted small">Not Reported</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="{{ 3 + count($parameters) }}" class="text-center text-muted py-5">No participant registrations found for master report matrix.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
