@extends('layouts.app')

@section('title', 'Program Sample Assignment')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Sample Assignment: {{ $program->program_code }}</h4>
        <p class="text-muted small mb-0">{{ $program->program_name }}</p>
    </div>
    <a href="{{ route('admin.programs.show', $program->program_id) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bx bx-arrow-back me-1"></i> Back to Program
    </a>
</div>

<div class="row g-4 mb-4">
    <!-- Bulk Auto-Assign Card -->
    <div class="col-lg-12">
        <div class="card border-start border-primary border-4 shadow-sm">
            <div class="card-body">
                <form action="{{ route('admin.samples.bulk-assign', $program->program_id) }}" method="POST" class="row g-3 align-items-center">
                    @csrf
                    <div class="col-md-5">
                        <label class="form-label small fw-semibold">Select Approved Production Batch <span class="text-danger">*</span></label>
                        <select name="batch_id" class="form-select" required>
                            <option value="">-- Choose Approved Sample Batch --</option>
                            @foreach($approvedBatches as $batch)
                                <option value="{{ $batch->batch_id }}">{{ $batch->batch_number }} (Material: {{ $batch->material }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <small class="text-muted d-block">Auto-Assignment Pattern:</small>
                        <span class="badge bg-light text-dark border font-monospace fs-6">PT-{{ date('Y') }}-001, PT-{{ date('Y') }}-002...</span>
                    </div>

                    <div class="col-md-3 text-md-end">
                        @if($approvedBatches->isEmpty())
                            <div class="alert alert-warning py-1 px-2 small mb-0">
                                <i class="bx bx-info-circle"></i> Create & approve a production batch first.
                            </div>
                        @else
                            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold" onclick="return confirm('Auto-generate and assign sample codes for all confirmed registrations?')">
                                <i class="bx bx-bolt me-1"></i> Bulk Auto-Assign Samples
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Participant Registration Sample Assignment Table -->
<div class="card">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0">Confirmed Registrations & Sample Codes</h6>
        <span class="badge bg-primary">{{ $program->registrations->count() }} Registrations</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Reg Number</th>
                        <th>Participant Laboratory</th>
                        <th>Assigned Sample Code</th>
                        <th>Batch Number</th>
                        <th>Status</th>
                        <th class="text-end">Manual Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($program->registrations as $reg)
                        @php
                            $sample = $assignedSamples->get($reg->registration_id);
                        @endphp
                        <tr>
                            <td class="fw-semibold text-primary">{{ $reg->registration_number }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $reg->lab->laboratory_name ?? 'N/A' }}</div>
                                <small class="text-muted"><i class="bx bx-map-pin"></i> {{ $reg->lab->city ?? '' }}, {{ $reg->lab->state ?? '' }}</small>
                            </td>
                            <td>
                                @if($sample)
                                    <span class="fw-bold text-success fs-6"><i class="bx bx-qr-scan me-1"></i> {{ $sample->sample_code }}</span>
                                @else
                                    <span class="badge badge-soft-warning">Unassigned</span>
                                @endif
                            </td>
                            <td>
                                @if($sample && $sample->batch)
                                    <span class="badge bg-light text-dark border">{{ $sample->batch->batch_number }}</span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                @if($sample)
                                    @if($sample->status === 'dispatched')
                                        <span class="badge badge-soft-info">Dispatched</span>
                                    @elseif($sample->status === 'received')
                                        <span class="badge badge-soft-success">Received</span>
                                    @else
                                        <span class="badge badge-soft-warning">Pending Dispatch</span>
                                    @endif
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <!-- Single Manual Assign Modal Button -->
                                <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#assignModal{{ $reg->registration_id }}">
                                    <i class="bx bx-edit me-1"></i> {{ $sample ? 'Re-assign' : 'Assign Sample' }}
                                </button>

                                <!-- Modal -->
                                <div class="modal fade text-start" id="assignModal{{ $reg->registration_id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('admin.samples.assign-single') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="registration_id" value="{{ $reg->registration_id }}">

                                                <div class="modal-header">
                                                    <h6 class="modal-title fw-bold">Assign Sample to {{ $reg->lab->laboratory_name ?? 'Lab' }}</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Sample Code <span class="text-danger">*</span></label>
                                                        <input type="text" name="sample_code" class="form-control" value="{{ $sample->sample_code ?? 'PT-' . date('Y') . '-' . str_pad($loop->iteration, 3, '0', STR_PAD_LEFT) }}" required>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label small fw-semibold">Production Batch <span class="text-danger">*</span></label>
                                                        <select name="batch_id" class="form-select" required>
                                                            @foreach($allBatches as $b)
                                                                <option value="{{ $b->batch_id }}" {{ ($sample->batch_id ?? '') == $b->batch_id ? 'selected' : '' }}>
                                                                    {{ $b->batch_number }} ({{ ucfirst($b->status) }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Save Assignment</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">No registrations found for this program.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
