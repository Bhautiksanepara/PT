@extends('layouts.app')

@section('title', 'Sample Traceability Master Sheet')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Sample Assignment & Traceability</h4>
        <p class="text-muted small mb-0">Trace auto-generated sample IDs (`PT-2026-001`), production batches, and participant labs</p>
    </div>
    <div>
        @if($programs->isNotEmpty())
            <div class="dropdown">
                <button class="btn btn-primary btn-sm dropdown-toggle fw-semibold shadow-sm" type="button" data-bs-toggle="dropdown">
                    <i class="bx bx-bolt me-1"></i> Assign Program Samples
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow">
                    <li class="dropdown-header text-uppercase small fw-bold">Select Program to Assign Samples</li>
                    @foreach($programs as $prog)
                        <li>
                            <a class="dropdown-item d-flex justify-content-between align-items-center" href="{{ route('admin.samples.program', $prog->program_id) }}">
                                <span><i class="bx bx-layer me-2 text-primary"></i>{{ $prog->program_code }}</span>
                                <small class="text-muted ms-2">{{ $prog->program_name }}</small>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>

<!-- Search & Filter Bar -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form action="{{ route('admin.samples.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bx bx-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search Sample ID, Lab Name, Reg No..." value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-md-3">
                <select name="program_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Filter PT Program --</option>
                    @foreach($programs as $prog)
                        <option value="{{ $prog->program_id }}" {{ request('program_id') == $prog->program_id ? 'selected' : '' }}>{{ $prog->program_code }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Filter Sample Status --</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Dispatch</option>
                    <option value="dispatched" {{ request('status') == 'dispatched' ? 'selected' : '' }}>Dispatched</option>
                    <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received by Lab</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-secondary btn-sm w-100"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                @if(request()->hasAny(['search', 'program_id', 'status']))
                    <a href="{{ route('admin.samples.index') }}" class="btn btn-light btn-sm"><i class="bx bx-reset"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Traceability Master Sheet Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Sample Code</th>
                        <th>Participant Laboratory</th>
                        <th>Reg Number</th>
                        <th>PT Program</th>
                        <th>Production Batch</th>
                        <th>Sample Status</th>
                        <th>Assigned At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($samples as $sample)
                        <tr>
                            <td class="fw-bold text-primary">
                                <i class="bx bx-qr-scan me-1"></i> {{ $sample->sample_code }}
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $sample->registration->lab->laboratory_name ?? 'N/A' }}</div>
                                <small class="text-muted"><i class="bx bx-envelope"></i> {{ $sample->registration->lab->email ?? '' }}</small>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $sample->registration->registration_number ?? 'N/A' }}</span></td>
                            <td>
                                <span class="fw-semibold text-dark">{{ $sample->program->program_code ?? 'N/A' }}</span>
                            </td>
                            <td>
                                @if($sample->batch)
                                    <span class="badge bg-primary text-white">{{ $sample->batch->batch_number }}</span>
                                @else
                                    <span class="text-muted small">Not Linked</span>
                                @endif
                            </td>
                            <td>
                                @if($sample->status === 'dispatched')
                                    <span class="badge badge-soft-info"><i class="bx bx-package me-1"></i> Dispatched</span>
                                @elseif($sample->status === 'received')
                                    <span class="badge badge-soft-success"><i class="bx bx-check-double me-1"></i> Received</span>
                                @else
                                    <span class="badge badge-soft-warning"><i class="bx bx-time me-1"></i> Pending Dispatch</span>
                                @endif
                            </td>
                            <td class="small text-muted">{{ \Carbon\Carbon::parse($sample->created_at)->format('d M Y, h:i A') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bx bx-qr-scan display-4 text-muted d-block mb-2"></i>
                                No sample assignments recorded yet. Go to <a href="{{ route('admin.programs.index') }}">PT Programs</a> to assign samples.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($samples->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $samples->links() }}
        </div>
    @endif
</div>
@endsection
