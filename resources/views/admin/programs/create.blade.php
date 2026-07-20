@extends('layouts.app')

@section('title', 'Create PT Program')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Create New PT Program</h4>
        <p class="text-muted small mb-0">Define program details, schedule timelines, and test parameters</p>
    </div>
    <a href="{{ route('admin.programs.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bx bx-arrow-back me-1"></i> Back to List
    </a>
</div>

<form action="{{ route('admin.programs.store') }}" method="POST" id="program-form">
    @csrf

    <div class="row g-4">
        <!-- Main Form Column -->
        <div class="col-lg-8">
            <!-- Program Details Card -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="fw-bold mb-0"><i class="bx bx-info-circle me-1 text-primary"></i> 1. Basic Program Information</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Program Code <span class="text-danger">*</span></label>
                            <input type="text" name="program_code" class="form-control @error('program_code') is-invalid @enderror" value="{{ old('program_code') }}" placeholder="e.g. PT-CHEM-2026-01" required>
                            @error('program_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Discipline <span class="text-danger">*</span></label>
                            <input type="text" name="discipline" class="form-control @error('discipline') is-invalid @enderror" value="{{ old('discipline') }}" placeholder="e.g. Chemical / Biological / Mechanical" required>
                            @error('discipline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Program Name <span class="text-danger">*</span></label>
                            <input type="text" name="program_name" class="form-control @error('program_name') is-invalid @enderror" value="{{ old('program_name') }}" placeholder="e.g. Chemical Analysis in Drinking Water Samples" required>
                            @error('program_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Scheme Code</label>
                            <input type="text" name="scheme_code" class="form-control" value="{{ old('scheme_code') }}" placeholder="e.g. SCH-CHEM-01">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Program Fee (₹) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" name="program_fee" class="form-control @error('program_fee') is-invalid @enderror" value="{{ old('program_fee', '0.00') }}" required>
                            </div>
                            @error('program_fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Program Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Provide detailed scope and objectives of the PT program...">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Parameters Card (Dynamic Rows) -->
            <div class="card mb-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="bx bx-list-plus me-1 text-success"></i> 2. Test Parameters <span class="text-danger">*</span></h6>
                    <button type="button" class="btn btn-success btn-sm" id="add-parameter-btn">
                        <i class="bx bx-plus me-1"></i> Add Parameter
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0" id="parameters-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 35%;">Parameter Name <span class="text-danger">*</span></th>
                                    <th style="width: 35%;">Test Method</th>
                                    <th style="width: 20%;">Unit</th>
                                    <th style="width: 10%; text-align: center;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="parameters-tbody">
                                <!-- Dynamic rows inserted via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Status & Timeline Dates -->
        <div class="col-lg-4">
            <!-- Status Card -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="fw-bold mb-0"><i class="bx bx-cog me-1 text-primary"></i> Program Status Controls</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Registration Status</label>
                        <select name="registration_status" class="form-select">
                            <option value="upcoming" {{ old('registration_status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                            <option value="active" {{ old('registration_status', 'active') == 'active' ? 'selected' : '' }}>Active (Open for Reg)</option>
                            <option value="closed" {{ old('registration_status') == 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Program Status</label>
                        <select name="program_status" class="form-select">
                            <option value="draft" {{ old('program_status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="open" {{ old('program_status', 'open') == 'open' ? 'selected' : '' }}>Open (In Progress)</option>
                            <option value="closed" {{ old('program_status') == 'closed' ? 'selected' : '' }}>Closed</option>
                            <option value="completed" {{ old('program_status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Timeline Dates Card -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="fw-bold mb-0"><i class="bx bx-calendar me-1 text-warning"></i> Key Timeline Dates</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Registration Start Date</label>
                        <input type="date" name="registration_start_date" class="form-control" value="{{ old('registration_start_date') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Registration End Date</label>
                        <input type="date" name="registration_end_date" class="form-control" value="{{ old('registration_end_date') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Sample Dispatch Date</label>
                        <input type="date" name="dispatch_date" class="form-control" value="{{ old('dispatch_date') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Submission Deadline</label>
                        <input type="date" name="submission_deadline" class="form-control" value="{{ old('submission_deadline') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Report Release Date</label>
                        <input type="date" name="report_date" class="form-control" value="{{ old('report_date') }}">
                    </div>
                </div>
            </div>

            <!-- Submit Button Card -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="bx bx-save me-1"></i> Save PT Program
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        let paramIndex = 0;
        const tbody = document.getElementById("parameters-tbody");
        const addBtn = document.getElementById("add-parameter-btn");

        function addParameterRow(name = '', method = '', unit = '') {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>
                    <input type="text" name="parameters[${paramIndex}][parameter_name]" class="form-control form-control-sm" placeholder="e.g. pH Value" value="${name}" required>
                </td>
                <td>
                    <input type="text" name="parameters[${paramIndex}][test_method]" class="form-control form-control-sm" placeholder="e.g. IS 3025 (Part 11)" value="${method}">
                </td>
                <td>
                    <input type="text" name="parameters[${paramIndex}][unit]" class="form-control form-control-sm" placeholder="e.g. pH Units" value="${unit}">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-row-btn"><i class="bx bx-trash"></i></button>
                </td>
            `;
            tbody.appendChild(tr);

            tr.querySelector(".remove-row-btn").addEventListener("click", function() {
                if (tbody.children.length > 1) {
                    tr.remove();
                } else {
                    alert("At least one test parameter is required.");
                }
            });

            paramIndex++;
        }

        // Add initial row
        addParameterRow();

        addBtn.addEventListener("click", function() {
            addParameterRow();
        });
    });
</script>
@endpush
