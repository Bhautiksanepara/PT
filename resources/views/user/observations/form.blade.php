@extends('layouts.user')

@section('title', 'Observation Form - ' . $sample->sample_code)

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <a href="{{ route('lab.observations.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill mb-2">
            <i class="bx bx-left-arrow-alt me-1"></i> Back to Observation List
        </a>
        <h3 class="fw-bold text-dark mb-1">Phase 6 – Observation Entry Form</h3>
        <p class="text-muted small">Sample Code: <strong>{{ $sample->sample_code }}</strong> | Program: <strong>{{ $program->program_name }}</strong></p>
    </div>

    @if($isPastDeadline)
        <div class="alert alert-danger mb-4">
            <i class="bx bx-lock-alt me-2 fs-5 align-middle"></i> <strong>Submission Deadline Passed:</strong> The submission window for this program closed on {{ \Carbon\Carbon::parse($program->submission_deadline)->format('d M Y') }}. This form is locked.
        </div>
    @endif

    <form method="POST" action="{{ route('lab.observations.store', $sample->sample_id) }}" enctype="multipart/form-data">
        @csrf

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0"><i class="bx bx-vial me-2"></i>Test Results Entry</h5>
                <span class="badge bg-white text-success">Registration #: {{ $sample->registration->registration_number }}</span>
            </div>
            <div class="card-body">
                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 25%;">Test Parameter</th>
                                <th style="width: 25%;">Test Method *</th>
                                <th style="width: 20%;">Result Value *</th>
                                <th style="width: 15%;">Unit *</th>
                                <th style="width: 15%;">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($program->parameters as $index => $param)
                                @php
                                    $existing = $existingObservations->get($param->parameter_id);
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $param->parameter_name }}</div>
                                        <input type="hidden" name="results[{{ $index }}][parameter_id]" value="{{ $param->parameter_id }}">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" name="results[{{ $index }}][test_method]" value="{{ old("results.{$index}.test_method", $existing->test_method ?? $param->test_method) }}" required {{ $isPastDeadline ? 'disabled' : '' }} placeholder="e.g. ISO 17025 / ASTM">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm fw-bold" name="results[{{ $index }}][result_value]" value="{{ old("results.{$index}.result_value", $existing->result_value ?? '') }}" required {{ $isPastDeadline ? 'disabled' : '' }} placeholder="Enter result value">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" name="results[{{ $index }}][unit]" value="{{ old("results.{$index}.unit", $existing->unit ?? $param->unit) }}" {{ $isPastDeadline ? 'disabled' : '' }} placeholder="Unit">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" name="results[{{ $index }}][remarks]" value="{{ old("results.{$index}.remarks", $existing->remarks ?? '') }}" {{ $isPastDeadline ? 'disabled' : '' }} placeholder="Optional remarks">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No test parameters registered for this program.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- File Attachments Upload (Phase 6 Specification) -->
                <div class="card bg-light border-0 p-3 mb-3">
                    <h6 class="fw-bold text-dark mb-2"><i class="bx bx-paperclip me-1 text-primary"></i>Upload Supporting Files / Raw Data Sheets</h6>
                    <p class="text-muted small mb-2">Upload raw data sheets, calibration certificates, or test reports (Accepted: PDF, XLS, XLSX, DOC, DOCX, Images. Max: 10MB).</p>
                    
                    <input type="file" class="form-control" name="attachment" {{ $isPastDeadline ? 'disabled' : '' }}>
                </div>

                @if(!$isPastDeadline)
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="submit" class="btn btn-success btn-lg px-4 fw-semibold">
                            <i class="bx bx-send me-1"></i> Submit Observations & Lock Form
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </form>
</div>
@endsection
