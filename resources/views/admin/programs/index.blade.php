@extends('layouts.app')

@section('title', 'PT Programs')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">PT Program Management</h4>
        <p class="text-muted small mb-0">Create, edit, monitor, and close Proficiency Testing programs</p>
    </div>
    <div>
        <a href="{{ route('admin.programs.create') }}" class="btn btn-primary shadow-sm btn-sm px-3">
            <i class="bx bx-plus me-1"></i> Create New Program
        </a>
    </div>
</div>

<!-- Filters & Search Card -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form action="{{ route('admin.programs.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bx bx-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search by Code, Name, Discipline..." value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Filter Program Status --</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open (Active)</option>
                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>

            <div class="col-md-3">
                <select name="registration_status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Filter Reg Window --</option>
                    <option value="upcoming" {{ request('registration_status') == 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                    <option value="active" {{ request('registration_status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="closed" {{ request('registration_status') == 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-secondary btn-sm w-100"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                @if(request()->hasAny(['search', 'status', 'registration_status']))
                    <a href="{{ route('admin.programs.index') }}" class="btn btn-light btn-sm"><i class="bx bx-reset"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Programs Data Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Program Name</th>
                        <th>Discipline</th>
                        <th>Fee (₹)</th>
                        <th>Parameters</th>
                        <th>Reg Window</th>
                        <th>Program Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($programs as $program)
                        <tr>
                            <td class="fw-bold text-primary">{{ $program->program_code }}</td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $program->program_name }}</div>
                                @if($program->scheme_code)
                                    <small class="text-muted">Scheme: {{ $program->scheme_code }}</small>
                                @endif
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $program->discipline ?? 'General' }}</span></td>
                            <td class="fw-semibold">₹{{ number_format($program->program_fee, 2) }}</td>
                            <td><span class="badge bg-info text-dark rounded-pill">{{ $program->parameters_count }} Parameters</span></td>
                            <td>
                                @if($program->computed_registration_status === 'active')
                                    <span class="badge badge-soft-success"><i class="bx bx-check-circle me-1"></i> Active (Open)</span>
                                @elseif($program->computed_registration_status === 'upcoming')
                                    <span class="badge badge-soft-warning"><i class="bx bx-time me-1"></i> Upcoming</span>
                                @else
                                    <span class="badge badge-soft-secondary"><i class="bx bx-lock-alt me-1"></i> Closed (Disabled)</span>
                                @endif
                            </td>
                            <td>
                                @if($program->program_status === 'open')
                                    <span class="badge bg-success">Open</span>
                                @elseif($program->program_status === 'draft')
                                    <span class="badge bg-secondary">Draft</span>
                                @elseif($program->program_status === 'closed')
                                    <span class="badge bg-danger">Closed</span>
                                @else
                                    <span class="badge bg-primary">Completed</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm btn-icon rounded-circle" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li><a class="dropdown-item" href="{{ route('admin.programs.show', $program->program_id) }}"><i class="bx bx-show me-2 text-info"></i> View Details</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.plans.show', $program->program_id) }}"><i class="bx bx-task me-2 text-success"></i> Official PT Plan</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.samples.program', $program->program_id) }}"><i class="bx bx-barcode me-2 text-warning"></i> Assign Samples</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.dispatches.program', $program->program_id) }}"><i class="bx bx-package me-2 text-primary"></i> Dispatch Samples</a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.programs.edit', $program->program_id) }}"><i class="bx bx-edit me-2 text-secondary"></i> Edit Program</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('admin.programs.toggle-window', $program->program_id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="dropdown-item text-warning">
                                                    <i class="bx bx-slider-alt me-2"></i> {{ $program->registration_status === 'active' ? 'Force Close Window' : 'Force Open Window' }}
                                                </button>
                                            </form>
                                        </li>
                                        @if($program->program_status !== 'closed')
                                            <li>
                                                <form action="{{ route('admin.programs.close', $program->program_id) }}" method="POST" onsubmit="return confirm('Are you sure you want to close this program?')">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item text-danger"><i class="bx bx-x-circle me-2"></i> Close Program</button>
                                                </form>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bx bx-layer display-4 text-muted d-block mb-2"></i>
                                No PT Programs found. Click "Create New Program" to add one.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($programs->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $programs->links() }}
        </div>
    @endif
</div>
@endsection
