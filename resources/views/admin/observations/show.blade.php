@extends('layouts.app')

@section('title', 'Observation Registry — Lab Results')

@section('content')

{{-- Page Header --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">
            <i class="bx bx-test-tube me-2 text-primary"></i>
            Observation Registry
        </h4>
        <p class="text-muted small mb-0">
            <i class="bx bx-building me-1"></i>{{ $observation->lab->laboratory_name ?? 'Participant Lab' }}
            &nbsp;&bull;&nbsp;
            <i class="bx bx-barcode me-1"></i>{{ $observation->parameter->program->program_code ?? 'N/A' }}
            &nbsp;&bull;&nbsp;
            Sample: <strong>{{ $observation->sample->sample_code ?? 'N/A' }}</strong>
        </p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editAllModal">
            <i class="bx bx-edit me-1"></i> Edit All Parameters
        </button>
        <a href="{{ route('admin.observations.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i> Back to Registry
        </a>
    </div>
</div>

{{-- Success / Error Alerts --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show py-2 mb-3" role="alert">
        <i class="bx bx-check-circle me-1"></i>{{ session('success') }}
        <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show py-2 mb-3" role="alert">
        <i class="bx bx-error-circle me-1"></i>{{ $errors->first() }}
        <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    {{-- ====== LEFT: All Parameters Results Card ====== --}}
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #1e3a5f 0%, #2d6a9f 100%);">
                <h6 class="fw-bold mb-0 text-white">
                    <i class="bx bx-list-check me-2"></i>All Parameters & Submitted Results
                </h6>
                <span class="badge bg-white text-primary fw-semibold">
                    {{ $observation->registration->program->parameters->count() }} Parameter(s)
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                        <thead style="background: #f0f4f8; border-bottom: 2px solid #dee2e6;">
                            <tr>
                                <th class="ps-3 py-3 text-uppercase text-muted" style="font-size:0.7rem; letter-spacing:.06em; width:16%;">Parameter</th>
                                <th class="py-3 text-uppercase text-muted" style="font-size:0.7rem; letter-spacing:.06em; width:17%;">Method</th>
                                <th class="py-3 text-center text-uppercase text-muted" style="font-size:0.7rem; letter-spacing:.06em; width:12%;">Result Value</th>
                                <th class="py-3 text-center text-uppercase text-muted" style="font-size:0.7rem; letter-spacing:.06em; width:8%;">Unit</th>
                                <th class="py-3 text-center text-uppercase text-muted" style="font-size:0.7rem; letter-spacing:.06em; width:11%;">Uncertainty</th>
                                <th class="py-3 text-center text-uppercase text-muted" style="font-size:0.7rem; letter-spacing:.06em; width:9%;">Status</th>
                                <th class="py-3 text-center text-uppercase text-muted" style="font-size:0.7rem; letter-spacing:.06em; width:9%;">Sub. Date</th>
                                <th class="py-3 text-uppercase text-muted" style="font-size:0.7rem; letter-spacing:.06em;">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($observation->registration->program->parameters as $param)
                                @php
                                    $obs = $allObservations->get($param->parameter_id);
                                    $isCurrent = $obs && $obs->observation_id == $observation->observation_id;
                                @endphp
                                <tr class="{{ $isCurrent ? 'table-primary' : '' }}" style="{{ $isCurrent ? 'border-left: 3px solid #0d6efd;' : '' }}">
                                    <td class="ps-3 fw-semibold {{ $isCurrent ? 'text-primary' : 'text-dark' }}">
                                        {{ $param->parameter_name }}
                                        @if($isCurrent)
                                            <span class="badge bg-primary ms-1" style="font-size:0.65rem;">Current</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">{{ $obs?->test_method ?: '—' }}</td>
                                    <td class="text-center">
                                        @if($obs && $obs->result_value !== null && $obs->result_value !== '')
                                            <span class="fw-bold fs-6 text-success">{{ $obs->result_value }}</span>
                                        @else
                                            <span class="badge bg-warning text-dark" style="font-size:0.7rem;">Not Submitted</span>
                                        @endif
                                    </td>
                                    <td class="text-center text-muted small">{{ $obs?->unit ?: '—' }}</td>
                                    <td class="text-center text-muted small">{{ $obs?->uncertainty ?: '—' }}</td>
                                    <td class="text-center">
                                        @if($obs)
                                            @if($obs->is_locked)
                                                <span class="badge bg-danger"><i class="bx bx-lock-alt me-1"></i>Locked</span>
                                            @else
                                                <span class="badge bg-success"><i class="bx bx-check me-1"></i>Submitted</span>
                                            @endif
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center small text-muted">
                                        @if($obs && $obs->submitted_at)
                                            {{ \Carbon\Carbon::parse($obs->submitted_at)->format('d M Y') }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="small">
                                        @if($obs && $obs->remarks)
                                            <span class="text-dark">{{ $obs->remarks }}</span>
                                        @else
                                            <span class="text-muted fst-italic">No remarks</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-light text-end py-2">
                <button type="button" class="btn btn-primary btn-sm px-4" data-bs-toggle="modal" data-bs-target="#editAllModal">
                    <i class="bx bx-edit me-1"></i> Edit All Parameters
                </button>
            </div>
        </div>

        {{-- All Remarks per Parameter --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="bx bx-comment-detail me-1 text-secondary"></i> Laboratory Remarks — All Parameters</h6>
            </div>
            <div class="card-body p-0">
                @php $hasAnyRemark = false; @endphp
                @foreach($observation->registration->program->parameters as $param)
                    @php
                        $obs = $allObservations->get($param->parameter_id);
                        if ($obs && $obs->remarks) { $hasAnyRemark = true; }
                    @endphp
                    @if($obs && $obs->remarks)
                        <div class="px-3 py-2 border-bottom {{ ($obs->observation_id == $observation->observation_id) ? 'bg-primary bg-opacity-10' : '' }}">
                            <div class="d-flex align-items-start gap-2">
                                <span class="badge bg-secondary mt-1" style="font-size:0.65rem; white-space:nowrap;">{{ $param->parameter_name }}</span>
                                <p class="mb-0 text-dark small">{{ $obs->remarks }}</p>
                            </div>
                        </div>
                    @endif
                @endforeach
                @if(!$hasAnyRemark)
                    <div class="px-3 py-3 text-muted small fst-italic">
                        <i class="bx bx-info-circle me-1"></i>No remarks provided for any parameter.
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ====== RIGHT: Sidebar Info ====== --}}
    <div class="col-lg-4">
        {{-- Lab Profile --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light">
                <h6 class="fw-bold mb-0"><i class="bx bx-building me-1 text-primary"></i> Participant Laboratory</h6>
            </div>
            <div class="card-body">
                <h6 class="fw-bold text-dark mb-1">{{ $observation->lab->laboratory_name ?? 'N/A' }}</h6>
                <p class="text-muted small mb-2"><i class="bx bx-certification me-1 text-info"></i> NABL: {{ $observation->lab->nabl_certificate_number ?? 'N/A' }}</p>
                <p class="text-muted small mb-2"><i class="bx bx-user me-1"></i> {{ $observation->lab->contact_person ?? 'N/A' }}</p>
                <p class="text-muted small mb-0"><i class="bx bx-envelope me-1"></i> {{ $observation->lab->email ?? 'N/A' }}</p>
            </div>
        </div>

        {{-- Program Info --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light">
                <h6 class="fw-bold mb-0"><i class="bx bx-folder-open me-1 text-warning"></i> PT Program</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Code</span>
                    <span class="fw-semibold small">{{ $observation->parameter->program->program_code ?? 'N/A' }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted small">Sample</span>
                    <span class="fw-semibold small">{{ $observation->sample->sample_code ?? 'N/A' }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted small">Parameters</span>
                    <span class="badge bg-primary">{{ $observation->registration->program->parameters->count() }}</span>
                </div>
            </div>
        </div>

        {{-- Attached Files --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="bx bx-paperclip me-1 text-info"></i> Attached Files</h6>
                <span class="badge bg-secondary">{{ $observation->files->count() }}</span>
            </div>
            <div class="card-body p-2">
                @forelse($observation->files as $file)
                    <div class="d-flex justify-content-between align-items-center p-2 rounded border mb-2 bg-light">
                        <div class="d-flex align-items-center">
                            <i class="bx bx-file-blank fs-4 text-primary me-2"></i>
                            <div>
                                <small class="fw-bold text-dark d-block" style="max-width:140px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $file->original_filename }}</small>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($file->uploaded_at)->format('d M Y') }}</small>
                            </div>
                        </div>
                        <a href="{{ asset($file->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2">
                            <i class="bx bx-download"></i>
                        </a>
                    </div>
                @empty
                    <p class="text-muted small mb-0 p-2">No files attached.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>


{{-- ======================================================== --}}
{{-- EDIT ALL PARAMETERS MODAL                                --}}
{{-- ======================================================== --}}
<div class="modal fade" id="editAllModal" tabindex="-1" aria-labelledby="editAllModalLabel">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('admin.observations.bulkUpdate') }}" method="POST" id="editAllForm">
                @csrf
                <input type="hidden" name="redirect_to" value="{{ $observation->observation_id }}">

                <div class="modal-header" style="background: linear-gradient(135deg, #1e3a5f 0%, #2d6a9f 100%);">
                    <h5 class="modal-title text-white fw-bold" id="editAllModalLabel">
                        <i class="bx bx-edit me-2"></i>Edit All Parameters
                        <small class="text-white-50 fw-normal fs-6 ms-2">— {{ $observation->lab->laboratory_name ?? '' }}</small>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-0">
                    {{-- Instructions banner --}}
                    <div class="alert alert-info alert-dismissible rounded-0 border-0 mb-0 py-2 px-3" style="font-size:0.85rem;">
                        <i class="bx bx-info-circle me-1"></i>
                        Edit existing values or <strong>fill in blank rows</strong> to add observations on behalf of the laboratory.
                        Rows marked <span class="badge bg-warning text-dark" style="font-size:0.7rem;">Admin Entry</span> are new records — leave them blank to skip.
                        Click <strong>Save All Changes</strong> when done.
                        <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
                    </div>

                    {{-- Parameters edit table --}}
                    <div class="table-responsive">
                        <table class="table table-bordered align-top mb-0" style="font-size:0.85rem;">
                            <thead style="background:#f0f4f8; position:sticky; top:0; z-index:1;">
                                <tr>
                                    <th class="ps-3 py-3 text-uppercase text-muted" style="font-size:0.68rem; letter-spacing:.05em; width:16%;">Parameter</th>
                                    <th class="py-3 text-uppercase text-muted" style="font-size:0.68rem; letter-spacing:.05em; width:18%;">Test Method <span class="text-danger">*</span></th>
                                    <th class="py-3 text-uppercase text-muted" style="font-size:0.68rem; letter-spacing:.05em; width:13%;">Result Value <span class="text-danger">*</span></th>
                                    <th class="py-3 text-uppercase text-muted" style="font-size:0.68rem; letter-spacing:.05em; width:10%;">Unit <span class="text-danger">*</span></th>
                                    <th class="py-3 text-uppercase text-muted" style="font-size:0.68rem; letter-spacing:.05em; width:12%;">Uncertainty</th>
                                    <th class="py-3 text-uppercase text-muted" style="font-size:0.68rem; letter-spacing:.05em; width:10%;">Status</th>
                                    <th class="py-3 text-uppercase text-muted" style="font-size:0.68rem; letter-spacing:.05em;">Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($observation->registration->program->parameters as $param)
                                    @php
                                        $obs = $allObservations->get($param->parameter_id);
                                    @endphp
                                    <tr>
                                        <td class="ps-3 align-middle fw-semibold text-dark" style="vertical-align:middle;">
                                            {{ $param->parameter_name }}
                                            @if(!$obs)
                                                <br><span class="badge bg-warning text-dark mt-1" style="font-size:0.65rem;">Not Submitted</span>
                                            @endif
                                        </td>

                                        @if($obs)
                                            <td>
                                                <input type="text"
                                                    name="observations[{{ $obs->observation_id }}][test_method]"
                                                    class="form-control form-control-sm"
                                                    value="{{ old("observations.{$obs->observation_id}.test_method", $obs->test_method) }}"
                                                    placeholder="e.g. IS 3025 (part 11):2022"
                                                    required>
                                            </td>
                                            <td>
                                                <input type="text"
                                                    name="observations[{{ $obs->observation_id }}][result_value]"
                                                    class="form-control form-control-sm fw-bold text-success"
                                                    value="{{ old("observations.{$obs->observation_id}.result_value", $obs->result_value) }}"
                                                    placeholder="e.g. 7.4"
                                                    required>
                                            </td>
                                            <td>
                                                <input type="text"
                                                    name="observations[{{ $obs->observation_id }}][unit]"
                                                    class="form-control form-control-sm"
                                                    value="{{ old("observations.{$obs->observation_id}.unit", $obs->unit) }}"
                                                    placeholder="e.g. %"
                                                    required>
                                            </td>
                                            <td>
                                                <input type="text"
                                                    name="observations[{{ $obs->observation_id }}][uncertainty]"
                                                    class="form-control form-control-sm"
                                                    value="{{ old("observations.{$obs->observation_id}.uncertainty", $obs->uncertainty) }}"
                                                    placeholder="e.g. ±0.1">
                                            </td>
                                            <td>
                                                <select name="observations[{{ $obs->observation_id }}][is_locked]" class="form-select form-select-sm">
                                                    <option value="0" {{ !$obs->is_locked ? 'selected' : '' }}>Unlocked</option>
                                                    <option value="1" {{ $obs->is_locked  ? 'selected' : '' }}>Locked</option>
                                                </select>
                                            </td>
                                            <td>
                                                <textarea
                                                    name="observations[{{ $obs->observation_id }}][remarks]"
                                                    class="form-control form-control-sm"
                                                    rows="2"
                                                    placeholder="Optional remarks">{{ old("observations.{$obs->observation_id}.remarks", $obs->remarks) }}</textarea>
                                            </td>
                                        @else
                                            {{-- Parameter not submitted — Admin can enter values on behalf of lab --}}
                                            {{-- Use new[parameter_id] key to signal controller to create a new record --}}
                                            <td class="ps-2">
                                                <div class="d-flex align-items-center gap-1 mb-1">
                                                    <span class="badge bg-warning text-dark" style="font-size:0.62rem;">Admin Entry</span>
                                                    <small class="text-muted fst-italic" style="font-size:0.72rem;">Not submitted by lab</small>
                                                </div>
                                                <input type="hidden" name="new[{{ $param->parameter_id }}][registration_id]" value="{{ $observation->registration_id }}">
                                                <input type="hidden" name="new[{{ $param->parameter_id }}][lab_id]"          value="{{ $observation->lab_id }}">
                                                <input type="hidden" name="new[{{ $param->parameter_id }}][sample_id]"       value="{{ $observation->sample_id }}">
                                                <input type="hidden" name="new[{{ $param->parameter_id }}][parameter_id]"   value="{{ $param->parameter_id }}">
                                                <input type="text"
                                                    name="new[{{ $param->parameter_id }}][test_method]"
                                                    class="form-control form-control-sm"
                                                    placeholder="Test Method (e.g. IS 3025)">
                                            </td>
                                            <td>
                                                <input type="text"
                                                    name="new[{{ $param->parameter_id }}][result_value]"
                                                    class="form-control form-control-sm fw-bold"
                                                    style="color:#198754;"
                                                    placeholder="Result Value">
                                            </td>
                                            <td>
                                                <input type="text"
                                                    name="new[{{ $param->parameter_id }}][unit]"
                                                    class="form-control form-control-sm"
                                                    placeholder="Unit">
                                            </td>
                                            <td>
                                                <input type="text"
                                                    name="new[{{ $param->parameter_id }}][uncertainty]"
                                                    class="form-control form-control-sm"
                                                    placeholder="e.g. ±0.1">
                                            </td>
                                            <td>
                                                <select name="new[{{ $param->parameter_id }}][is_locked]" class="form-select form-select-sm">
                                                    <option value="0">Unlocked</option>
                                                    <option value="1">Locked</option>
                                                </select>
                                            </td>
                                            <td>
                                                <textarea
                                                    name="new[{{ $param->parameter_id }}][remarks]"
                                                    class="form-control form-control-sm"
                                                    rows="2"
                                                    placeholder="Optional remarks"></textarea>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer" style="background:#f8f9fa;">
                    <span class="text-muted small me-auto"><i class="bx bx-info-circle me-1"></i>Fields marked <span class="text-danger">*</span> are required.</span>
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">
                        <i class="bx bx-x me-1"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bx bx-save me-1"></i>Save All Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
