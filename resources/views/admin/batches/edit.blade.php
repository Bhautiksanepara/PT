@extends('layouts.app')

@section('title', 'Edit Sample Batch')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Edit Sample Batch: {{ $batch->batch_number }}</h4>
        <p class="text-muted small mb-0">Program: {{ $program->program_code }} — {{ $program->program_name }}</p>
    </div>
    <a href="{{ route('admin.batches.show', $batch->batch_id) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bx bx-arrow-back me-1"></i> Back to Batch Details
    </a>
</div>

<form action="{{ route('admin.batches.update', $batch->batch_id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <!-- Left Column: Batch Information -->
        <div class="col-lg-7">
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h6 class="fw-bold mb-0"><i class="bx bx-box me-1 text-primary"></i> 1. Batch Production Details</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Batch Number <span class="text-danger">*</span></label>
                            <input type="text" name="batch_number" class="form-control @error('batch_number') is-invalid @enderror" value="{{ old('batch_number', $batch->batch_number) }}" required>
                            @error('batch_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Batch Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select">
                                <option value="in_preparation" {{ old('status', $batch->status) == 'in_preparation' ? 'selected' : '' }}>In Preparation</option>
                                <option value="testing" {{ old('status', $batch->status) == 'testing' ? 'selected' : '' }}>In Testing (Homogeneity/Stability)</option>
                                <option value="approved" {{ old('status', $batch->status) == 'approved' ? 'selected' : '' }}>Approved for Assignment</option>
                                <option value="rejected" {{ old('status', $batch->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Material / Matrix <span class="text-danger">*</span></label>
                            <input type="text" name="material" class="form-control" value="{{ old('material', $batch->material) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Total Quantity Produced <span class="text-danger">*</span></label>
                            <input type="text" name="quantity" class="form-control" value="{{ old('quantity', $batch->quantity) }}" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Preparation Date</label>
                            <input type="date" name="preparation_date" class="form-control" value="{{ old('preparation_date', $batch->preparation_date) }}">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Prepared By</label>
                            <select name="prepared_by" class="form-select">
                                <option value="">-- Select Staff --</option>
                                @foreach($adminUsers as $user)
                                    <option value="{{ $user->admin_id }}" {{ old('prepared_by', $batch->prepared_by) == $user->admin_id ? 'selected' : '' }}>{{ $user->full_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Verified By</label>
                            <select name="verified_by" class="form-select">
                                <option value="">-- Select Staff --</option>
                                @foreach($adminUsers as $user)
                                    <option value="{{ $user->admin_id }}" {{ old('verified_by', $batch->verified_by) == $user->admin_id ? 'selected' : '' }}>{{ $user->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reference Test Replicates -->
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-primary"><i class="bx bx-chart me-1"></i> Reference Test Replicates (ISO 13528 Baseline)</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addTestRunBtn">
                        <i class="bx bx-plus me-1"></i> Add Test Run
                    </button>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Analyze the batch material multiple times and record your reference analysis results here. 
                        These values will be averaged to establish the target assigned value for Z-score calculation.
                    </p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle" id="referenceTestsTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 120px;">Test Run</th>
                                    @foreach($program->parameters as $param)
                                        <th>{{ $param->parameter_name }} @if($param->unit) ({{ $param->unit }}) @endif <span class="text-danger">*</span></th>
                                    @endforeach
                                    <th style="width: 80px;" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="referenceTestsBody">
                                @forelse($groupedReferenceValues ?? [] as $replicateNum => $values)
                                    <tr class="test-run-row" data-run-index="{{ $replicateNum }}">
                                        <td class="fw-bold text-muted ps-2">Run #{{ $replicateNum }}</td>
                                        @foreach($program->parameters as $param)
                                            @php
                                                $valRecord = $values->where('parameter_id', $param->parameter_id)->first();
                                                $val = $valRecord ? floatval($valRecord->reference_value) : '';
                                            @endphp
                                            <td>
                                                <input type="number" step="any" name="reference_tests[{{ $replicateNum }}][{{ $param->parameter_id }}]" class="form-control form-control-sm" placeholder="Value" value="{{ $val }}" required>
                                            </td>
                                        @endforeach
                                        <td class="text-center">
                                            @if($replicateNum == 1)
                                                <span class="text-muted small">-</span>
                                            @else
                                                <button type="button" class="btn btn-outline-danger btn-sm p-1 py-0 remove-run-btn"><i class="bx bx-trash"></i></button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="test-run-row" data-run-index="1">
                                        <td class="fw-bold text-muted ps-2">Run #1</td>
                                        @foreach($program->parameters as $param)
                                            <td>
                                                <input type="number" step="any" name="reference_tests[1][{{ $param->parameter_id }}]" class="form-control form-control-sm" placeholder="Value" required>
                                            </td>
                                        @endforeach
                                        <td class="text-center">
                                            <span class="text-muted small">-</span>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
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
                        <input type="date" name="homogeneity_date" class="form-control" value="{{ old('homogeneity_date', $latestHomogeneity->test_date ?? date('Y-m-d')) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Result Status</label>
                        <select name="homogeneity_result" class="form-select">
                            <option value="Pass / Homogeneous" {{ old('homogeneity_result', $latestHomogeneity->result ?? '') == 'Pass / Homogeneous' ? 'selected' : '' }}>Pass / Homogeneous (ISO 13528 compliant)</option>
                            <option value="In Progress" {{ old('homogeneity_result', $latestHomogeneity->result ?? '') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="Fail / Non-Homogeneous" {{ old('homogeneity_result', $latestHomogeneity->result ?? '') == 'Fail / Non-Homogeneous' ? 'selected' : '' }}>Fail / Non-Homogeneous</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-semibold">Remarks & Statistics</label>
                        <textarea name="homogeneity_remarks" class="form-control" rows="2">{{ old('homogeneity_remarks', $latestHomogeneity->remarks ?? '') }}</textarea>
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
                        <input type="date" name="stability_date" class="form-control" value="{{ old('stability_date', $latestStability->test_date ?? date('Y-m-d')) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Result Status</label>
                        <select name="stability_result" class="form-select">
                            <option value="Pass / Stable" {{ old('stability_result', $latestStability->result ?? '') == 'Pass / Stable' ? 'selected' : '' }}>Pass / Stable (under 2-8°C storage)</option>
                            <option value="In Progress" {{ old('stability_result', $latestStability->result ?? '') == 'In Progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="Fail / Unstable" {{ old('stability_result', $latestStability->result ?? '') == 'Fail / Unstable' ? 'selected' : '' }}>Fail / Unstable</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-semibold">Remarks & Conditions</label>
                        <textarea name="stability_remarks" class="form-control" rows="2">{{ old('stability_remarks', $latestStability->remarks ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="card">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="bx bx-check-circle me-1"></i> Update Production Batch
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var addBtn = document.getElementById('addTestRunBtn');
    var body = document.getElementById('referenceTestsBody');
    var parameters = [
        @foreach($program->parameters as $param)
            { id: {{ $param->parameter_id }}, name: "{{ $param->parameter_name }}" },
        @endforeach
    ];

    // Bind remove event to any pre-loaded rows
    body.querySelectorAll('.remove-run-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            btn.closest('tr').remove();
            reindexRows();
        });
    });

    if (addBtn && body) {
        addBtn.addEventListener('click', function () {
            var rows = body.querySelectorAll('.test-run-row');
            var nextIndex = 1;
            
            if (rows.length > 0) {
                var lastRow = rows[rows.length - 1];
                nextIndex = parseInt(lastRow.getAttribute('data-run-index')) + 1;
            }

            var tr = document.createElement('tr');
            tr.className = 'test-run-row';
            tr.setAttribute('data-run-index', nextIndex);

            var tdRun = document.createElement('td');
            tdRun.className = 'fw-bold text-muted ps-2';
            tdRun.innerText = 'Run #' + nextIndex;
            tr.appendChild(tdRun);

            parameters.forEach(function (param) {
                var td = document.createElement('td');
                var input = document.createElement('input');
                input.type = 'number';
                input.step = 'any';
                input.name = 'reference_tests[' + nextIndex + '][' + param.id + ']';
                input.className = 'form-control form-control-sm';
                input.placeholder = 'Value';
                input.required = true;
                td.appendChild(input);
                tr.appendChild(td);
            });

            var tdAction = document.createElement('td');
            tdAction.className = 'text-center';
            var removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'btn btn-outline-danger btn-sm p-1 py-0';
            removeBtn.innerHTML = '<i class="bx bx-trash"></i>';
            removeBtn.addEventListener('click', function () {
                tr.remove();
                reindexRows();
            });
            tdAction.appendChild(removeBtn);
            tr.appendChild(tdAction);

            body.appendChild(tr);
        });
    }

    function reindexRows() {
        var rows = body.querySelectorAll('.test-run-row');
        rows.forEach(function (row, idx) {
            var newIndex = idx + 1;
            row.setAttribute('data-run-index', newIndex);
            row.querySelector('td:first-child').innerText = 'Run #' + newIndex;

            var inputs = row.querySelectorAll('input');
            inputs.forEach(function (input, paramIdx) {
                var paramId = parameters[paramIdx].id;
                input.name = 'reference_tests[' + newIndex + '][' + paramId + ']';
            });
        });
    }
});
</script>
@endpush
@endsection
