@extends('layouts.user')

@section('title', 'Observation Form - ' . $sample->sample_code)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bx bx-vial me-2 text-success"></i> Phase 6 – Observation Entry Form</h4>
            <p class="text-muted small mb-0">
                Sample Code: <span class="badge bg-primary font-monospace">{{ $sample->sample_code }}</span> | 
                Program: <strong class="text-dark">{{ $program->program_name }}</strong> ({{ $program->program_code }}) | 
                Reg #: <code>{{ $registration->registration_number }}</code>
            </p>
        </div>
        <div>
            <a href="{{ route('user.observations.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bx bx-arrow-back me-1"></i> Back to Observation List
            </a>
        </div>
    </div>

    @if($isPastDeadline)
        <div class="alert alert-danger shadow-sm mb-4">
            <i class="bx bx-lock-alt me-2 fs-5 align-middle"></i> 
            <strong>Submission Window Closed & Locked:</strong> The submission deadline for this scheme was 
            <strong>{{ $deadline ? $deadline->format('d M Y') : 'N/A' }}</strong>. Form entries are locked for ISO 13528 evaluation.
        </div>
    @else
        <div class="alert alert-info shadow-sm mb-4">
            <i class="bx bx-info-circle me-2 fs-5 align-middle"></i> 
            <strong>Flexible Editing Open:</strong> You can submit and update your test results anytime until the submission deadline on 
            <strong>{{ $deadline ? $deadline->format('d M Y') : 'N/A' }}</strong>.
        </div>
    @endif

    <form method="POST" action="{{ route('user.observations.store', $registration->registration_id) }}" enctype="multipart/form-data">
        @csrf

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-3">
                <h6 class="fw-bold mb-0 text-white"><i class="bx bx-spreadsheet me-2 text-success"></i> Test Results & Measurement Uncertainty Data Entry</h6>
                <span class="badge bg-primary font-monospace">{{ $registeredParams->count() }} Registered Parameter(s)</span>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 22%;">Test Parameter</th>
                                <th style="width: 20%;">Test Method *</th>
                                <th style="width: 18%;">Result Value *</th>
                                <th style="width: 12%;">Unit *</th>
                                <th style="width: 13%;">Uncertainty (MU / ±)</th>
                                <th style="width: 15%;">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($registeredParams as $index => $param)
                                @php
                                    $existing = $existingObservations->get($param->parameter_id);
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $param->parameter_name }}</div>
                                        <small class="text-muted d-block">Default Method: {{ $param->test_method }}</small>
                                        <input type="hidden" name="results[{{ $index }}][parameter_id]" value="{{ $param->parameter_id }}">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" name="results[{{ $index }}][test_method]" value="{{ old("results.{$index}.test_method", $existing->test_method ?? $param->test_method) }}" required {{ $isPastDeadline ? 'disabled' : '' }} placeholder="e.g. ISO 17025 / ASTM">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm fw-bold text-primary" name="results[{{ $index }}][result_value]" value="{{ old("results.{$index}.result_value", $existing->result_value ?? '') }}" required {{ $isPastDeadline ? 'disabled' : '' }} placeholder="Enter result value">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" name="results[{{ $index }}][unit]" value="{{ old("results.{$index}.unit", $existing->unit ?? $param->unit) }}" {{ $isPastDeadline ? 'disabled' : '' }} placeholder="Unit">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm font-monospace" name="results[{{ $index }}][uncertainty]" value="{{ old("results.{$index}.uncertainty", $existing->uncertainty ?? '') }}" {{ $isPastDeadline ? 'disabled' : '' }} placeholder="± 0.05">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" name="results[{{ $index }}][remarks]" value="{{ old("results.{$index}.remarks", $existing->remarks ?? '') }}" {{ $isPastDeadline ? 'disabled' : '' }} placeholder="Optional remarks">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No test parameters registered for this program.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Raw Data & Supporting Files Section -->
                <div class="bg-light p-3 rounded border mb-4">
                    <h6 class="fw-bold text-dark mb-2"><i class="bx bx-paperclip me-1 text-primary"></i> Upload Supporting Raw Data Sheets / Calibration Files</h6>
                    <p class="text-muted small mb-3">Upload your laboratory raw data sheets, equipment calibration slips, or test certificates (Accepted: PDF, XLS, XLSX, DOC, DOCX, Images. Max: 10MB).</p>
                    
                    <input type="file" class="form-control" name="attachment" {{ $isPastDeadline ? 'disabled' : '' }}>

                    @php
                        $attachedFiles = collect();
                        foreach ($existingObservations as $obs) {
                            if ($obs->files->count() > 0) {
                                $attachedFiles = $attachedFiles->merge($obs->files);
                            }
                        }
                        $attachedFiles = $attachedFiles->unique('file_id');
                    @endphp

                    @if($attachedFiles->count() > 0)
                        <div class="mt-3 border-top pt-2">
                            <small class="fw-bold text-dark d-block mb-2"><i class="bx bx-folder me-1 text-success"></i> Previously Uploaded Data Sheets:</small>
                            <ul class="list-group list-group-flush small">
                                @foreach($attachedFiles as $file)
                                    <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center py-1 px-0">
                                        <div>
                                            <i class="bx bx-file text-primary me-1"></i>
                                            <span class="fw-semibold text-dark">{{ $file->original_filename }}</span>
                                            <small class="text-muted ms-2">({{ \Carbon\Carbon::parse($file->uploaded_at)->format('d M Y H:i') }})</small>
                                        </div>
                                        <a href="{{ asset($file->file_path) }}" class="btn btn-sm btn-outline-primary py-0 px-2" target="_blank">
                                            <i class="bx bx-download me-1"></i> View / Download
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                @if(!$isPastDeadline)
                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-success btn-lg px-4 fw-bold shadow-sm">
                            <i class="bx bx-check-circle me-1"></i> Submit Test Observations
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </form>
</div>
@endsection
