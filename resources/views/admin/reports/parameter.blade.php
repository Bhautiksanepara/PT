@extends('layouts.app')

@section('title', 'Parameter Z-Score Report')

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
<!-- Print Action Bar -->
<div class="d-flex justify-content-between align-items-center mb-4 btn-print-bar">
    <div>
        <h4 class="fw-bold mb-1">Parameter-wise Z-Score Report</h4>
        <p class="text-muted small mb-0">Parameter: {{ $parameter->parameter_name }} ({{ $program->program_code }})</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-primary btn-sm shadow-sm">
            <i class="bx bx-printer me-1"></i> Print Report / Save PDF
        </button>
        <a href="{{ route('admin.stats.parameter', [$program->program_id, $parameter->parameter_id]) }}" class="btn btn-outline-primary btn-sm">
            <i class="bx bx-line-chart me-1"></i> Statistical Engine View
        </a>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i> Back to Reports
        </a>
    </div>
</div>

<div class="card p-4 shadow-sm mb-5">
    <div class="border-bottom pb-3 mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="fw-bold text-dark mb-0">PARAMETER PROFICIENCY EVALUATION REPORT</h4>
            <p class="text-muted small mb-0">ISO 13528 Algorithm A Statistical Assessment</p>
        </div>
        <div class="text-end">
            <span class="badge bg-primary fs-6">{{ $program->program_code }}</span>
            <small class="text-muted d-block mt-1">Date: {{ date('d M Y') }}</small>
        </div>
    </div>

    <!-- KPI Summary Block -->
    <div class="row g-3 p-3 bg-light rounded border mb-4">
        <div class="col-md-3">
            <small class="text-muted d-block fw-semibold">Parameter Name</small>
            <span class="fw-bold text-dark fs-6">{{ $parameter->parameter_name }}</span>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block fw-semibold">Test Method / Unit</small>
            <span class="fw-semibold text-dark">{{ $parameter->test_method }} ({{ $parameter->unit }})</span>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block fw-semibold">ISO 13528 Robust Mean (x*)</small>
            <span class="fw-bold text-success fs-5">{{ $stats['robust_mean'] }}</span>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block fw-semibold">ISO 13528 Target SD (s*)</small>
            <span class="fw-bold text-info fs-5">{{ $stats['robust_sd'] }}</span>
        </div>
    </div>

    <!-- Parameter Participants Table -->
    <h6 class="fw-bold text-dark mb-2">Participating Laboratories Z-Score Evaluation</h6>
    <div class="table-responsive">
        <table class="table table-bordered align-middle mb-0">
            <thead class="table-dark text-center">
                <tr>
                    <th>#</th>
                    <th>Sample Code</th>
                    <th>Participating Laboratory</th>
                    <th>Test Method</th>
                    <th>Reported Result</th>
                    <th>Z-Score</th>
                    <th>Performance Evaluation</th>
                </tr>
            </thead>
            <tbody>
                @forelse($participantEvaluations as $index => $item)
                    <tr>
                        <td class="text-center fw-bold">{{ $index + 1 }}</td>
                        <td class="fw-bold text-primary">{{ $item['obs']->sample->sample_code ?? 'N/A' }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $item['obs']->lab->laboratory_name ?? 'N/A' }}</div>
                            <small class="text-muted">{{ $item['obs']->lab->email ?? '' }}</small>
                        </td>
                        <td class="text-center"><span class="badge bg-light text-dark border">{{ $item['obs']->test_method }}</span></td>
                        <td class="text-center fw-bold fs-6">{{ $item['obs']->result_value }} {{ $item['obs']->unit }}</td>
                        <td class="text-center fw-bold fs-5 {{ $item['status'] === 'satisfactory' ? 'text-success' : ($item['status'] === 'warning' ? 'text-warning' : 'text-danger') }}">
                            {{ $item['z_score'] > 0 ? '+' : '' }}{{ number_format($item['z_score'], 2) }}
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $item['badge_class'] }} px-3 py-2">
                                {{ $item['label'] }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No laboratory observations submitted for this parameter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
