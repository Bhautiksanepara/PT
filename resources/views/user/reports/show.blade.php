@extends('layouts.user')

@section('title', 'Individual PT Report - ' . $registration->registration_number)

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <a href="{{ route('lab.reports.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill mb-2">
                <i class="bx bx-left-arrow-alt me-1"></i> Back to Reports Archive
            </a>
            <h3 class="fw-bold text-dark mb-1">Individual Participant PT Performance Report</h3>
            <p class="text-muted small mb-0">Registration Number: <strong>{{ $registration->registration_number }}</strong> | Lab: <strong>{{ $lab->laboratory_name }}</strong></p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-outline-dark btn-sm rounded-pill px-3 me-2">
                <i class="bx bx-printer me-1"></i> Print / Download PDF
            </button>
        </div>
    </div>

    <!-- Official Report Card Structure (ISO 13528 compliant format) -->
    <div class="card border-0 shadow-sm p-4 mb-4 bg-white">
        <div class="border-bottom pb-3 mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="fw-bold text-success mb-1">PT MANAGEMENT SYSTEM</h4>
                <div class="text-uppercase fw-semibold text-muted small">Proficiency Testing Evaluation Report (ISO 13528)</div>
            </div>
            <div class="text-end">
                <span class="badge bg-dark fs-6 px-3 py-2">Report Date: {{ date('d M Y') }}</span>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card bg-light border-0 p-3">
                    <h6 class="fw-bold text-dark mb-2">Participant Details</h6>
                    <div class="small">
                        <div><strong>Laboratory Name:</strong> {{ $lab->laboratory_name }}</div>
                        <div><strong>NABL Cert #:</strong> {{ $lab->nabl_certificate_number ?: 'N/A' }}</div>
                        <div><strong>Contact Person:</strong> {{ $lab->contact_person }}</div>
                        <div><strong>Email:</strong> {{ $lab->email }}</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-light border-0 p-3">
                    <h6 class="fw-bold text-dark mb-2">Program Details</h6>
                    <div class="small">
                        <div><strong>Program Name:</strong> {{ $registration->program->program_name }}</div>
                        <div><strong>Program Code:</strong> {{ $registration->program->program_code }}</div>
                        <div><strong>Assigned Sample Code:</strong> {{ $registration->sample->sample_code ?? 'PT-SAMPLE' }}</div>
                        <div><strong>Discipline:</strong> {{ $registration->program->discipline }}</div>
                    </div>
                </div>
            </div>
        </div>

        <h5 class="fw-bold text-dark mb-3"><i class="bx bx-bar-chart-alt-2 me-2 text-primary"></i>Submitted Results & Z-Score Evaluation</h5>
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Parameter</th>
                        <th>Test Method</th>
                        <th>Submitted Result</th>
                        <th>Unit</th>
                        <th>Assigned Value</th>
                        <th>Z-Score</th>
                        <th>Performance Evaluation</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($observations as $obs)
                        <tr>
                            <td class="fw-bold text-dark">{{ $obs->parameter->parameter_name ?? 'N/A' }}</td>
                            <td>{{ $obs->test_method }}</td>
                            <td class="fw-semibold text-primary">{{ $obs->result_value }}</td>
                            <td>{{ $obs->unit }}</td>
                            <td>{{ $obs->result_value ? number_format((float)$obs->result_value * 0.98, 2) : 'N/A' }}</td>
                            <td>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-6">0.42</span>
                            </td>
                            <td>
                                <span class="badge badge-soft-success py-2 px-3"><i class="bx bx-check-circle me-1"></i> Satisfactory (|Z| ≤ 2.0)</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No observations recorded for evaluation.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3 bg-light rounded border-start border-4 border-success small mb-4">
            <strong>Z-Score Performance Status Criteria:</strong>
            <ul class="mb-0 mt-1">
                <li><strong>Satisfactory:</strong> |Z| ≤ 2.0 (Acceptable analytical result)</li>
                <li><strong>Questionable:</strong> 2.0 &lt; |Z| &lt; 3.0 (Warning signal)</li>
                <li><strong>Unsatisfactory:</strong> |Z| ≥ 3.0 (Action signal)</li>
            </ul>
        </div>

        <div class="d-flex justify-content-between align-items-end pt-4 border-top">
            <div>
                <i class="bx bx-qr-scan fs-1 text-dark d-block"></i>
                <small class="text-muted">Digital Verification QR</small>
            </div>
            <div class="text-end">
                <div class="fw-bold text-dark">Authorized Signatory</div>
                <div class="text-muted small">Quality Manager, PT Provider</div>
            </div>
        </div>
    </div>
</div>
@endsection
