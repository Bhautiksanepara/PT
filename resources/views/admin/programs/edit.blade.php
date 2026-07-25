@extends('layouts.app')

@section('title', 'Edit PT Program')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Edit PT Program: {{ $program->program_code }}</h4>
        <p class="text-muted small mb-0">Update program parameters, fee, timelines, and registration status</p>
    </div>
    <a href="{{ route('admin.programs.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bx bx-arrow-back me-1"></i> Back to List
    </a>
</div>

<form action="{{ route('admin.programs.update', $program->program_id) }}" method="POST" id="program-form">
    @csrf
    @method('PUT')

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
                            <label class="form-label small fw-semibold">Discipline <span class="text-danger">*</span></label>
                            <select name="discipline_id" id="discipline-select" class="form-select @error('discipline_id') is-invalid @enderror" required>
                                <option value="">-- Select Discipline --</option>
                                @foreach($disciplines as $disc)
                                    <option value="{{ $disc->id }}" data-code="{{ $disc->short_code }}" {{ old('discipline_id', $program->discipline_id) == $disc->id ? 'selected' : '' }}>{{ $disc->discipline_name }}</option>
                                @endforeach
                                <option value="other">Other (Manual Entry)</option>
                            </select>
                            @error('discipline_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Program Code <span class="text-danger">*</span></label>
                            <input type="text" name="program_code" id="program-code-input" class="form-control @error('program_code') is-invalid @enderror" value="{{ old('program_code', $program->program_code) }}" placeholder="Auto-generated on selection" required>
                            @error('program_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 d-none" id="custom-discipline-container">
                            <label class="form-label small fw-semibold">Custom Discipline Name <span class="text-danger">*</span></label>
                            <input type="text" name="custom_discipline" id="custom-discipline-input" class="form-control @error('custom_discipline') is-invalid @enderror" placeholder="e.g. Environmental Testing">
                            @error('custom_discipline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6 d-none" id="custom-short-code-container">
                            <label class="form-label small fw-semibold">Short Code (Max 10 chars) <span class="text-danger">*</span></label>
                            <input type="text" name="custom_short_code" id="custom-short-code-input" class="form-control @error('custom_short_code') is-invalid @enderror" placeholder="e.g. ENV" maxlength="10">
                            @error('custom_short_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Program Name <span class="text-danger">*</span></label>
                            <input type="text" name="program_name" class="form-control @error('program_name') is-invalid @enderror" value="{{ old('program_name', $program->program_name) }}" required>
                            @error('program_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Scheme Code</label>
                            <input type="text" name="scheme_code" class="form-control" value="{{ old('scheme_code', $program->scheme_code) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Program Fee (₹) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" step="0.01" name="program_fee" class="form-control @error('program_fee') is-invalid @enderror" value="{{ old('program_fee', $program->program_fee) }}" required>
                            </div>
                            @error('program_fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold">Program Description</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description', $program->description) }}</textarea>
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
                                <!-- Dynamic rows populated via JS -->
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
                        <label class="form-label small fw-semibold">Program Status</label>
                        <select name="program_status" class="form-select">
                            <option value="" {{ old('program_status', $program->program_status) === null ? 'selected' : '' }}>-- Automatic (Date-Based) --</option>
                            <option value="draft" {{ old('program_status', $program->program_status) == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="reopen" {{ old('program_status', $program->program_status) == 'reopen' ? 'selected' : '' }}>Reopen</option>
                            <option value="forcefully_closed" {{ old('program_status', $program->program_status) == 'forcefully_closed' ? 'selected' : '' }}>Forcefully Closed</option>
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
                        <input type="date" name="registration_start_date" class="form-control" value="{{ old('registration_start_date', $program->registration_start_date) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Registration End Date</label>
                        <input type="date" name="registration_end_date" class="form-control" value="{{ old('registration_end_date', $program->registration_end_date) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Sample Dispatch Date</label>
                        <input type="date" name="dispatch_date" class="form-control" value="{{ old('dispatch_date', $program->dispatch_date) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Submission Deadline</label>
                        <input type="date" name="submission_deadline" class="form-control" value="{{ old('submission_deadline', $program->submission_deadline) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Report Release Date</label>
                        <input type="date" name="report_date" class="form-control" value="{{ old('report_date', $program->report_date) }}">
                    </div>
                </div>
            </div>

            <!-- Submit Button Card -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="bx bx-check-circle me-1"></i> Update PT Program
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
        const disciplines = @json($disciplines);
        const existingParams = @json($program->parameters);
        let paramIndex = 0;

        const tbody = document.getElementById("parameters-tbody");
        const addBtn = document.getElementById("add-parameter-btn");
        const disciplineSelect = document.getElementById("discipline-select");
        const programCodeInput = document.getElementById("program-code-input");
        const startDateInput = document.querySelector('input[name="registration_start_date"]');

        const customDiscContainer = document.getElementById("custom-discipline-container");
        const customShortContainer = document.getElementById("custom-short-code-container");
        const customDiscInput = document.getElementById("custom-discipline-input");
        const customShortInput = document.getElementById("custom-short-code-input");

        // Initialize toggle for Discipline
        if (disciplineSelect.value === 'other') {
            customDiscContainer.classList.remove("d-none");
            customShortContainer.classList.remove("d-none");
            customDiscInput.setAttribute("required", "required");
            customShortInput.setAttribute("required", "required");
        }

        // 1. Handle Discipline Toggle & Custom Fields
        disciplineSelect.addEventListener("change", function() {
            const val = this.value;
            if (val === 'other') {
                customDiscContainer.classList.remove("d-none");
                customShortContainer.classList.remove("d-none");
                customDiscInput.setAttribute("required", "required");
                customShortInput.setAttribute("required", "required");
            } else {
                customDiscContainer.classList.add("d-none");
                customShortContainer.classList.add("d-none");
                customDiscInput.removeAttribute("required");
                customShortInput.removeAttribute("required");
            }
            updateProgramCode();
            refreshAllParameterDropdowns();
        });

        customShortInput.addEventListener("input", updateProgramCode);
        startDateInput.addEventListener("change", updateProgramCode);

        // 2. Dynamic Program Code Generation via Fetch
        function updateProgramCode() {
            const disciplineId = disciplineSelect.value;
            let year = new Date().getFullYear();
            if (startDateInput.value) {
                year = new Date(startDateInput.value).getFullYear();
            }

            if (!disciplineId) {
                programCodeInput.value = '';
                return;
            }

            let url = `{{ route('admin.programs.next-code') }}?discipline_id=${disciplineId}&year=${year}`;
            if (disciplineId === 'other') {
                const customCode = customShortInput.value || 'TEMP';
                url += `&custom_short_code=${encodeURIComponent(customCode)}`;
            }

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (data.next_code) {
                        programCodeInput.value = data.next_code;
                    }
                })
                .catch(err => console.error("Error generating code:", err));
        }

        // 3. Dynamic Parameter Rows
        function addParameterRow(name = '', method = '', unit = '') {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>
                    <select class="form-select form-select-sm parameter-select" required>
                        <option value="">-- Select Parameter --</option>
                        <option value="other">Other (Manual Entry)</option>
                    </select>
                    <input type="text" name="parameters[${paramIndex}][parameter_name]" class="form-control form-control-sm mt-1 d-none custom-parameter-name" placeholder="e.g. pH Value" value="${name}">
                </td>
                <td>
                    <input type="text" name="parameters[${paramIndex}][test_method]" class="form-control form-control-sm test-method-input" placeholder="e.g. IS 3025 (Part 11)" value="${method}">
                </td>
                <td>
                    <input type="text" name="parameters[${paramIndex}][unit]" class="form-control form-control-sm unit-input" placeholder="e.g. pH Units" value="${unit}">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm remove-row-btn"><i class="bx bx-trash"></i></button>
                </td>
            `;
            tbody.appendChild(tr);

            const select = tr.querySelector(".parameter-select");
            const customInput = tr.querySelector(".custom-parameter-name");
            const methodInput = tr.querySelector(".test-method-input");
            const unitInput = tr.querySelector(".unit-input");

            // Populate current discipline options
            populateParameterSelect(select);

            // Bind values
            if (name) {
                // Check if exists in options
                let optionExists = false;
                for (let i = 0; i < select.options.length; i++) {
                    if (select.options[i].value === name) {
                        optionExists = true;
                        break;
                    }
                }

                if (optionExists) {
                    select.value = name;
                    customInput.value = name;
                } else {
                    select.value = 'other';
                    customInput.value = name;
                    customInput.classList.remove('d-none');
                    customInput.setAttribute('required', 'required');
                }
            }

            select.addEventListener("change", function() {
                const val = this.value;
                if (val === 'other') {
                    customInput.classList.remove("d-none");
                    customInput.setAttribute("required", "required");
                    customInput.value = '';
                    methodInput.value = '';
                    unitInput.value = '';
                } else if (val) {
                    customInput.classList.add("d-none");
                    customInput.removeAttribute("required");
                    customInput.value = val;

                    // Autofill method and unit
                    const discId = disciplineSelect.value;
                    const selectedDisc = disciplines.find(d => d.id == discId);
                    if (selectedDisc) {
                        const paramObj = selectedDisc.parameters.find(p => p.parameter_name === val);
                        if (paramObj) {
                            methodInput.value = paramObj.test_method || '';
                            unitInput.value = paramObj.unit || '';
                        }
                    }
                } else {
                    customInput.classList.add("d-none");
                    customInput.removeAttribute("required");
                    customInput.value = '';
                    methodInput.value = '';
                    unitInput.value = '';
                }
            });

            tr.querySelector(".remove-row-btn").addEventListener("click", function() {
                if (tbody.children.length > 1) {
                    tr.remove();
                } else {
                    alert("At least one test parameter is required.");
                }
            });

            paramIndex++;
        }

        function populateParameterSelect(selectEl) {
            const discId = disciplineSelect.value;
            // Clear current options except first and last
            const options = Array.from(selectEl.options);
            options.slice(1, -1).forEach(opt => opt.remove());

            if (discId && discId !== 'other') {
                const selectedDisc = disciplines.find(d => d.id == discId);
                if (selectedDisc && selectedDisc.parameters) {
                    selectedDisc.parameters.forEach(p => {
                        const opt = document.createElement("option");
                        opt.value = p.parameter_name;
                        opt.textContent = p.parameter_name;
                        selectEl.insertBefore(opt, selectEl.options[selectEl.options.length - 1]);
                    });
                }
            }
        }

        function refreshAllParameterDropdowns() {
            const selects = tbody.querySelectorAll(".parameter-select");
            selects.forEach(select => {
                const customInput = select.parentElement.querySelector(".custom-parameter-name");
                const methodInput = select.parentElement.parentElement.querySelector(".test-method-input");
                const unitInput = select.parentElement.parentElement.querySelector(".unit-input");
                
                select.value = "";
                customInput.classList.add("d-none");
                customInput.removeAttribute("required");
                customInput.value = "";
                methodInput.value = "";
                unitInput.value = "";

                populateParameterSelect(select);
            });
        }

        // Add existing parameters or a blank row
        if (existingParams && existingParams.length > 0) {
            existingParams.forEach(p => {
                addParameterRow(p.parameter_name || '', p.test_method || '', p.unit || '');
            });
        } else {
            addParameterRow();
        }

        addBtn.addEventListener("click", function() {
            addParameterRow();
        });
    });
</script>
@endpush
