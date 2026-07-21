@extends('layouts.user')

@section('title', 'Participant Dashboard')

@section('content')
<!-- Welcome Banner -->
<div class="card bg-dark text-white mb-4 shadow-sm">
    <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="fw-bold text-white mb-1"><i class="bx bx-building me-2 text-primary"></i> {{ $lab->laboratory_name }}</h4>
            <p class="text-white-50 mb-0 small">
                NABL Cert: <span class="badge bg-light text-dark me-2">{{ $lab->nabl_certificate_number ?? 'N/A' }}</span> | 
                User ID: <span class="badge bg-primary font-monospace">{{ $lab->username }}</span> | 
                Contact: {{ $lab->contact_person }} ({{ $lab->email }})
            </p>
        </div>
        <div>
            <span class="badge bg-success py-2 px-3 fs-6"><i class="bx bx-check-circle me-1"></i> Active Participant</span>
        </div>
    </div>
</div>

<!-- Section A: Active PT Programs Open for Registration -->
<div class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h5 class="fw-bold text-dark mb-0"><i class="bx bx-layer me-2 text-primary"></i> Available Active PT Programs</h5>
            <small class="text-muted">Browse upcoming ISO/IEC 17043 schemes and register your laboratory</small>
        </div>
        <span class="badge bg-primary">{{ $activePrograms->count() }} Schemes Open</span>
    </div>

    <div class="row g-3">
        @forelse($activePrograms as $prog)
            @php
                $isRegistered = in_array($prog->program_id, $userRegistrations->pluck('program_id')->toArray());
            @endphp
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-start {{ $isRegistered ? 'border-success' : 'border-primary' }} border-4 shadow-sm hover-shadow transition">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-primary font-monospace">{{ $prog->program_code }}</span>
                            <div>
                                @if($isRegistered)
                                    <span class="badge bg-success me-1"><i class="bx bx-check-circle me-1"></i> Registered</span>
                                @endif
                                <span class="badge bg-soft-success text-success fw-bold">₹{{ number_format($prog->program_fee, 2) }}</span>
                            </div>
                        </div>

                        <h6 class="fw-bold text-dark mb-2">{{ $prog->program_name }}</h6>
                        <small class="text-muted d-block mb-3">{{ Str::limit($prog->description, 90) }}</small>

                        <div class="bg-light p-2 rounded mb-3 small">
                            <div class="d-flex justify-content-between text-muted mb-1">
                                <span>Discipline:</span>
                                <strong class="text-dark">{{ $prog->discipline }}</strong>
                            </div>
                            <div class="d-flex justify-content-between text-muted mb-1">
                                <span>Registration Deadline:</span>
                                <strong class="text-danger">{{ \Carbon\Carbon::parse($prog->registration_end_date)->format('d M Y') }}</strong>
                            </div>
                            <div class="d-flex justify-content-between text-muted">
                                <span>Test Parameters:</span>
                                <strong class="text-primary">{{ $prog->parameters->count() }} Parameters</strong>
                            </div>
                        </div>

                        <div class="mt-auto d-grid gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#progDetailModal{{ $prog->program_id }}">
                                <i class="bx bx-info-circle me-1"></i> View Details
                            </button>
                            @if($isRegistered)
                                <button type="button" class="btn btn-success btn-sm fw-bold disabled" disabled>
                                    <i class="bx bx-check-circle me-1"></i> Already Registered
                                </button>
                            @else
                                <a href="{{ route('user.program.register', $prog->program_id) }}" class="btn btn-primary btn-sm fw-bold">
                                    <i class="bx bx-edit me-1"></i> Register Now
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Program Detail Modal -->
            <div class="modal fade" id="progDetailModal{{ $prog->program_id }}" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h6 class="modal-title fw-bold"><i class="bx bx-layer me-1 text-primary"></i> Scheme Specifications: {{ $prog->program_code }}</h6>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <h5 class="fw-bold text-dark">{{ $prog->program_name }}</h5>
                            <p class="text-muted small">{{ $prog->description }}</p>

                            <div class="row g-2 mb-3 bg-light p-3 rounded border">
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Discipline:</small>
                                    <strong class="text-dark">{{ $prog->discipline }}</strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Scheme Code:</small>
                                    <strong class="text-dark">{{ $prog->scheme_code }}</strong>
                                </div>
                                <div class="col-md-4">
                                    <small class="text-muted d-block">Program Fee:</small>
                                    <strong class="text-success fs-5">₹{{ number_format($prog->program_fee, 2) }}</strong>
                                </div>
                            </div>

                            <h6 class="fw-bold text-dark mb-2">Test Parameters Included:</h6>
                            <ul class="list-group mb-3">
                                @foreach($prog->parameters as $pm)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="fw-bold text-dark">{{ $pm->parameter_name }}</span>
                                            <small class="text-muted d-block">Method: {{ $pm->test_method }}</small>
                                        </div>
                                        <span class="badge bg-light text-dark border">{{ $pm->unit }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                            @if($isRegistered)
                                <button type="button" class="btn btn-success btn-sm fw-bold disabled" disabled><i class="bx bx-check-circle me-1"></i> Already Registered</button>
                            @else
                                <a href="{{ route('user.program.register', $prog->program_id) }}" class="btn btn-primary btn-sm fw-bold">Register Now</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card p-4 text-center text-muted">
                    <i class="bx bx-info-circle fs-1 text-primary mb-2"></i>
                    <p class="mb-0">No active PT programs are currently open for registration.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>

<!-- Section B: Registered Programs & History Vault -->
<div class="card">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <div>
            <h6 class="fw-bold mb-0 text-dark"><i class="bx bx-history me-2 text-primary"></i> My Registered PT History & Reports Vault</h6>
            <small class="text-muted">Track sample dispatches, submit test observations, and download official reports/certificates</small>
        </div>
        <span class="badge bg-secondary">{{ $userRegistrations->count() }} Subscriptions</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Registration #</th>
                        <th>Program Code</th>
                        <th>Sample Code</th>
                        <th>Payment Status</th>
                        <th>Dispatch Status</th>
                        <th>Observations</th>
                        <th class="text-end text-nowrap">Reports & Downloads</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($userRegistrations as $reg)
                        <tr>
                            <td>
                                <span class="fw-bold text-primary">{{ $reg->registration_number }}</span>
                                <small class="text-muted d-block micro-text">{{ \Carbon\Carbon::parse($reg->registered_at)->format('d M Y') }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $reg->program->program_code }}</span>
                            </td>
                            <td>
                                @if($reg->sample)
                                    <span class="badge bg-primary font-monospace"><i class="bx bx-barcode me-1"></i> {{ $reg->sample->sample_code }}</span>
                                @else
                                    <span class="badge bg-light text-muted border">Pending Assignment</span>
                                @endif
                            </td>
                            <td>
                                @if(($reg->payment->payment_status ?? '') === 'success')
                                    <span class="badge badge-soft-success"><i class="bx bx-check-circle me-1"></i> Paid (₹{{ number_format($reg->payment->final_amount, 2) }})</span>
                                @else
                                    <span class="badge badge-soft-warning">Payment Pending</span>
                                @endif
                            </td>
                            <td>
                                @if($reg->sample && $reg->sample->status === 'dispatched' && $reg->sample->dispatch)
                                    <button type="button" class="btn btn-sm btn-soft-info p-1 px-2 border-0 fw-semibold" data-bs-toggle="modal" data-bs-target="#trackingModal{{ $reg->registration_id }}" title="Click to view tracking details & QR code">
                                        <i class="bx bx-package me-1"></i> Dispatched ({{ $reg->sample->dispatch->courier_name }}) <i class="bx bx-qr-scan ms-1"></i>
                                    </button>

                                    <!-- Tracking & QR Code Modal -->
                                    <div class="modal fade text-start" id="trackingModal{{ $reg->registration_id }}" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header bg-dark text-white py-3">
                                                    <h6 class="modal-title fw-bold text-white mb-0"><i class="bx bx-package text-info me-1"></i> Sample Shipment & QR Code Receipt</h6>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body text-center p-4">
                                                    @php
                                                        $sampleCode = $reg->sample->sample_code ?? 'N/A';
                                                        $courier = $reg->sample->dispatch->courier_name ?? 'Courier Service';
                                                        $tracking = $reg->sample->dispatch->tracking_number ?? 'N/A';
                                                        $dispatchDate = \Carbon\Carbon::parse($reg->sample->dispatch->dispatch_date)->format('d M Y');
                                                        $qrPayload = "SAMPLE ID: {$sampleCode}\nPROGRAM: {$reg->program->program_code}\nCOURIER: {$courier}\nTRACKING #: {$tracking}\nDISPATCH DATE: {$dispatchDate}\nLABORATORY: {$lab->laboratory_name}";
                                                    @endphp

                                                    <div class="p-3 bg-light rounded border d-inline-block mb-3 shadow-sm">
                                                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data={{ urlencode($qrPayload) }}" alt="Sample QR Code" style="width: 160px; height: 160px;">
                                                        <div class="mt-2 font-monospace fw-bold text-primary fs-6">{{ $sampleCode }}</div>
                                                    </div>

                                                    <p class="small text-muted mb-3">Scan this QR Code upon parcel arrival to verify official sample authenticity.</p>

                                                    <div class="bg-light p-3 rounded border text-start mb-2">
                                                        <div class="row g-2 small">
                                                            <div class="col-6"><span class="text-muted d-block">Courier Partner:</span> <strong>{{ $courier }}</strong></div>
                                                            <div class="col-6"><span class="text-muted d-block">Tracking Number:</span> <code class="fw-bold text-dark font-monospace fs-6">{{ $tracking }}</code></div>
                                                            <div class="col-6 mt-2"><span class="text-muted d-block">Dispatch Date:</span> <strong>{{ $dispatchDate }}</strong></div>
                                                            <div class="col-6 mt-2"><span class="text-muted d-block">PT Scheme:</span> <strong>{{ $reg->program->program_code }}</strong></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light py-2">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <span class="badge bg-light text-muted border">In Processing</span>
                                @endif
                            </td>
                            <td>
                                @if($reg->observations->count() > 0)
                                    <span class="badge badge-soft-success"><i class="bx bx-file-find me-1"></i> {{ $reg->observations->count() }} Results Submitted</span>
                                @else
                                    <span class="badge bg-light text-muted border">Awaiting Results</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('user.invoice', $reg->registration_id) }}" class="btn btn-sm btn-outline-secondary text-nowrap" target="_blank" title="Download Tax Invoice">
                                        <i class="bx bx-receipt me-1"></i> Tax Invoice
                                    </a>

                                    @if($reg->reports->count() > 0 || $reg->observations->count() > 0)
                                        <a href="{{ route('user.reports.individual', [$reg->program_id, $reg->registration_id]) }}" class="btn btn-sm btn-outline-primary text-nowrap" target="_blank" title="View Official PT Evaluation Report">
                                            <i class="bx bx-file me-1"></i> PT Report
                                        </a>
                                        <a href="{{ route('user.reports.certificate', [$reg->program_id, $reg->registration_id]) }}" class="btn btn-sm btn-outline-success text-nowrap" target="_blank" title="Download Certificate of Participation">
                                            <i class="bx bx-award me-1"></i> Certificate
                                        </a>
                                    @else
                                        <button class="btn btn-sm btn-outline-secondary text-nowrap opacity-50" disabled title="Report will be unlocked after testing & evaluation completion">
                                            <i class="bx bx-lock-alt me-1"></i> PT Report
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary text-nowrap opacity-50" disabled title="Certificate will be unlocked after testing & evaluation completion">
                                            <i class="bx bx-lock-alt me-1"></i> Certificate
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                You have not registered for any PT programs yet. Choose a program above to get started!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
