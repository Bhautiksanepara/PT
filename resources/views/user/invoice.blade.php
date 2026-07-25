<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice - {{ $registration->registration_number }} - PT Software</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Boxicons -->
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background: #f8fafc; color: #1e293b; padding: 20px 0; }
        .invoice-card { max-width: 800px; margin: 0 auto; background: #ffffff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; }
        .invoice-header { border-bottom: 2px solid #0f172a; padding-bottom: 20px; margin-bottom: 30px; }
        .sig-line { border-top: 1px dashed #94a3b8; width: 180px; margin: 40px auto 5px auto; }
        @media print {
            body { background: #ffffff; padding: 0; }
            .invoice-card { box-shadow: none; border: none; padding: 0; width: 100%; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="no-print text-center mb-4">
        <button onclick="window.print()" class="btn btn-primary btn-sm px-4 fw-bold me-2">
            <i class="bx bx-printer me-1"></i> Print / Download Invoice
        </button>
        <a href="{{ route('user.dashboard') }}" class="btn btn-outline-secondary btn-sm px-3">
            <i class="bx bx-arrow-back me-1"></i> Back to Dashboard
        </a>
    </div>

    <div class="invoice-card">
        <!-- Header -->
        <div class="invoice-header d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold text-dark mb-0">PT SOFTWARE PORTAL</h3>
                <small class="text-muted d-block">ISO/IEC 17043 Accredited PT Provider</small>
                <small class="text-muted">GSTIN: 27AAAAA0000A1Z5 | Reg: PT-IND-2026</small>
            </div>
            <div class="text-end">
                <span class="badge bg-dark fs-6 font-monospace mb-1">TAX INVOICE</span>
                <div class="fw-bold text-primary fs-5">{{ $registration->registration_number }}</div>
                <small class="text-muted d-block">Date: {{ \Carbon\Carbon::parse($registration->registered_at)->format('d M Y') }}</small>
            </div>
        </div>

        <!-- Addresses -->
        <div class="row mb-4">
            <div class="col-6">
                <small class="text-muted text-uppercase fw-bold d-block mb-1">Billed To (Participant Lab):</small>
                <h6 class="fw-bold text-dark mb-1">{{ $lab->laboratory_name }}</h6>
                <small class="text-muted d-block">Contact: {{ $lab->contact_person }} ({{ $lab->designation }})</small>
                <small class="text-muted d-block">GSTIN: {{ $lab->gst_number ?? 'N/A' }}</small>
                <small class="text-muted d-block">Email: {{ $lab->email }} | Mob: {{ $lab->mobile_number }}</small>
                <small class="text-muted d-block mt-1">{{ $registration->billing_address }}</small>
            </div>
            <div class="col-6 text-end">
                <small class="text-muted text-uppercase fw-bold d-block mb-1">Payment Status:</small>
                @if(($registration->payment->payment_status ?? '') === 'success')
                    <span class="badge bg-success py-2 px-3 mb-2 fs-6"><i class="bx bx-check-circle me-1"></i> PAID</span>
                @else
                    <span class="badge bg-warning text-dark py-2 px-3 mb-2 fs-6">PENDING</span>
                @endif
                <small class="text-muted d-block">Payment Method: {{ $registration->payment->payment_method ?? 'N/A' }}</small>
                <small class="text-muted d-block font-monospace">Transaction ID: {{ $registration->payment->transaction_id ?? 'N/A' }}</small>
                <small class="text-muted d-block">Paid Date: {{ \Carbon\Carbon::parse($registration->payment->paid_at ?? now())->format('d M Y, h:i A') }}</small>
            </div>
        </div>

        <!-- Table -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Scheme / Item Description</th>
                        <th>Scheme Code</th>
                        <th class="text-end">Amount (INR)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>
                            <strong class="text-dark">{{ $registration->program->program_name }}</strong>
                            <small class="text-muted d-block">Discipline: {{ $registration->program->discipline->discipline_name ?? 'N/A' }} | Sample Quantity: {{ $registration->sample_quantity }}</small>
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $registration->program->program_code }}</span></td>
                        <td class="text-end fw-semibold">₹{{ number_format($registration->payment->amount ?? $registration->program->program_fee, 2) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end text-muted">Subtotal Fee:</th>
                        <th class="text-end">₹{{ number_format($registration->payment->amount ?? $registration->program->program_fee, 2) }}</th>
                    </tr>
                    @if(($registration->discount_applied ?? 0) > 0)
                        <tr class="text-success">
                            <th colspan="3" class="text-end">Referral Code Discount Applied:</th>
                            <th class="text-end">- ₹{{ number_format($registration->discount_applied, 2) }}</th>
                        </tr>
                    @endif
                    <tr class="fs-5 table-light">
                        <th colspan="3" class="text-end text-dark">Total Net Amount Paid:</th>
                        <th class="text-end text-primary fw-bold">₹{{ number_format($registration->payment->final_amount ?? $registration->program->program_fee, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Footer Signatures -->
        <div class="row pt-4 text-center">
            <div class="col-6 text-start">
                <small class="text-muted d-block">Terms & Conditions:</small>
                <small class="text-muted micro-text d-block">• Payment received for proficiency testing participation.</small>
                <small class="text-muted micro-text d-block">• Computer generated invoice, valid without seal.</small>
            </div>
            <div class="col-6">
                <div class="sig-line"></div>
                <small class="fw-bold text-dark d-block">Authorized Accounts Officer</small>
                <small class="text-muted micro-text">PT Software Accounts Department</small>
            </div>
        </div>
    </div>
</div>

</body>
</html>
