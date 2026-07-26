@extends('layouts.app')

@section('title', 'Individual PT Evaluation Report')

@push('styles')
<style>
    @media print {
        body { background: #fff !important; font-size: 12px !important; }
        #sidebar, 
        .top-navbar, 
        .btn-print-bar, 
        footer { 
            display: none !important; 
        }
        #wrapper,
        #content,
        .container-fluid {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            box-shadow: none !important;
        }
        .report-card { 
            border: none !important; 
            box-shadow: none !important; 
            max-width: 100% !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        @page {
            size: A4 portrait;
            margin: 10mm;
        }
    }
    .report-card { max-width: 900px; margin: 0 auto; background: #fff; border: 1px solid #cbd5e1; border-radius: 8px; }
    .report-header { border-bottom: 3px double #0f172a; padding: 25px 30px; }
    .sig-line { border-top: 1px solid #333; width: 180px; display: inline-block; margin-top: 40px; }
</style>
@endpush

@section('content')
<!-- Print Action Bar -->
<div class="d-flex justify-content-between align-items-center mb-4 btn-print-bar">
    <div>
        <h4 class="fw-bold mb-1">Individual PT Evaluation Report</h4>
        <p class="text-muted small mb-0">ISO/IEC 17043 Compliant Performance Report</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-primary btn-sm shadow-sm">
            <i class="bx bx-printer me-1"></i> Print / Save PDF
        </button>
        <a href="{{ route('admin.reports.certificate', [$program->program_id, $registration->registration_id]) }}" class="btn btn-success btn-sm">
            <i class="bx bx-award me-1"></i> View Certificate
        </a>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i> Back to Reports
        </a>
    </div>
</div>

<!-- Report Document Card -->
<div class="report-card p-4 shadow-sm mb-5">
    <!-- Header -->
    <div class="report-header d-flex justify-content-between align-items-center">
        <div>
            <h3 class="fw-bold text-dark mb-0">GLOBAL PROFICIENCY TESTING PROVIDER</h3>
            <p class="text-muted small mb-0">ISO/IEC 17043:2023 Accredited Provider | Accreditation No: PT-8912</p>
            <p class="text-muted micro-text mb-0">Industrial Zone, Phase-I, Technology Park | Contact: pt@ptsoftware.com</p>
        </div>
        <div class="text-end">
            <span class="badge bg-primary fs-6 mb-1">FINAL REPORT</span>
            <small class="text-muted d-block">Report No: RPT-{{ str_pad($registration->registration_id, 5, '0', STR_PAD_LEFT) }}</small>
            <small class="text-muted d-block">Date: {{ date('d M Y') }}</small>
        </div>
    </div>

    <div class="py-3">
        <h5 class="fw-bold text-center text-dark text-uppercase tracking-wide mb-4">Confidential Performance Evaluation Report</h5>

        <!-- Participant & Scheme Metadata Box -->
        <div class="row g-3 p-3 bg-light rounded border mb-4">
            <div class="col-md-6 border-end">
                <small class="text-muted d-block fw-semibold text-uppercase">Participating Laboratory</small>
                <h6 class="fw-bold text-dark mb-1">{{ $lab->laboratory_name }}</h6>
                <small class="text-muted d-block">NABL Cert No: {{ $lab->nabl_certificate_number ?? 'N/A' }}</small>
                <small class="text-muted d-block">Address: {{ $lab->address }}, {{ $lab->city }}, {{ $lab->state }}</small>
                <small class="text-muted d-block">Contact: {{ $lab->contact_person }} ({{ $lab->email }})</small>
            </div>
            <div class="col-md-6 ps-md-3">
                <small class="text-muted d-block fw-semibold text-uppercase">PT Scheme & Sample Identifier</small>
                <h6 class="fw-bold text-primary mb-1">{{ $program->program_code }} — {{ $program->program_name }}</h6>
                <small class="text-dark d-block fw-semibold">Discipline: {{ $program->discipline->discipline_name ?? $program->discipline }}</small>
                <small class="text-dark d-block">Registration #: <strong>{{ $registration->registration_number }}</strong></small>
                <small class="text-dark d-block">Assigned Sample ID: <strong class="text-primary">{{ $sample->sample_code ?? 'N/A' }}</strong></small>
            </div>
        </div>

        <!-- Evaluation Results Table -->
        <h6 class="fw-bold text-dark mb-2"><i class="bx bx-table me-1 text-primary"></i> Parameter Performance Summary (ISO 13528 Algorithm A)</h6>
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-dark text-center">
                    <tr>
                        <th>Parameter Name</th>
                        <th>Test Method</th>
                        <th>Reported Result</th>
                        <th>Assigned Value (x*)</th>
                        <th>Target SD (s*)</th>
                        <th>Z-Score</th>
                        <th>Performance Evaluation</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($parameterEvaluations as $eval)
                        <tr>
                            <td class="fw-bold text-dark">{{ $eval['parameter_name'] }}</td>
                            <td class="text-center"><span class="badge bg-light text-dark border">{{ $eval['test_method'] }}</span></td>
                            <td class="text-center fw-bold">{{ $eval['reported_value'] }} {{ $eval['unit'] }}</td>
                            <td class="text-center fw-bold text-primary">{{ $eval['assigned_value'] }}</td>
                            <td class="text-center fw-bold text-info">{{ $eval['target_sd'] }}</td>
                            <td class="text-center fw-bold fs-6 {{ $eval['status'] === 'satisfactory' ? 'text-success' : ($eval['status'] === 'warning' ? 'text-warning' : 'text-danger') }}">
                                {{ $eval['z_score'] > 0 ? '+' : '' }}{{ number_format($eval['z_score'], 2) }}
                            </td>
                            <td class="text-center">
                                <span class="badge {{ $eval['badge_class'] }} px-3 py-2">
                                    {{ $eval['label'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No observation results evaluated for this laboratory.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Z-Score Graph -->
        @if(count($chartZScores) > 0)
            <div class="p-3 bg-light rounded border mb-4">
                <h6 class="fw-bold text-dark mb-3"><i class="bx bx-bar-chart-alt-2 me-1 text-primary"></i> Parameter Z-Score Profile</h6>
                <div id="indZScoreChart" style="min-height: 220px;"></div>
            </div>
        @endif

        <!-- Evaluation Criteria Footer & Remarks -->
        <div class="p-3 bg-light rounded border mb-4">
            <small class="fw-bold text-dark d-block mb-1">ISO 13528 / ISO 17043 Performance Assessment Criteria:</small>
            <ul class="mb-0 text-muted small ps-3">
                <li><strong>Satisfactory (|z| ≤ 2.0)</strong>: Results are within acceptable limits. No corrective action required.</li>
                <li><strong>Questionable (2.0 < |z| < 3.0)</strong>: Warning signal. The laboratory should investigate testing procedures.</li>
                <li><strong>Unsatisfactory (|z| ≥ 3.0)</strong>: Action signal. The laboratory must perform root-cause analysis and corrective measures.</li>
            </ul>
        </div>

        <!-- Footer Signatures & QR Verification -->
        <div class="row align-items-end mt-5 pt-3">
            <div class="col-4 text-center">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=110x110&data={{ urlencode($qrPayload) }}" alt="QR Code" class="border p-1 bg-white mb-1" style="width: 100px; height: 100px;">
                <small class="text-muted d-block micro-text">Scan to verify report authenticity</small>
            </div>
            <div class="col-4 text-center">
                <div class="sig-line"></div>
                <small class="fw-bold text-dark d-block mt-1">Dr. System Administrator</small>
                <small class="text-muted micro-text">Quality Assurance Manager</small>
            </div>
            <div class="col-4 text-center">
                <div class="sig-line"></div>
                <small class="fw-bold text-dark d-block mt-1">PT Scheme Coordinator</small>
                <small class="text-muted micro-text">ISO/IEC 17043 Technical Signatory</small>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if(count($chartZScores) > 0)
<script>
    document.addEventListener("DOMContentLoaded", function () {
        var options = {
            series: [{ name: 'Z-Score', data: @json($chartZScores) }],
            chart: { type: 'bar', height: 220, toolbar: { show: false } },
            plotOptions: {
                bar: {
                    distributed: true,
                    borderRadius: 4,
                    columnWidth: '35%',
                    dataLabels: { position: 'top' }
                }
            },
            colors: @json($chartColors),
            dataLabels: {
                enabled: true,
                formatter: function(val) { return val > 0 ? '+' + val : val; },
                offsetY: -22,
                style: {
                    fontSize: '12px',
                    fontWeight: 'bold',
                    colors: ["#0f172a"]
                }
            },
            xaxis: { categories: @json($chartParams) },
            yaxis: { min: -4, max: 4 }
        };
        var chart = new ApexCharts(document.querySelector("#indZScoreChart"), options);
        chart.render();
    });
</script>
@endif
@endpush
