@extends('layouts.app')

@section('title', 'Observation Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Observation Record: OBS-{{ str_pad($observation->observation_id, 4, '0', STR_PAD_LEFT) }}</h4>
        <p class="text-muted small mb-0">Submitted by: {{ $observation->lab->laboratory_name ?? 'Participant Lab' }}</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editObservationModal">
            <i class="bx bx-edit me-1"></i> Edit Observation
        </button>
        <a href="{{ route('admin.observations.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i> Back to Registry
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Result Information -->
    <div class="col-lg-7">
        <div class="card mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="bx bx-test-tube me-1 text-primary"></i> Parameter Test Result</h6>
                <div>
                    @if($observation->is_locked)
                        <span class="badge bg-danger"><i class="bx bx-lock-alt me-1"></i> Locked</span>
                    @else
                        <span class="badge bg-success"><i class="bx bx-check me-1"></i> Submitted</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block fw-semibold">Sample Code</small>
                        <span class="fw-bold text-primary fs-5"><i class="bx bx-qr-scan me-1"></i> {{ $observation->sample->sample_code ?? 'N/A' }}</span>
                    </div>

                    <div class="col-md-6">
                        <small class="text-muted d-block fw-semibold">PT Program</small>
                        <span class="fw-semibold text-dark">{{ $observation->parameter->program->program_code ?? 'N/A' }}</span>
                    </div>

                    <div class="col-md-6 border-top pt-3">
                        <small class="text-muted d-block fw-semibold">Parameter Name</small>
                        <span class="fw-bold text-dark fs-6">{{ $observation->parameter->parameter_name ?? 'N/A' }}</span>
                    </div>

                    <div class="col-md-6 border-top pt-3">
                        <small class="text-muted d-block fw-semibold">Submitted Result Value</small>
                        <span class="fw-bold text-success fs-4">{{ $observation->result_value }}</span>
                        <span class="text-muted fw-semibold">{{ $observation->unit }}</span>
                    </div>

                    <div class="col-md-6 border-top pt-3">
                        <small class="text-muted d-block fw-semibold">Measurement Uncertainty (MU / ±)</small>
                        <span class="fw-bold text-dark fs-5">{{ $observation->uncertainty ?: 'N/A' }}</span>
                    </div>

                    <div class="col-md-6 border-top pt-3">
                        <small class="text-muted d-block fw-semibold">Test Method Used</small>
                        <span class="badge bg-light text-dark border fs-6">{{ $observation->test_method }}</span>
                    </div>

                    <div class="col-md-6 border-top pt-3">
                        <small class="text-muted d-block fw-semibold">Submission Date & Time</small>
                        <span class="fw-semibold text-dark">{{ \Carbon\Carbon::parse($observation->submitted_at)->format('d M Y, h:i:s A') }}</span>
                    </div>

                    <div class="col-12 border-top pt-3">
                        <small class="text-muted d-block fw-semibold mb-1">Laboratory Remarks & Technical Comments</small>
                        <div class="p-3 bg-light rounded border text-dark">
                            {{ $observation->remarks ?? 'No remarks provided by the laboratory.' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Participant Info & Files -->
    <div class="col-lg-5">
        <!-- Participant Lab Details Card -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="fw-bold mb-0"><i class="bx bx-building me-1 text-primary"></i> Participant Laboratory Profile</h6>
            </div>
            <div class="card-body">
                <h6 class="fw-bold text-dark mb-1">{{ $observation->lab->laboratory_name ?? 'N/A' }}</h6>
                <p class="text-muted small mb-2"><i class="bx bx-certification me-1"></i> NABL Cert: {{ $observation->lab->nabl_certificate_number ?? 'N/A' }}</p>
                <p class="text-muted small mb-2"><i class="bx bx-user me-1"></i> Contact: {{ $observation->lab->contact_person ?? 'N/A' }}</p>
                <p class="text-muted small mb-0"><i class="bx bx-envelope me-1"></i> {{ $observation->lab->email ?? 'N/A' }}</p>
            </div>
        </div>

        <!-- Attached Raw Data Files Card -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="fw-bold mb-0"><i class="bx bx-file me-1 text-info"></i> Attached Raw Data Files ({{ $observation->files->count() }})</h6>
            </div>
            <div class="card-body">
                @forelse($observation->files as $file)
                    <div class="d-flex justify-content-between align-items-center p-2 bg-light rounded border mb-2">
                        <div class="d-flex align-items-center">
                            <i class="bx bx-file-blank display-6 text-primary me-2"></i>
                            <div>
                                <small class="fw-bold text-dark d-block">{{ $file->original_filename }}</small>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($file->uploaded_at)->format('d M Y, h:i A') }}</small>
                            </div>
                        </div>
                        <a href="{{ asset($file->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="bx bx-download"></i> View
                        </a>
                    </div>
                @empty
                    <div class="text-muted small py-2">No raw data files attached to this observation.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Edit Observation Modal -->
<div class="modal fade" id="editObservationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.observations.update', $observation->observation_id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h6 class="modal-title fw-bold">Edit Observation (OBS-{{ str_pad($observation->observation_id, 4, '0', STR_PAD_LEFT) }})</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Result Value <span class="text-danger">*</span></label>
                            <input type="text" name="result_value" class="form-control" value="{{ old('result_value', $observation->result_value) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Unit <span class="text-danger">*</span></label>
                            <input type="text" name="unit" class="form-control" value="{{ old('unit', $observation->unit) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Uncertainty (MU / ±)</label>
                            <input type="text" name="uncertainty" class="form-control" value="{{ old('uncertainty', $observation->uncertainty) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Test Method <span class="text-danger">*</span></label>
                            <input type="text" name="test_method" class="form-control" value="{{ old('test_method', $observation->test_method) }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Lock Submission Status <span class="text-danger">*</span></label>
                            <select name="is_locked" class="form-select">
                                <option value="0" {{ old('is_locked', $observation->is_locked) == 0 ? 'selected' : '' }}>Unlocked (Editable)</option>
                                <option value="1" {{ old('is_locked', $observation->is_locked) == 1 ? 'selected' : '' }}>Locked (Finalized)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Laboratory Remarks</label>
                            <textarea name="remarks" class="form-control" rows="3">{{ old('remarks', $observation->remarks) }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
