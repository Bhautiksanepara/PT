<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate of Participation - {{ $registration->registration_number }} - PT Software</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Boxicons CSS -->
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
        }

        .cert-container {
            max-width: 920px;
            margin: 0 auto;
        }

        .cert-card {
            background: #ffffff;
            border: 12px double #1e293b;
            border-radius: 12px;
            padding: 45px 55px;
            position: relative;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        }

        .cert-title {
            font-family: 'Times New Roman', Times, serif;
            font-size: 34px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: 2px;
        }

        .cert-sub {
            font-family: 'Times New Roman', Times, serif;
            font-size: 17px;
            font-style: italic;
            color: #475569;
        }

        .lab-name-heading {
            font-size: 28px;
            font-weight: 800;
            color: #1e3a8a;
            border-bottom: 2px solid #cbd5e1;
            display: inline-block;
            padding-bottom: 5px;
        }

        .seal-badge {
            width: 105px;
            height: 105px;
            border-radius: 50%;
            background: linear-gradient(135deg, #f59e0b 0%, #b45309 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            font-weight: 700;
            font-size: 11px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            margin: 0 auto;
            border: 4px solid #fef3c7;
        }

        .sig-line-cert {
            border-top: 2px solid #334155;
            width: 200px;
            display: inline-block;
            margin-top: 50px;
        }

        .micro-text {
            font-size: 11px;
        }

        @media print {
            body {
                background: #ffffff !important;
            }
            .no-print {
                display: none !important;
            }
            .cert-card {
                border: 10px solid #0f172a !important;
                box-shadow: none !important;
            }
            .cert-container {
                max-width: 100% !important;
                margin: 0 !important;
            }
        }
    </style>
</head>
<body>

    <!-- Top Action Bar (Participant View) -->
    <div class="bg-dark text-white py-3 mb-4 shadow-sm no-print">
        <div class="container d-flex justify-content-between align-items-center" style="max-width: 920px;">
            <div class="d-flex align-items-center gap-2">
                <i class="bx bx-award text-warning fs-3"></i>
                <div>
                    <h6 class="fw-bold mb-0">ISO 17043 Certificate of Participation</h6>
                    <small class="text-white-50 micro-text">Official Accredited Proficiency Testing Certificate</small>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-success btn-sm fw-bold shadow-sm">
                    <i class="bx bx-printer me-1"></i> Print / Save PDF
                </button>
                <a href="{{ route('user.reports.individual', [$program->program_id, $registration->registration_id]) }}" class="btn btn-primary btn-sm fw-bold">
                    <i class="bx bx-file me-1"></i> View PT Evaluation Report
                </a>
                <a href="{{ route('user.dashboard') }}" class="btn btn-outline-light btn-sm fw-bold">
                    <i class="bx bx-arrow-back me-1"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Certificate Document Container -->
    <div class="cert-container mb-5">
        <div class="cert-card text-center">

            <!-- Top Header & Accreditation Notice -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="text-start">
                    <small class="fw-bold text-dark d-block">GLOBAL PT PROVIDER SERVICES</small>
                    <small class="text-muted micro-text">ISO/IEC 17043:2023 Accredited Body</small>
                </div>
                <div>
                    <span class="badge bg-dark px-3 py-2 fs-6">CERTIFICATE NO: CERT-{{ str_pad($registration->registration_id, 6, '0', STR_PAD_LEFT) }}</span>
                </div>
            </div>

            <hr class="my-3 text-secondary">

            <!-- Certificate Title -->
            <div class="my-4">
                <h1 class="cert-title text-uppercase">Certificate of Participation</h1>
                <p class="cert-sub">This is to officially certify that</p>
            </div>

            <!-- Participant Lab Name -->
            <div class="my-3">
                <h2 class="lab-name-heading">{{ $lab->laboratory_name }}</h2>
                <p class="text-muted small mt-2">NABL Accreditation Certificate #: <strong>{{ $lab->nabl_certificate_number ?? 'N/A' }}</strong></p>
                <p class="text-muted small">Location: {{ $lab->city }}, {{ $lab->state }}, {{ $lab->country }}</p>
            </div>

            <p class="cert-sub my-3">has successfully participated and submitted test observations in the Proficiency Testing Scheme</p>

            <!-- PT Scheme Details -->
            <div class="p-3 bg-light rounded border my-4 d-inline-block px-5">
                <h4 class="fw-bold text-dark mb-1">{{ $program->program_name }}</h4>
                <span class="badge bg-primary fs-6 me-2">Scheme Code: {{ $program->program_code }}</span>
                <span class="badge bg-secondary fs-6">Discipline: {{ $program->discipline->discipline_name ?? 'N/A' }}</span>
            </div>

            <p class="text-muted small my-3">
                Evaluation performed under ISO 13528 statistical procedures.<br>
                Date of Issue: <strong>{{ date('d M Y') }}</strong>
            </p>

            <!-- Seal, Signatures & Verification -->
            <div class="row align-items-end mt-5 pt-4">
                <div class="col-4 text-center">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=110x110&data={{ urlencode('ISO 17043 PT CERTIFICATE\nLAB: ' . $lab->laboratory_name . '\nREG: ' . $registration->registration_number . '\nVERIFIED: SUCCESSFUL') }}" alt="QR Code" class="border p-1 bg-white mb-1" style="width: 95px; height: 95px;">
                    <small class="text-muted d-block micro-text">Digital Authenticity QR</small>
                </div>
                <div class="col-4 text-center">
                    <div class="seal-badge">
                        <span>ISO/IEC<br>17043<br>SEAL</span>
                    </div>
                </div>
                <div class="col-4 text-center">
                    <div class="sig-line-cert"></div>
                    <small class="fw-bold text-dark d-block mt-1">PT Director / Signatory</small>
                    <small class="text-muted micro-text">Authorized QA Officer</small>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
