@extends('layouts.app')

@section('title', 'Certificate of Participation')

@push('styles')
<style>
    @media print {
        body { background: #fff !important; }
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
        .cert-card { 
            border: 10px solid #0f172a !important; 
            box-shadow: none !important; 
            max-width: 100% !important;
            width: 100% !important;
            margin: 0 !important;
        }
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
    }
    .cert-container { max-width: 920px; margin: 0 auto; }
    .cert-card {
        background: #fff;
        border: 12px double #1e293b;
        border-radius: 8px;
        padding: 40px 50px;
        position: relative;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .cert-title { font-family: 'Times New Roman', Times, serif; font-size: 32px; font-weight: 700; color: #0f172a; letter-spacing: 2px; }
    .cert-sub { font-family: 'Times New Roman', Times, serif; font-size: 16px; font-style: italic; color: #475569; }
    .lab-name-heading { font-size: 28px; font-weight: 800; color: #1e3a8a; border-bottom: 2px solid #cbd5e1; display: inline-block; padding-bottom: 5px; }
    .seal-badge {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f59e0b 0%, #b45309 100%);
        color: #fff;
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
    .sig-line-cert { border-top: 2px solid #334155; width: 200px; display: inline-block; margin-top: 50px; }
</style>
@endpush

@section('content')
<!-- Print Action Bar -->
<div class="d-flex justify-content-between align-items-center mb-4 btn-print-bar">
    <div>
        <h4 class="fw-bold mb-1">ISO 17043 Certificate of Participation</h4>
        <p class="text-muted small mb-0">Official ISO/IEC 17043 Accredited Proficiency Testing Certificate</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-success btn-sm shadow-sm">
            <i class="bx bx-printer me-1"></i> Print Certificate / Save PDF
        </button>
        <a href="{{ route('admin.reports.individual', [$program->program_id, $registration->registration_id]) }}" class="btn btn-outline-primary btn-sm">
            <i class="bx bx-file me-1"></i> View PT Evaluation Report
        </a>
        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i> Back to Directory
        </a>
    </div>
</div>

<!-- Certificate Document -->
<div class="cert-container mb-5">
    <div class="cert-card text-center">
        <!-- Top Header & Accreditation Notice -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="text-start">
                <small class="fw-bold text-dark d-block">GLOBAL PT PROVIDER SERVICES</small>
                <small class="text-muted micro-text">ISO/IEC 17043:2023 Accredited Body</small>
            </div>
            <div>
                <span class="badge bg-dark px-3 py-2">CERTIFICATE NO: CERT-{{ str_pad($registration->registration_id, 6, '0', STR_PAD_LEFT) }}</span>
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
            <span class="badge bg-secondary fs-6">Discipline: {{ $program->discipline->discipline_name ?? $program->discipline }}</span>
            <small class="d-block text-muted mt-2">Assigned Sample Identifier: <strong>{{ $sample->sample_code ?? 'N/A' }}</strong></small>
        </div>

        <!-- Seal & Verification -->
        <div class="row align-items-center my-4">
            <div class="col-4">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ urlencode($qrPayload) }}" alt="QR Code" class="border p-1 bg-white" style="width: 90px; height: 90px;">
                <small class="text-muted d-block micro-text mt-1">Scan for ISO 17043 Verification</small>
            </div>
            <div class="col-4">
                <div class="seal-badge">
                    <span>OFFICIAL<br>ISO 17043<br>SEAL</span>
                </div>
            </div>
            <div class="col-4">
                <small class="text-muted d-block fw-semibold">Issue Date</small>
                <span class="fw-bold text-dark fs-6">{{ date('d F Y') }}</span>
            </div>
        </div>

        <!-- Signature Blocks -->
        <div class="row mt-5 pt-3">
            <div class="col-6">
                <div class="sig-line-cert"></div>
                <h6 class="fw-bold text-dark mb-0 mt-2">Dr. System Administrator</h6>
                <small class="text-muted">Quality Assurance Manager</small>
            </div>
            <div class="col-6">
                <div class="sig-line-cert"></div>
                <h6 class="fw-bold text-dark mb-0 mt-2">Proficiency Testing Director</h6>
                <small class="text-muted">ISO/IEC 17043 Technical Authority</small>
            </div>
        </div>
    </div>
</div>
@endsection
