<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PT Plan — {{ $program->plan->program_number ?? $program->program_code }}</title>
    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            padding: 30px;
        }

        .print-container {
            max-width: 850px;
            margin: 0 auto;
            background: #ffffff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border: 1px solid #cbd5e1;
        }

        .document-header {
            border-bottom: 3px double #0f172a;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 0.9rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 6px;
            margin-top: 25px;
            margin-bottom: 15px;
        }

        .table-custom th {
            background-color: #f1f5f9;
            color: #334155;
            font-size: 0.8rem;
            text-transform: uppercase;
        }

        .signature-box {
            border-top: 1px dashed #94a3b8;
            padding-top: 10px;
            margin-top: 60px;
        }

        @media print {
            body {
                background: #ffffff;
                padding: 0;
            }
            .print-container {
                box-shadow: none;
                border: none;
                padding: 0;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <!-- Action Bar (Not Printed) -->
    <div class="max-w-850 mx-auto text-end mb-3 no-print" style="max-width: 850px;">
        <button onclick="window.print()" class="btn btn-primary px-4 shadow-sm fw-bold">
            <i class="bx bx-printer me-1"></i> Print / Save as PDF
        </button>
        <button onclick="window.close()" class="btn btn-outline-secondary ms-2">Close</button>
    </div>

    <!-- Official Printable ISO 17043 Document -->
    <div class="print-container">
        <!-- Letterhead Header -->
        <div class="document-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-extrabold mb-0 text-dark" style="letter-spacing: -0.5px;">PROFICIENCY TESTING PROVIDER</h3>
                <div class="small text-uppercase fw-bold text-primary">ISO/IEC 17043:2023 Accredited Provider</div>
                <small class="text-muted">Document Ref: <strong>{{ $program->plan->program_number }}</strong></small>
            </div>
            <div class="text-end">
                <div class="border px-3 py-2 rounded bg-light text-center">
                    <span class="d-block small text-muted font-monospace">CONFIDENTIAL</span>
                    <strong class="text-dark">OFFICIAL PT PLAN</strong>
                </div>
            </div>
        </div>

        <div class="text-center mb-4">
            <h4 class="fw-bold text-uppercase mb-1" style="color: #0f172a;">PROFICIENCY TESTING SCHEME PLAN</h4>
            <div class="lead fw-semibold text-secondary fs-6">{{ $program->program_name }}</div>
        </div>

        <!-- Section 1: General Scheme Details -->
        <div class="section-title"><i class="bx bx-info-circle me-1"></i> 1. General Program Information</div>
        <table class="table table-bordered table-sm align-middle mb-4" style="font-size: 0.9rem;">
            <tbody>
                <tr>
                    <th style="width: 30%;">Program Code:</th>
                    <td class="fw-bold">{{ $program->program_code }}</td>
                    <th style="width: 25%;">Discipline:</th>
                    <td>{{ $program->discipline ?? 'Chemical' }}</td>
                </tr>
                <tr>
                    <th>Scheme Code:</th>
                    <td>{{ $program->scheme_code ?? 'N/A' }}</td>
                    <th>Program Fee:</th>
                    <td>₹{{ number_format($program->program_fee, 2) }}</td>
                </tr>
                <tr>
                    <th>PT Matrix / Material:</th>
                    <td class="fw-bold text-primary">{{ $program->plan->material }}</td>
                    <th>Sample Quantity:</th>
                    <td>{{ $program->plan->sample_quantity ?? 'N/A' }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Section 2: Timelines -->
        <div class="section-title"><i class="bx bx-calendar me-1"></i> 2. Scheme Operational Timelines</div>
        <table class="table table-bordered table-sm align-middle mb-4" style="font-size: 0.875rem;">
            <thead class="table-light">
                <tr>
                    <th>Registration Start</th>
                    <th>Registration End</th>
                    <th>Dispatch Date</th>
                    <th>Submission Deadline</th>
                    <th>Report Release</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $program->registration_start_date ? \Carbon\Carbon::parse($program->registration_start_date)->format('d/m/Y') : 'TBD' }}</td>
                    <td>{{ $program->registration_end_date ? \Carbon\Carbon::parse($program->registration_end_date)->format('d/m/Y') : 'TBD' }}</td>
                    <td>{{ $program->dispatch_date ? \Carbon\Carbon::parse($program->dispatch_date)->format('d/m/Y') : 'TBD' }}</td>
                    <td class="fw-bold text-danger">{{ $program->submission_deadline ? \Carbon\Carbon::parse($program->submission_deadline)->format('d/m/Y') : 'TBD' }}</td>
                    <td class="fw-bold text-success">{{ $program->report_date ? \Carbon\Carbon::parse($program->report_date)->format('d/m/Y') : 'TBD' }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Section 3: Parameters -->
        <div class="section-title"><i class="bx bx-list-check me-1"></i> 3. Test Parameters & Analytical Scope</div>
        <table class="table table-bordered table-custom table-sm align-middle mb-4" style="font-size: 0.875rem;">
            <thead>
                <tr>
                    <th style="width: 8%;">S.No</th>
                    <th style="width: 45%;">Parameter Name</th>
                    <th style="width: 35%;">Test Method Standard</th>
                    <th style="width: 12%;">Unit</th>
                </tr>
            </thead>
            <tbody>
                @forelse($program->parameters as $index => $param)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="fw-semibold">{{ $param->parameter_name }}</td>
                        <td><code>{{ $param->test_method ?? 'Standard Method' }}</code></td>
                        <td>{{ $param->unit ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted">No test parameters configured.</td></tr>
                @endforelse
            </tbody>
        </table>

        <!-- Section 4: Sample Preparation & Handling Instructions -->
        <div class="section-title"><i class="bx bx-file me-1"></i> 4. Sample Preparation & Quality Controls</div>
        <div class="p-3 bg-light rounded border mb-4" style="font-size: 0.875rem;">
            <pre class="mb-0 text-dark" style="font-family: inherit; white-space: pre-wrap;">{{ $program->plan->sample_preparation_instructions ?? 'Standard storage at 2-8°C. Homogeneity and stability testing carried out in accordance with ISO 13528 guidelines.' }}</pre>
        </div>

        <!-- Section 5: Coordinator Sign-off -->
        <div class="row g-4 pt-4 mt-4">
            <div class="col-6">
                <div class="signature-box text-start">
                    <div class="fw-bold text-dark">{{ $program->plan->coordinator->full_name ?? 'System Administrator' }}</div>
                    <small class="text-muted d-block">Assigned PT Program Coordinator</small>
                    <small class="text-muted">ISO 17043 Technical Manager</small>
                </div>
            </div>
            <div class="col-6 text-end">
                <div class="signature-box text-end">
                    <div class="fw-bold text-dark">Authorized Quality Approver</div>
                    <small class="text-muted d-block">Quality Assurance Department</small>
                    <small class="text-muted">Date: {{ \Carbon\Carbon::parse($program->plan->created_at)->format('d M Y') }}</small>
                </div>
            </div>
        </div>

        <!-- Document Footer -->
        <div class="text-center text-muted small mt-5 pt-3 border-top" style="font-size: 10px;">
            This document is an official Proficiency Testing Scheme Plan generated by PT Management Software. Unauthorized copying or redistribution is strictly prohibited.
        </div>
    </div>

</body>
</html>
