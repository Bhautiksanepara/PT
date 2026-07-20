@extends('layouts.app')

@section('title', 'Create Sample Batch')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Create Sample Production Batch</h4>
        <p class="text-muted small mb-0">Program: {{ $program->program_code }} — {{ $program->program_name }}</p>
    </div>
    <a href="{{ route('admin.programs.show', $program->program_id) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bx bx-arrow-back me-1"></i> Back to Program
    </a>
</div>

<form action="{{ route('admin.batches.store') }}" method="POST">
    @csrf
    <input type="hidden" name="program_id" value="{{ $program->program_id }}">

    <div class="row g-4">
        <!-- Left Column: Batch Information -->
        <div class="col-lg-7">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="fw-bold mb-0"><i class="bx bx-box me-1 text-primary"></i> 1. Batch Production Details</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @if(isset($programs) && $programs->count() > 1)
                            <div class="col-12">
                                <label class="form-label small fw-semibold">Target PT Program <span class="text-danger">*</span></label>
                                <select name="program_id" class="form-select" onchange="window.location.href='{{ route('admin.batches.create_direct') }}?program_id=' + this.value">
                                    @foreach($programs as $prog)
                                        <option value="{{ $prog->program_id }}" {{ $program->program_id == $prog->program_id ? 'selected' : '' }}>
                                            {{ $prog->program_code }} — {{ $prog->program_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Batch Number <span class="text-danger">*</span></label>
                            <input type="text" name="batch_number" class="form-control @error('batch_number') is-invalid @enderror" value="{{ old('batch_number', $defaultBatchNumber) }}" required>
                            @error('batch_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Batch Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select">
                                <option value="in_preparation" {{ old('status') == 'in_preparation' ? 'selected' : '' }}>In Preparation</option>
                                <option value="testing" {{ old('status', 'testing') == 'testing' ? 'selected' : '' }}>In Testing (Homogeneity/Stability)</option>
                                <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>Approved for Assignment</option>
                                <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Material / Matrix <span class="text-danger">*</span></label>
                            <input type="text" name="material" class="form-control" value="{{ old('material', $program->plan->material ?? 'Drinking Water Matrix') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Total Quantity Produced <span class="text-danger">*</span></label>
                            <input type="text" name="quantity" class="form-control" value="{{ old('quantity', '50 Bottles (500 ml each)') }}" placeholder="e.g. 50 Bottles" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Preparation Date</label>
                            <input type="date" name="preparation_date" class="form-control" value="{{ old('preparation_date', date('Y-m-d')) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Prepared By</label>
                            <select name="prepared_by" class="form-select">
                                <option value="">-- Select Staff --</option>
                                @foreach($adminUsers as $user)
                                    <option value="{{ $user->admin_id }}" {{ old('prepared_by') == $user->admin_id ? 'selected' : '' }}>{{ $user->full_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Verified By</label>
                            <select name="verified_by" class="form-select">
                                <option value="">-- Select Staff --</option>
                                @foreach($adminUsers as $user)
                                    <option value="{{ $user->admin_id }}" {{ old('verified_by') == $user->admin_id ? 'selected' : '' }}>{{ $user->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Homogeneity & Stability Testing -->
        <div class="col-lg-5">
            <!-- Homogeneity Testing Card -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="fw-bold mb-0"><i class="bx bx-check-shield me-1 text-success"></i> 2. Homogeneity Testing</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Test Date</label>
                        <input type="date" name="homogeneity_date" class="form-control" value="{{ old('homogeneity_date', date('Y-m-d')) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Result Status</label>
                        <select name="homogeneity_result" class="form-select">
                            <option value="Pass / Homogeneous" {{ old('homogeneity_result', 'Pass / Homogeneous') == 'Pass / Homogeneous' ? 'selected' : '' }}>Pass / Homogeneous (ISO 13528 compliant)</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Fail / Non-Homogeneous">Fail / Non-Homogeneous</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-semibold">Remarks & Statistics</label>
                        <textarea name="homogeneity_remarks" class="form-control" rows="2" placeholder="e.g. Calculated s_sample <= 0.3 * sigma_pt per ISO 13528 Annex B.">{{ old('homogeneity_remarks', 'Calculated sample standard deviation meets ISO 13528 homogeneity criterion.') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Stability Testing Card -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="fw-bold mb-0"><i class="bx bx-time me-1 text-info"></i> 3. Stability Testing</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Test Date</label>
                        <input type="date" name="stability_date" class="form-control" value="{{ old('stability_date', date('Y-m-d')) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Result Status</label>
                        <select name="stability_result" class="form-select">
                            <option value="Pass / Stable" {{ old('stability_result', 'Pass / Stable') == 'Pass / Stable' ? 'selected' : '' }}>Pass / Stable (under 2-8°C storage)</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Fail / Unstable">Fail / Unstable</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-semibold">Remarks & Conditions</label>
                        <textarea name="stability_remarks" class="form-control" rows="2" placeholder="e.g. No significant degradation observed over 60 days.">{{ old('stability_remarks', 'Stable under recommended storage temperature of 2-8°C.') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="bx bx-save me-1"></i> Save Production Batch
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
