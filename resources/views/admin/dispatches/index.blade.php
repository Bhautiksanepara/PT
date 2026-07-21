@extends('layouts.app')

@section('title', 'Dispatch Management Master Sheet')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Dispatch Management — Master Sheet</h4>
        <p class="text-muted small mb-0">Track courier dispatches, tracking numbers, and notification status</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary btn-sm shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#quickDispatchProgramModal">
            <i class="bx bx-package me-1"></i> + New Dispatch by Program
        </button>
        <a href="{{ route('admin.test-email') }}" class="btn btn-outline-primary btn-sm">
            <i class="bx bx-mail-send me-1"></i> Send Test Email
        </a>
    </div>
</div>

<!-- Quick Program Dispatch Modal -->
<div class="modal fade" id="quickDispatchProgramModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="bx bx-package text-primary me-1"></i> Select PT Program to Dispatch</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Choose an active PT program below to manage sample assignments, single dispatch, or bulk dispatch for registered labs.</p>
                <div class="list-group">
                    @forelse($programs as $prog)
                        <a href="{{ route('admin.dispatches.program', $prog->program_id) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-bold text-primary">{{ $prog->program_code }}</span> — {{ $prog->program_name }}
                            </div>
                            <i class="bx bx-chevron-right text-muted"></i>
                        </a>
                    @empty
                        <div class="text-center py-3 text-muted">No programs available.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search & Filters Card -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form action="{{ route('admin.dispatches.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bx bx-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search Courier, Tracking #, Sample Code, Lab..." value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-md-4">
                <select name="program_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Filter PT Program --</option>
                    @foreach($programs as $prog)
                        <option value="{{ $prog->program_id }}" {{ request('program_id') == $prog->program_id ? 'selected' : '' }}>{{ $prog->program_code }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4 d-flex gap-1">
                <button type="submit" class="btn btn-secondary btn-sm w-100"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                @if(request()->hasAny(['search', 'program_id']))
                    <a href="{{ route('admin.dispatches.index') }}" class="btn btn-light btn-sm"><i class="bx bx-reset"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Dispatch Master Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Sample Code</th>
                        <th>Participant Laboratory</th>
                        <th>Courier Partner</th>
                        <th>Tracking Number</th>
                        <th>Dispatch Date</th>
                        <th>Dispatched By</th>
                        <th>Notification</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dispatches as $dispatch)
                        <tr>
                            <td class="fw-bold text-primary">
                                <i class="bx bx-package me-1"></i> {{ $dispatch->sample->sample_code ?? 'N/A' }}
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $dispatch->sample->registration->lab->laboratory_name ?? 'N/A' }}</div>
                                <small class="text-muted"><i class="bx bx-envelope"></i> {{ $dispatch->sample->registration->lab->email ?? '' }}</small>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $dispatch->courier_name }}</span></td>
                            <td class="fw-semibold text-dark font-monospace"><code>{{ $dispatch->tracking_number }}</code></td>
                            <td class="small text-muted">{{ \Carbon\Carbon::parse($dispatch->dispatch_date)->format('d M Y') }}</td>
                            <td>{{ $dispatch->dispatchedByAdmin->full_name ?? 'System Admin' }}</td>
                            <td>
                                @if($dispatch->notification_sent)
                                    <span class="badge badge-soft-success"><i class="bx bx-check-circle me-1"></i> Sent</span>
                                @else
                                    <span class="badge badge-soft-warning">Pending</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <form action="{{ route('admin.dispatches.resend', $dispatch->dispatch_id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-primary btn-sm">
                                        <i class="bx bx-mail-send me-1"></i> Resend Email
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bx bx-package display-4 text-muted d-block mb-2"></i>
                                No sample dispatches recorded yet. Go to <a href="{{ route('admin.programs.index') }}">PT Programs</a> to dispatch samples.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($dispatches->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $dispatches->links() }}
        </div>
    @endif
</div>
@endsection
