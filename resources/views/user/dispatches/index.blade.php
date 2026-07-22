@extends('layouts.user')

@section('title', 'Sample Dispatches & QR Tracking')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bx bx-package me-2 text-primary"></i> Phase 5 – Sample Dispatches & Live Tracking</h4>
        <p class="text-muted small mb-0">Track physical courier shipments, live AWB tracking URLs, confirm parcel receipts, and view ISO 17043 packing slips</p>
    </div>
    <div>
        <a href="{{ route('user.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i> Back to Dashboard
        </a>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form action="{{ route('user.dispatches.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-9">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bx bx-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search Sample Code, Courier, Tracking #, Program..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bx bx-filter-alt me-1"></i> Search</button>
                @if(request()->has('search'))
                    <a href="{{ route('user.dispatches.index') }}" class="btn btn-light btn-sm"><i class="bx bx-reset"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Dispatches Data Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Registration #</th>
                        <th>Program Code</th>
                        <th>Sample Code</th>
                        <th>Courier Partner</th>
                        <th>Tracking / AWB #</th>
                        <th>Dispatch Date</th>
                        <th>Status & Progress</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrations as $reg)
                        @php
                            $sample = $reg->sample;
                            $dispatch = $sample->dispatch ?? null;
                            $isDispatched = $sample && in_array($sample->status, ['dispatched', 'received']) && $dispatch;
                            $isReceived = $sample && $sample->status === 'received';
                            $isTested = $reg->observations->count() > 0;
                            $sampleCode = $sample->sample_code ?? 'N/A';
                            $courier = $dispatch->courier_name ?? 'N/A';
                            $tracking = $dispatch->tracking_number ?? 'N/A';
                            $dispatchDate = $dispatch ? \Carbon\Carbon::parse($dispatch->dispatch_date)->format('d M Y') : 'N/A';
                            $trackingUrl = $isDispatched ? \App\Http\Controllers\User\UserDispatchController::getCourierTrackingUrl($courier, $tracking) : '#';
                        @endphp
                        <tr>
                            <td>
                                <span class="fw-bold text-primary">{{ $reg->registration_number }}</span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $reg->program->program_name }}</div>
                                <span class="badge bg-light text-dark border">{{ $reg->program->program_code }}</span>
                            </td>
                            <td>
                                @if($sample)
                                    <span class="badge bg-primary font-monospace"><i class="bx bx-barcode me-1"></i> {{ $sampleCode }}</span>
                                @else
                                    <span class="badge bg-light text-muted border">Pending Assignment</span>
                                @endif
                            </td>
                            <td>
                                @if($isDispatched)
                                    <span class="fw-semibold text-dark">{{ $courier }}</span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="font-monospace">
                                @if($isDispatched)
                                    <code>{{ $tracking }}</code>
                                    <a href="{{ $trackingUrl }}" target="_blank" class="btn btn-xs btn-outline-primary ms-1 py-0 px-1 micro-text" title="Track Live on Courier Website">
                                        Track Live <i class="bx bx-export"></i>
                                    </a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="small text-muted">{{ $dispatchDate }}</td>
                            <td>
                                @if($isTested)
                                    <span class="badge badge-soft-success py-2 px-3"><i class="bx bx-check-double me-1"></i> Testing Completed</span>
                                @elseif($isReceived)
                                    <span class="badge badge-soft-success py-2 px-3"><i class="bx bx-package me-1"></i> Received at Lab</span>
                                    @if($dispatch && $dispatch->arrival_condition === 'damaged')
                                        <small class="text-danger d-block micro-text fw-bold">Condition: Damaged</small>
                                    @endif
                                @elseif($isDispatched)
                                    <span class="badge badge-soft-info py-2 px-3"><i class="bx bx-truck me-1"></i> In Transit</span>
                                @else
                                    <span class="badge badge-soft-warning py-2 px-3"><i class="bx bx-time me-1"></i> In Processing</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    @if($isDispatched)
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#dispatchModal{{ $reg->registration_id }}" title="View Tracking & QR Receipt">
                                            <i class="bx bx-qr-scan me-1"></i> Tracking & QR
                                        </button>

                                        <a href="{{ route('user.dispatches.slip', $reg->registration_id) }}" class="btn btn-sm btn-outline-dark" target="_blank" title="Print ISO 17043 Packing Slip">
                                            <i class="bx bx-receipt me-1"></i> Slip
                                        </a>

                                        @if(!$isReceived)
                                            <button type="button" class="btn btn-sm btn-success fw-bold" data-bs-toggle="modal" data-bs-target="#confirmModal{{ $reg->registration_id }}" title="Confirm parcel arrival & check condition">
                                                <i class="bx bx-check-circle me-1"></i> Confirm Receipt
                                            </button>
                                        @endif
                                    @else
                                        <button class="btn btn-sm btn-outline-secondary opacity-50" disabled><i class="bx bx-time me-1"></i> Pending</button>
                                    @endif
                                </div>

                                @if($isDispatched)
                                    <!-- Tracking & Visual Progress Stepper Modal -->
                                    <div class="modal fade text-start" id="dispatchModal{{ $reg->registration_id }}" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header bg-dark text-white py-3">
                                                    <h6 class="modal-title fw-bold text-white mb-0"><i class="bx bx-package text-info me-1"></i> Shipment Tracking & Visual Stepper</h6>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body p-4" id="printableQrSlip{{ $reg->registration_id }}">
                                                    
                                                    <!-- Visual Shipment Stepper Progress Bar -->
                                                    <div class="mb-4 bg-light p-3 rounded border">
                                                        <small class="fw-bold text-dark d-block mb-3"><i class="bx bx-git-commit me-1 text-primary"></i> Live Shipment Progress Stepper:</small>
                                                        <div class="d-flex justify-content-between text-center small position-relative">
                                                            <div class="flex-fill">
                                                                <div class="badge rounded-circle bg-success p-2 mb-1"><i class="bx bx-check fs-6"></i></div>
                                                                <div class="fw-bold text-dark micro-text">1. Sample Assigned</div>
                                                                <small class="text-muted micro-text">{{ $sampleCode }}</small>
                                                            </div>
                                                            <div class="flex-fill">
                                                                <div class="badge rounded-circle {{ $isDispatched ? 'bg-success' : 'bg-secondary' }} p-2 mb-1"><i class="bx bx-truck fs-6"></i></div>
                                                                <div class="fw-bold text-dark micro-text">2. Dispatched</div>
                                                                <small class="text-muted micro-text">{{ $dispatchDate }}</small>
                                                            </div>
                                                            <div class="flex-fill">
                                                                <div class="badge rounded-circle {{ $isReceived ? 'bg-success' : 'bg-secondary' }} p-2 mb-1"><i class="bx bx-building fs-6"></i></div>
                                                                <div class="fw-bold text-dark micro-text">3. Received at Lab</div>
                                                                <small class="text-muted micro-text">{{ $dispatch->received_at ? \Carbon\Carbon::parse($dispatch->received_at)->format('d M Y') : 'Pending' }}</small>
                                                            </div>
                                                            <div class="flex-fill">
                                                                <div class="badge rounded-circle {{ $isTested ? 'bg-success' : 'bg-secondary' }} p-2 mb-1"><i class="bx bx-vial fs-6"></i></div>
                                                                <div class="fw-bold text-dark micro-text">4. Testing Done</div>
                                                                <small class="text-muted micro-text">{{ $isTested ? 'Completed' : 'Awaiting' }}</small>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center">
                                                        <div class="col-md-5 text-center mb-3 mb-md-0">
                                                            @php
                                                                $qrPayload = "SAMPLE ID: {$sampleCode}\nPROGRAM: {$reg->program->program_code}\nCOURIER: {$courier}\nTRACKING #: {$tracking}\nDISPATCH DATE: {$dispatchDate}\nLABORATORY: {$lab->laboratory_name}";
                                                            @endphp
                                                            <div class="p-3 bg-light rounded border d-inline-block shadow-sm">
                                                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode($qrPayload) }}" alt="Sample QR Code" style="width: 150px; height: 150px;">
                                                                <div class="mt-2 font-monospace fw-bold text-primary fs-6">{{ $sampleCode }}</div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-7">
                                                            <div class="bg-light p-3 rounded border">
                                                                <h6 class="fw-bold text-dark mb-2">Courier Logistics Details</h6>
                                                                <div class="row g-2 small">
                                                                    <div class="col-6"><span class="text-muted d-block">Courier Partner:</span> <strong>{{ $courier }}</strong></div>
                                                                    <div class="col-6"><span class="text-muted d-block">Tracking Number:</span> <code class="fw-bold text-dark font-monospace fs-6">{{ $tracking }}</code></div>
                                                                    <div class="col-6 mt-2"><span class="text-muted d-block">Dispatch Date:</span> <strong>{{ $dispatchDate }}</strong></div>
                                                                    <div class="col-6 mt-2"><span class="text-muted d-block">PT Scheme:</span> <strong>{{ $reg->program->program_code }}</strong></div>
                                                                    <div class="col-12 mt-2">
                                                                        <a href="{{ $trackingUrl }}" target="_blank" class="btn btn-sm btn-primary w-100 fw-bold">
                                                                            <i class="bx bx-export me-1"></i> Live Courier Website Tracking Portal
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light py-2 justify-content-between">
                                                    <button type="button" class="btn btn-outline-dark btn-sm" onclick="printQrSlip('printableQrSlip{{ $reg->registration_id }}')">
                                                        <i class="bx bx-printer me-1"></i> Print QR Slip
                                                    </button>
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Confirm Receipt & Condition Check Modal -->
                                    <div class="modal fade text-start" id="confirmModal{{ $reg->registration_id }}" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <form action="{{ route('user.dispatches.confirm', $reg->registration_id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header bg-success text-white py-3">
                                                        <h6 class="modal-title fw-bold text-white mb-0"><i class="bx bx-check-circle me-1"></i> Confirm Parcel Arrival & Condition</h6>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <p class="small text-muted mb-3">Please inspect the physical sample container received via {{ $courier }} (<code>{{ $tracking }}</code>) and record bottle condition.</p>
                                                        
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-bold text-dark">Sample Arrival Condition <span class="text-danger">*</span></label>
                                                            <div class="d-flex gap-3">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="radio" name="arrival_condition" id="condIntact{{ $reg->registration_id }}" value="intact" checked>
                                                                    <label class="form-check-input-label text-success fw-bold" for="condIntact{{ $reg->registration_id }}">
                                                                        <i class="bx bx-check-shield me-1"></i> Intact & Sealed
                                                                    </label>
                                                                </div>
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="radio" name="arrival_condition" id="condDamaged{{ $reg->registration_id }}" value="damaged">
                                                                    <label class="form-check-input-label text-danger fw-bold" for="condDamaged{{ $reg->registration_id }}">
                                                                        <i class="bx bx-error me-1"></i> Damaged
                                                                    </label>
                                                                </div>
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="radio" name="arrival_condition" id="condLeaked{{ $reg->registration_id }}" value="leaked">
                                                                    <label class="form-check-input-label text-warning fw-bold" for="condLeaked{{ $reg->registration_id }}">
                                                                        <i class="bx bx-droplet me-1"></i> Container Leaked
                                                                    </label>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label small fw-semibold">Condition Notes / Remarks (Optional)</label>
                                                            <textarea name="condition_notes" class="form-control form-control-sm" rows="2" placeholder="e.g. Parcel received in good condition, seal unbroken."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer bg-light py-2">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-success btn-sm fw-bold">Confirm Receipt</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bx bx-package display-4 text-muted d-block mb-2"></i>
                                No sample dispatches found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($registrations->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $registrations->links() }}
        </div>
    @endif
</div>

<script>
function printQrSlip(elementId) {
    var printContents = document.getElementById(elementId).innerHTML;
    var originalContents = document.body.innerHTML;
    document.body.innerHTML = '<div style="padding: 40px; text-align: center;">' + printContents + '</div>';
    window.print();
    document.body.innerHTML = originalContents;
    window.location.reload();
}
</script>
@endsection
