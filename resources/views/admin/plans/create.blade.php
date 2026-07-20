@extends('layouts.app')

@section('title', 'Generate PT Plan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Generate Official PT Plan</h4>
        <p class="text-muted small mb-0">Program: {{ $program->program_code }} — {{ $program->program_name }}</p>
    </div>
    <a href="{{ route('admin.programs.show', $program->program_id) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bx bx-arrow-back me-1"></i> Back to Program
    </a>
</div>

<form action="{{ route('admin.plans.store', $program->program_id) }}" method="POST">
    @csrf

    <div class="row g-4">
        <!-- Main Form Column -->
        <div class="col-lg-8">
            <!-- Program Header Summary -->
            <div class="card mb-4 border-start border-primary border-4">
                <div class="card-body">
                    <div class="row g-2 small">
                        <div class="col-md-4">
                            <span class="text-muted d-block">Program Code</span>
                            <span class="fw-bold text-primary fs-6">{{ $program->program_code }}</span>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted d-block">Discipline</span>
                            <span class="fw-semibold text-dark">{{ $program->discipline ?? 'General' }}</span>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted d-block">Parameters Count</span>
                            <span class="badge bg-info text-dark">{{ $program->parameters->count() }} Test Parameters</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PT Plan Fields Card -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="fw-bold mb-0"><i class="bx bx-task me-1 text-primary"></i> PT Plan Details</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Program Number / Plan ID <span class="text-danger">*</span></label>
                            <input type="text" name="program_number" class="form-control @error('program_number') is-invalid @enderror" value="{{ old('program_number', $plan->program_number ?? 'PT-PLAN-' . $program->program_code) }}" placeholder="e.g. PT-PLAN-2026-001" required>
                            @error('program_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">PT Material / Matrix <span class="text-danger">*</span></label>
                            <input type="text" name="material" class="form-control @error('material') is-invalid @enderror" value="{{ old('material', $plan->material ?? 'Drinking Water / Synthetic Matrix') }}" placeholder="e.g. Drinking Water Matrix" required>
                            @error('material')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Sample Quantity per Lab</label>
                            <input type="text" name="sample_quantity" class="form-control" value="{{ old('sample_quantity', $plan->sample_quantity ?? '2 Bottles (500 ml each)') }}" placeholder="e.g. 500 ml polyethylene bottle">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Timeline Overview</label>
                            <input type="text" name="timeline" class="form-control" value="{{ old('timeline', $plan->timeline ?? ($program->registration_start_date ? \Carbon\Carbon::parse($program->registration_start_date)->format('M Y') . ' - ' . \Carbon\Carbon::parse($program->report_date)->format('M Y') : 'Q1 2026')) }}" placeholder="e.g. Jan 2026 - Mar 2026">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Assigned Program Coordinator</label>
                            <select name="assigned_coordinator" class="form-select">
                                <option value="">-- Select Technical / Quality Coordinator --</option>
                                @foreach($coordinators as $coordinator)
                                    <option value="{{ $coordinator->admin_id }}" {{ old('assigned_coordinator', $plan->assigned_coordinator ?? '') == $coordinator->admin_id ? 'selected' : '' }}>
                                        {{ $coordinator->full_name }} ({{ $coordinator->email }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Selected coordinator will appear on the official PT Plan document signature block.</small>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Sample Preparation & Handling Instructions</label>
                            <textarea name="sample_preparation_instructions" class="form-control" rows="5" placeholder="Specify storage temperature (e.g. 2-8°C), preservation instructions, homogenization protocol, and dispatch safety checks...">{{ old('sample_preparation_instructions', $plan->sample_preparation_instructions ?? "1. Store samples at 2-8°C prior to dispatch.\n2. Verify homogeneity & stability per ISO 13528 guidelines.\n3. Include instruction sheet and safety data sheet (SDS) in the dispatch box.") }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Parameters Preview & Actions -->
        <div class="col-lg-4">
            <!-- Included Parameters -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="fw-bold mb-0"><i class="bx bx-list-check me-1 text-success"></i> Program Test Parameters</h6>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush small">
                        @forelse($program->parameters as $param)
                            <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <div>
                                    <strong class="text-dark">{{ $param->parameter_name }}</strong>
                                    <small class="text-muted d-block">{{ $param->test_method ?? 'Standard Method' }}</small>
                                </div>
                                <span class="badge bg-light text-dark border">{{ $param->unit ?? '-' }}</span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted text-center py-3">No parameters defined yet.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <!-- Submit Card -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="bx bx-save me-1"></i> Save & Generate Official PT Plan
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
