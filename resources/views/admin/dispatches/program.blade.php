@extends('layouts.app')

@section('title', 'Program Sample Dispatches')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Dispatch Management: {{ $program->program_code }}</h4>
        <p class="text-muted small mb-0">{{ $program->program_name }}</p>
    </div>
    <a href="{{ route('admin.programs.show', $program->program_id) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bx bx-arrow-back me-1"></i> Back to Program
    </a>
</div>

<!-- Bulk Dispatch Box -->
<div class="row g-4 mb-4">
    <div class="col-lg-12">
        <div class="card border-start border-success border-4 shadow-sm">
            <div class="card-body">
                <form action="{{ route('admin.dispatches.bulk', $program->program_id) }}" method="POST" class="row g-3 align-items-center">
                    @csrf
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Courier Partner <span class="text-danger">*</span></label>
                        <select name="courier_name" class="form-select" required>
                            <option value="BlueDart Express">BlueDart Express</option>
                            <option value="DTDC Courier">DTDC Courier</option>
                            <option value="FedEx India">FedEx India</option>
                            <option value="India Post Speed Post">India Post Speed Post</option>
                            <option value="Delhivery">Delhivery</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Tracking Number Prefix <span class="text-danger">*</span></label>
                        <input type="text" name="tracking_prefix" class="form-control" value="BD-{{ date('Y') }}-00" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Dispatch Date <span class="text-danger">*</span></label>
                        <input type="date" name="dispatch_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="col-md-3 text-md-end">
                        <button type="submit" class="btn btn-success w-100 py-2 fw-semibold" onclick="return confirm('Bulk dispatch all pending samples and send email notifications to labs?')">
                            <i class="bx bx-paper-plane me-1"></i> Bulk Dispatch & Notify
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Samples Dispatch Table -->
<div class="card">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0">Assigned Samples & Dispatch Status</h6>
        <span class="badge bg-primary">{{ $samples->count() }} Total Samples</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Sample Code</th>
                        <th>Participant Laboratory</th>
                        <th>Status</th>
                        <th>Courier Partner</th>
                        <th>Tracking Number</th>
                        <th>Dispatched At</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($samples as $sample)
                        @php
                            $latestDispatch = $sample->dispatches->last();
                        @endphp
                        <tr>
                            <td class="fw-bold text-primary">
                                <i class="bx bx-qr-scan me-1"></i> {{ $sample->sample_code }}
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $sample->registration->lab->laboratory_name ?? 'N/A' }}</div>
                                <small class="text-muted"><i class="bx bx-envelope"></i> {{ $sample->registration->lab->email ?? '' }}</small>
                            </td>
                            <td>
                                @if($sample->status === 'dispatched')
                                    <span class="badge badge-soft-info"><i class="bx bx-package me-1"></i> Dispatched</span>
                                @elseif($sample->status === 'received')
                                    <span class="badge badge-soft-success"><i class="bx bx-check-double me-1"></i> Received</span>
                                @else
                                    <span class="badge badge-soft-warning"><i class="bx bx-time me-1"></i> Pending</span>
                                @endif
                            </td>
                            <td>
                                @if($latestDispatch)
                                    <span class="badge bg-light text-dark border">{{ $latestDispatch->courier_name }}</span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="font-monospace">
                                @if($latestDispatch)
                                    <code>{{ $latestDispatch->tracking_number }}</code>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="small text-muted">
                                {{ $latestDispatch ? \Carbon\Carbon::parse($latestDispatch->dispatch_date)->format('d M Y') : 'N/A' }}
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#dispatchModal{{ $sample->sample_id }}">
                                    <i class="bx bx-edit me-1"></i> {{ $latestDispatch ? 'Update Dispatch' : 'Dispatch Sample' }}
                                </button>

                                <!-- Modal -->
                                <div class="modal fade text-start" id="dispatchModal{{ $sample->sample_id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.dispatches.single') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="sample_id" value="{{ $sample->sample_id }}">

                                                <div class="modal-header">
                                                    <h6 class="modal-title fw-bold">Dispatch Sample: {{ $sample->sample_code }}</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Courier Partner <span class="text-danger">*</span></label>
                                                        <input type="text" name="courier_name" class="form-control" value="{{ $latestDispatch->courier_name ?? 'BlueDart Express' }}" required>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Tracking Number <span class="text-danger">*</span></label>
                                                        <input type="text" name="tracking_number" class="form-control" value="{{ $latestDispatch->tracking_number ?? 'BD' . rand(100000, 999999) }}" required>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Dispatch Date <span class="text-danger">*</span></label>
                                                        <input type="date" name="dispatch_date" class="form-control" value="{{ $latestDispatch->dispatch_date ?? date('Y-m-d') }}" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Save & Send Email</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5">No assigned samples found for this program. Go to <a href="{{ route('admin.samples.program', $program->program_id) }}">Sample Assignment</a> first.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
