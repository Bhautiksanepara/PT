<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sample Packing Slip & Chain of Custody - {{ $sample->sample_code }}</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Boxicons CSS -->
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }

        .slip-card {
            max-width: 850px;
            margin: 0 auto;
            background: #ffffff;
            border: 2px solid #0f172a;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }

        .slip-header {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }

        @media print {
            body {
                background: #ffffff !important;
            }
            .no-print {
                display: none !important;
            }
            .slip-card {
                border: 1px solid #000 !important;
                box-shadow: none !important;
                max-width: 100% !important;
                width: 100% !important;
                padding: 20px !important;
            }
        }
    </style>
</head>
<body>

    <!-- Top Action Bar -->
    <div class="bg-dark text-white py-3 mb-4 shadow-sm no-print">
        <div class="container d-flex justify-content-between align-items-center" style="max-width: 850px;">
            <div class="d-flex align-items-center gap-2">
                <i class="bx bx-package text-info fs-3"></i>
                <div>
                    <h6 class="fw-bold mb-0 text-white">ISO 17043 Sample Packing Slip & Chain of Custody</h6>
                    <small class="text-white-50">Proficiency Testing Scheme Material Dispatch Document</small>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-primary btn-sm fw-bold">
                    <i class="bx bx-printer me-1"></i> Print / Save PDF
                </button>
                <a href="{{ route('user.dispatches.index') }}" class="btn btn-outline-light btn-sm fw-bold">
                    <i class="bx bx-arrow-back me-1"></i> Back to Dispatches
                </a>
            </div>
        </div>
    </div>

    <!-- Main Printable Slip Document -->
    <div class="container mb-5">
        <div class="slip-card">
            
            <!-- Header -->
            <div class="slip-header d-flex justify-content-between align-items-start">
                <div>
                    <h4 class="fw-bold text-dark mb-1">PT SOFTWARE</h4>
                    <p class="text-muted small mb-0">ISO/IEC 17043 Accredited Proficiency Testing Provider</p>
                    <small class="text-muted">Document Ref: <strong>PT-SLIP-{{ $sample->sample_code }}</strong></small>
                </div>
                <div class="text-end">
                    <span class="badge bg-dark fs-6 px-3 py-2 font-monospace">{{ $sample->sample_code }}</span>
                    <small class="text-muted d-block mt-1">Date: {{ \Carbon\Carbon::parse($dispatch->dispatch_date)->format('d M Y') }}</small>
                </div>
            </div>

            <!-- Shipment & Recipient Grid -->
            <div class="row g-4 mb-4">
                <div class="col-6">
                    <div class="p-3 bg-light rounded border">
                        <h6 class="fw-bold text-primary mb-2"><i class="bx bx-building me-1"></i> Recipient Laboratory</h6>
                        <div class="small">
                            <div><strong>Lab Name:</strong> {{ $lab->laboratory_name }}</div>
                            <div><strong>NABL Cert #:</strong> {{ $lab->nabl_certificate_number ?: 'N/A' }}</div>
                            <div><strong>Contact Person:</strong> {{ $lab->contact_person }}</div>
                            <div><strong>Email:</strong> {{ $lab->email }}</div>
                            <div><strong>Address:</strong> {{ $lab->city }}, {{ $lab->state }} - {{ $lab->pincode }}</div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 bg-light rounded border">
                        <h6 class="fw-bold text-success mb-2"><i class="bx bx-package me-1"></i> Dispatch & Logistics Details</h6>
                        <div class="small">
                            <div><strong>Courier Partner:</strong> {{ $dispatch->courier_name }}</div>
                            <div><strong>Tracking / AWB #:</strong> <code class="fw-bold font-monospace">{{ $dispatch->tracking_number }}</code></div>
                            <div><strong>Dispatch Date:</strong> {{ \Carbon\Carbon::parse($dispatch->dispatch_date)->format('d M Y') }}</div>
                            <div><strong>Registration #:</strong> {{ $registration->registration_number }}</div>
                            <div><strong>Dispatched By:</strong> {{ $dispatch->dispatchedByAdmin->full_name ?? 'System Admin' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Program & Sample Material Details -->
            <h6 class="fw-bold text-dark mb-2"><i class="bx bx-flask me-1 text-primary"></i> PT Scheme Material & Parameters Included</h6>
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle small mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Scheme Code</th>
                            <th>Program Name</th>
                            <th>Discipline</th>
                            <th>Sample Quantity</th>
                            <th>Test Parameters</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="badge bg-light text-dark border">{{ $program->program_code }}</span></td>
                            <td class="fw-bold">{{ $program->program_name }}</td>
                            <td>{{ $program->discipline->discipline_name ?? 'N/A' }}</td>
                            <td>1 Unit Bottle</td>
                            <td>{{ $program->parameters->pluck('parameter_name')->join(', ') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Storage & Handling Instructions -->
            <div class="p-3 bg-light rounded border mb-4">
                <h6 class="fw-bold text-dark mb-2"><i class="bx bx-error-circle me-1 text-warning"></i> Mandatory Storage & Safety Instructions</h6>
                <ul class="small mb-0 ps-3 text-muted">
                    <li><strong>Storage Condition:</strong> Store sample container tightly sealed at <strong>2°C – 8°C</strong> in a dark, dry place immediately upon arrival.</li>
                    <li><strong>Verification on Arrival:</strong> Inspect bottle seal integrity. If damaged or leaked, log into Participant Portal to submit a damage flag.</li>
                    <li><strong>Observation Deadline:</strong> Submit test observations before <strong>{{ $program->submission_deadline ? \Carbon\Carbon::parse($program->submission_deadline)->format('d M Y') : 'N/A' }}</strong>.</li>
                </ul>
            </div>

            <!-- QR Verification & Signature Block -->
            <div class="row align-items-center mt-4 pt-3 border-top">
                <div class="col-6 text-center">
                    @php
                        $qrPayload = "SAMPLE PACKING SLIP\nSAMPLE ID: {$sample->sample_code}\nPROGRAM: {$program->program_code}\nLAB: {$lab->laboratory_name}\nCOURIER: {$dispatch->courier_name}\nTRACKING #: {$dispatch->tracking_number}";
                    @endphp
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode($qrPayload) }}" alt="QR Code" style="width: 120px; height: 120px;">
                    <div class="small font-monospace fw-bold text-primary mt-1">{{ $sample->sample_code }}</div>
                </div>
                <div class="col-6 text-center">
                    <div style="height: 50px;"></div>
                    <div class="border-top border-dark w-75 mx-auto pt-1">
                        <strong class="d-block small text-dark">Authorized PT Provider Signatory</strong>
                        <small class="text-muted micro-text">ISO/IEC 17043 Quality Officer</small>
                    </div>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
