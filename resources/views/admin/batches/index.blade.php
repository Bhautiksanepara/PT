@extends('layouts.app')

@section('title', 'Sample Production Batches')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Sample Production Planning</h4>
        <p class="text-muted small mb-0">Track sample batch preparation, homogeneity testing, and stability testing</p>
    </div>
    <div>
        <a href="{{ route('admin.batches.create_direct') }}" class="btn btn-primary shadow-sm btn-sm px-3">
            <i class="bx bx-plus me-1"></i> Create Production Batch
        </a>
    </div>
</div>

<!-- Filters Card -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form action="{{ route('admin.batches.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bx bx-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search Batch Number, Material..." value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Filter Batch Status --</option>
                    <option value="in_preparation" {{ request('status') == 'in_preparation' ? 'selected' : '' }}>In Preparation</option>
                    <option value="testing" {{ request('status') == 'testing' ? 'selected' : '' }}>Testing</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>

            <div class="col-md-3">
                <select name="program_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Filter PT Program --</option>
                    @foreach($programs as $prog)
                        <option value="{{ $prog->program_id }}" {{ request('program_id') == $prog->program_id ? 'selected' : '' }}>{{ $prog->program_code }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-secondary btn-sm w-100"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                @if(request()->hasAny(['search', 'status', 'program_id']))
                    <a href="{{ route('admin.batches.index') }}" class="btn btn-light btn-sm"><i class="bx bx-reset"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Batches Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Batch Number</th>
                        <th>PT Program</th>
                        <th>Material</th>
                        <th>Quantity</th>
                        <th>Prepared By</th>
                        <th>Status</th>
                        <th>Samples Assigned</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batches as $batch)
                        <tr>
                            <td class="fw-bold text-primary">{{ $batch->batch_number }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $batch->program->program_code ?? 'N/A' }}</div>
                                <small class="text-muted">{{ Str::limit($batch->program->program_name ?? '', 30) }}</small>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $batch->material }}</span></td>
                            <td>{{ $batch->quantity }}</td>
                            <td>
                                @if($batch->preparedByAdmin)
                                    <div class="fw-medium text-dark">{{ $batch->preparedByAdmin->full_name }}</div>
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($batch->status === 'approved')
                                    <span class="badge badge-soft-success"><i class="bx bx-check-circle me-1"></i> Approved</span>
                                @elseif($batch->status === 'testing')
                                    <span class="badge badge-soft-warning"><i class="bx bx-test-tube me-1"></i> In Testing</span>
                                @elseif($batch->status === 'rejected')
                                    <span class="badge badge-soft-danger"><i class="bx bx-x-circle me-1"></i> Rejected</span>
                                @else
                                    <span class="badge badge-soft-secondary"><i class="bx bx-time me-1"></i> In Prep</span>
                                @endif
                            </td>
                            <td><span class="badge bg-info text-dark rounded-pill">{{ $batch->samples_count }} Samples</span></td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.batches.show', $batch->batch_id) }}" class="btn btn-outline-primary btn-sm me-1">
                                    <i class="bx bx-show me-1"></i> View
                                </a>
                                <a href="{{ route('admin.batches.edit', $batch->batch_id) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bx bx-edit me-1"></i> Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bx bx-box display-4 text-muted d-block mb-2"></i>
                                No sample production batches found. Go to <a href="{{ route('admin.programs.index') }}">PT Programs</a> to prepare a batch.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($batches->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $batches->links() }}
        </div>
    @endif
</div>
@endsection
