@extends('layouts.app')

@section('title', 'Batch Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Production Batch: {{ $batch->batch_number }}</h4>
        <p class="text-muted small mb-0">Program: {{ $batch->program->program_code ?? 'N/A' }} — {{ $batch->program->program_name ?? '' }}</p>
    </div>
    <div class="d-flex gap-2">
        @if($batch->status !== 'approved')
            <form action="{{ route('admin.batches.approve', $batch->batch_id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-success btn-sm shadow-sm">
                    <i class="bx bx-check-double me-1"></i> Approve Batch
                </button>
            </form>
        @endif
        <a href="{{ route('admin.samples.program', $batch->program_id) }}" class="btn btn-primary btn-sm shadow-sm">
            <i class="bx bx-qr-scan me-1"></i> Assign Samples
        </a>
        <a href="{{ route('admin.batches.edit', $batch->batch_id) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-edit me-1"></i> Edit Batch
        </a>
        <a href="{{ route('admin.batches.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i> Back to List
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Batch Info Column -->
    <div class="col-lg-7">
        <div class="card mb-4">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">Batch Production Profile</h6>
                <div>
                    @if($batch->status === 'approved')
                        <span class="badge bg-success"><i class="bx bx-check-circle me-1"></i> Approved</span>
                    @elseif($batch->status === 'testing')
                        <span class="badge bg-warning text-dark"><i class="bx bx-test-tube me-1"></i> In Testing</span>
                    @elseif($batch->status === 'rejected')
                        <span class="badge bg-danger"><i class="bx bx-x-circle me-1"></i> Rejected</span>
                    @else
                        <span class="badge bg-secondary">In Preparation</span>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block fw-semibold">Batch Number</small>
                        <span class="fw-bold text-primary fs-5">{{ $batch->batch_number }}</span>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block fw-semibold">PT Material / Matrix</small>
                        <span class="fw-bold text-dark">{{ $batch->material }}</span>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block fw-semibold">Quantity Produced</small>
                        <span class="fw-semibold text-dark">{{ $batch->quantity }}</span>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block fw-semibold">Preparation Date</small>
                        <span class="fw-semibold text-dark">{{ $batch->preparation_date ? \Carbon\Carbon::parse($batch->preparation_date)->format('d M Y') : 'N/A' }}</span>
                    </div>
                    <div class="col-md-6 border-top pt-3">
                        <small class="text-muted d-block fw-semibold">Prepared By</small>
                        <span class="fw-semibold text-dark">{{ $batch->preparedByAdmin->full_name ?? 'N/A' }}</span>
                    </div>
                    <div class="col-md-6 border-top pt-3">
                        <small class="text-muted d-block fw-semibold">Verified By</small>
                        <span class="fw-semibold text-dark">{{ $batch->verifiedByAdmin->full_name ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assigned Samples Table -->
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0">Assigned Participant Samples ({{ $batch->samples->count() }})</h6>
                <a href="{{ route('admin.samples.program', $batch->program_id) }}" class="btn btn-sm btn-link">Manage Assignments</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Sample Code</th>
                                <th>Participant Laboratory</th>
                                <th>Reg Number</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($batch->samples as $sample)
                                <tr>
                                    <td class="fw-bold text-primary">{{ $sample->sample_code }}</td>
                                    <td class="fw-semibold text-dark">{{ $sample->registration->lab->laboratory_name ?? 'N/A' }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $sample->registration->registration_number ?? 'N/A' }}</span></td>
                                    <td>
                                        @if($sample->status === 'dispatched')
                                            <span class="badge badge-soft-info">Dispatched</span>
                                        @elseif($sample->status === 'received')
                                            <span class="badge badge-soft-success">Received</span>
                                        @else
                                            <span class="badge badge-soft-warning">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">No samples assigned to this batch yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Homogeneity & Stability Results -->
    <div class="col-lg-5">
        <!-- Homogeneity Card -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="fw-bold mb-0"><i class="bx bx-check-shield me-1 text-success"></i> Homogeneity Test Results</h6>
            </div>
            <div class="card-body">
                @forelse($batch->homogeneityTests as $hTest)
                    <div class="p-3 bg-light rounded border mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge badge-soft-success fs-6">{{ $hTest->result }}</span>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($hTest->test_date)->format('d M Y') }}</small>
                        </div>
                        <small class="text-muted d-block mb-1">Performed by: {{ $hTest->performedByAdmin->full_name ?? 'System Admin' }}</small>
                        <p class="mb-0 small text-dark">{{ $hTest->remarks ?? 'No remarks recorded.' }}</p>
                    </div>
                @empty
                    <div class="text-muted small">No homogeneity test log recorded yet.</div>
                @endforelse
            </div>
        </div>

        <!-- Stability Card -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="fw-bold mb-0"><i class="bx bx-time me-1 text-info"></i> Stability Test Results</h6>
            </div>
            <div class="card-body">
                @forelse($batch->stabilityTests as $sTest)
                    <div class="p-3 bg-light rounded border mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge badge-soft-info fs-6">{{ $sTest->result }}</span>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($sTest->test_date)->format('d M Y') }}</small>
                        </div>
                        <small class="text-muted d-block mb-1">Performed by: {{ $sTest->performedByAdmin->full_name ?? 'System Admin' }}</small>
                        <p class="mb-0 small text-dark">{{ $sTest->remarks ?? 'No remarks recorded.' }}</p>
                    </div>
                @empty
                    <div class="text-muted small">No stability test log recorded yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
