@extends('layouts.app')

@section('title', 'Parameter Z-Score Analysis')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">ISO 13528 Evaluation: {{ $parameter->parameter_name }}</h4>
        <p class="text-muted small mb-0">Program: {{ $program->program_code }} — {{ $program->program_name }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.reports.parameter', [$program->program_id, $parameter->parameter_id]) }}" class="btn btn-outline-primary btn-sm">
            <i class="bx bx-printer me-1"></i> Parameter Print Report
        </a>
        <a href="{{ route('admin.stats.export', [$program->program_id, $parameter->parameter_id]) }}" class="btn btn-success btn-sm shadow-sm">
            <i class="bx bx-download me-1"></i> Export CSV
        </a>
        <a href="{{ route('admin.stats.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i> Back to Engine
        </a>
    </div>
</div>

<!-- ISO 13528 Statistical KPI Metrics Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-2">
        <div class="card card-stat text-center py-3">
            <small class="text-muted fw-semibold">Submissions (n)</small>
            <h3 class="fw-bold text-dark mb-0 mt-1">{{ $stats['count'] }}</h3>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card card-stat text-center py-3">
            <small class="text-muted fw-semibold">Classical Mean (x̄)</small>
            <h3 class="fw-bold text-primary mb-0 mt-1">{{ $stats['mean'] }}</h3>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card card-stat text-center py-3">
            <small class="text-muted fw-semibold">Median (M)</small>
            <h3 class="fw-bold text-dark mb-0 mt-1">{{ $stats['median'] }}</h3>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card card-stat text-center py-3 border-start border-success border-4">
            <small class="text-muted fw-semibold">Robust Mean (x*)</small>
            <h3 class="fw-bold text-success mb-0 mt-1">{{ $stats['robust_mean'] }}</h3>
            <small class="text-muted micro-text">ISO 13528 Assigned</small>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card card-stat text-center py-3 border-start border-info border-4">
            <small class="text-muted fw-semibold">Robust SD (s*)</small>
            <h3 class="fw-bold text-info mb-0 mt-1">{{ $stats['robust_sd'] }}</h3>
            <small class="text-muted micro-text">ISO 13528 Target SD</small>
        </div>
    </div>
    <div class="col-md-2">
        <div class="card card-stat text-center py-3">
            <small class="text-muted fw-semibold">Pass Rate (|z|≤2)</small>
            <h3 class="fw-bold text-success mb-0 mt-1">{{ $summaryCounts['pass_rate'] }}%</h3>
        </div>
    </div>
</div>

<!-- ApexCharts Z-Score Distribution Chart -->
<div class="card mb-4">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0"><i class="bx bx-bar-chart-alt-2 me-1 text-primary"></i> Participant Z-Score Distribution Graph</h6>
        <div>
            <span class="badge bg-success me-1">Satisfactory (|z| ≤ 2.0): {{ $summaryCounts['satisfactory'] }}</span>
            <span class="badge bg-warning text-dark me-1">Warning (2.0 < |z| < 3.0): {{ $summaryCounts['warning'] }}</span>
            <span class="badge bg-danger">Action Required (|z| ≥ 3.0): {{ $summaryCounts['action'] }}</span>
        </div>
    </div>
    <div class="card-body">
        @if(count($chartZScores) > 0)
            <div id="zScoreChart" style="min-height: 320px;"></div>
        @else
            <div class="text-center text-muted py-5">No observations available to render Z-Score chart.</div>
        @endif
    </div>
</div>

<!-- Participant Z-Score Ranking Table -->
<div class="card">
    <div class="card-header bg-light">
        <h6 class="fw-bold mb-0"><i class="bx bx-list-check me-1 text-primary"></i> Participant Laboratory Z-Score Ranking & Evaluation</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Sample Code</th>
                        <th>Participant Laboratory</th>
                        <th>Test Method</th>
                        <th>Reported Result</th>
                        <th>Z-Score</th>
                        <th>Performance Evaluation</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($participantResults as $index => $res)
                        <tr>
                            <td class="fw-bold text-muted">#{{ $index + 1 }}</td>
                            <td class="fw-bold text-primary"><i class="bx bx-qr-scan me-1"></i> {{ $res['sample_code'] }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $res['lab_name'] }}</div>
                                <small class="text-muted">{{ $res['email'] }}</small>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $res['test_method'] }}</span></td>
                            <td>
                                <span class="fw-bold text-dark fs-6">{{ $res['result_value'] }}</span>
                                <small class="text-muted">{{ $res['unit'] }}</small>
                            </td>
                            <td>
                                <span class="fw-bold fs-5 {{ $res['status'] === 'satisfactory' ? 'text-success' : ($res['status'] === 'warning' ? 'text-warning' : 'text-danger') }}">
                                    {{ $res['z_score'] > 0 ? '+' : '' }}{{ number_format($res['z_score'], 2) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $res['badge_class'] }} fs-6 py-2 px-3">
                                    {{ $res['label'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5">No observation results found for this parameter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if(count($chartZScores) > 0)
<script>
    document.addEventListener("DOMContentLoaded", function () {
        var options = {
            series: [{
                name: 'Z-Score',
                data: @json($chartZScores)
            }],
            chart: {
                type: 'bar',
                height: 320,
                toolbar: { show: true }
            },
            plotOptions: {
                bar: {
                    distributed: true,
                    borderRadius: 4,
                    columnWidth: '45%',
                    dataLabels: { position: 'top' }
                }
            },
            colors: @json($chartColors),
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return val > 0 ? '+' + val : val;
                },
                offsetY: -20,
                style: {
                    fontSize: '12px',
                    fontWeight: 'bold',
                    colors: ["#0f172a"]
                }
            },
            xaxis: {
                categories: @json($chartLabs),
                labels: {
                    style: { fontSize: '11px' }
                }
            },
            yaxis: {
                title: { text: 'Z-Score' },
                min: -4,
                max: 4
            },
            annotations: {
                yaxis: [
                    {
                        y: 2.0,
                        borderColor: '#f59e0b',
                        label: {
                            borderColor: '#f59e0b',
                            style: { color: '#fff', background: '#f59e0b' },
                            text: '+2.0 Warning Limit'
                        }
                    },
                    {
                        y: -2.0,
                        borderColor: '#f59e0b',
                        label: {
                            borderColor: '#f59e0b',
                            style: { color: '#fff', background: '#f59e0b' },
                            text: '-2.0 Warning Limit'
                        }
                    },
                    {
                        y: 3.0,
                        borderColor: '#ef4444',
                        label: {
                            borderColor: '#ef4444',
                            style: { color: '#fff', background: '#ef4444' },
                            text: '+3.0 Action Signal'
                        }
                    },
                    {
                        y: -3.0,
                        borderColor: '#ef4444',
                        label: {
                            borderColor: '#ef4444',
                            style: { color: '#fff', background: '#ef4444' },
                            text: '-3.0 Action Signal'
                        }
                    }
                ]
            },
            legend: { show: false }
        };

        var chart = new ApexCharts(document.querySelector("#zScoreChart"), options);
        chart.render();
    });
</script>
@endif
@endpush
