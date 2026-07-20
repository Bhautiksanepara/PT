@extends('layouts.app')

@section('title', 'Participant Profile')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">{{ $lab->laboratory_name }}</h4>
        <p class="text-muted small mb-0">Participant ID: LAB-{{ str_pad($lab->lab_id, 4, '0', STR_PAD_LEFT) }}</p>
    </div>
    <a href="{{ route('admin.participants.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bx bx-arrow-back me-1"></i> Back to Master Sheet
    </a>
</div>

<div class="row g-4">
    <!-- Left Column: Laboratory Details Card -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="fw-bold mb-0"><i class="bx bx-building me-1 text-primary"></i> Company & Contact Info</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block">Account Status</small>
                    @if($lab->status === 'active')
                        <span class="badge badge-soft-success">Active</span>
                    @elseif($lab->status === 'inactive')
                        <span class="badge badge-soft-secondary">Inactive</span>
                    @else
                        <span class="badge badge-soft-danger">Suspended</span>
                    @endif
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">Contact Person</small>
                    <span class="fw-semibold text-dark">{{ $lab->contact_person ?? 'N/A' }}</span>
                    @if($lab->designation)
                        <small class="text-muted d-block">({{ $lab->designation }})</small>
                    @endif
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">Email Address</small>
                    <a href="mailto:{{ $lab->email }}" class="fw-semibold text-primary">{{ $lab->email }}</a>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">Mobile Number</small>
                    <span class="fw-semibold text-dark">{{ $lab->mobile_number ?? 'N/A' }}</span>
                </div>

                <hr>

                <div class="mb-3">
                    <small class="text-muted d-block">NABL Certificate Number</small>
                    <span class="fw-semibold text-dark">{{ $lab->nabl_certificate_number ?? 'Not Provided' }}</span>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">Laboratory Type</small>
                    <span class="fw-semibold text-dark">{{ $lab->laboratory_type ?? 'Commercial' }}</span>
                </div>

                <div class="mb-3">
                    <small class="text-muted d-block">GST Number</small>
                    <span class="fw-semibold text-dark">{{ $lab->gst_number ?? 'N/A' }}</span>
                </div>

                <hr>

                <div>
                    <small class="text-muted d-block">Full Address</small>
                    <p class="mb-0 text-dark small">
                        {{ $lab->address ? $lab->address . ',' : '' }}<br>
                        {{ $lab->city ? $lab->city . ',' : '' }} {{ $lab->state ?? '' }} {{ $lab->pin_code ? '- ' . $lab->pin_code : '' }}<br>
                        {{ $lab->country ?? 'India' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Quick Summary Stats Card -->
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="fw-bold mb-0">Participation Summary</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">Total Programs Registered:</span>
                    <span class="fw-bold text-dark fs-6">{{ $totalRegisteredPrograms }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted small">Total Fees Paid:</span>
                    <span class="fw-bold text-success fs-6">₹{{ number_format($totalPaidAmount, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Registration History -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="fw-bold mb-0"><i class="bx bx-history me-1 text-primary"></i> PT Program Registration History</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Reg No</th>
                                <th>Program Code & Name</th>
                                <th>Reg Date</th>
                                <th>Reg Status</th>
                                <th>Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lab->registrations as $reg)
                                <tr>
                                    <td class="fw-semibold text-primary">{{ $reg->registration_number }}</td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $reg->program->program_name ?? 'N/A' }}</div>
                                        <small class="badge bg-light text-dark border">{{ $reg->program->program_code ?? 'N/A' }}</small>
                                    </td>
                                    <td class="small text-muted">{{ \Carbon\Carbon::parse($reg->registered_at)->format('d M Y') }}</td>
                                    <td>
                                        @if($reg->status === 'confirmed')
                                            <span class="badge badge-soft-success">Confirmed</span>
                                        @else
                                            <span class="badge badge-soft-warning">{{ ucfirst($reg->status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($reg->payment && $reg->payment->payment_status === 'success')
                                            <span class="badge badge-soft-success"><i class="bx bx-check me-1"></i> Paid (₹{{ number_format($reg->payment->final_amount, 2) }})</span>
                                        @else
                                            <span class="badge badge-soft-warning"><i class="bx bx-time me-1"></i> Pending</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">
                                        This laboratory has not registered for any PT programs yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
