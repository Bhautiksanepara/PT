@extends('layouts.app')

@section('title', 'Historical Archive')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Historical Archive Vault</h4>
        <p class="text-muted small mb-0">Permanent repository storing past PT Schemes, Reports, Certificates, Payments, Observations, and Statistical Data</p>
    </div>
</div>

<!-- Search & Archive Filters Card -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form action="{{ route('admin.archive.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bx bx-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search Scheme Code, Name, Discipline..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="discipline" class="form-select form-select-sm">
                    <option value="">-- Filter by Discipline --</option>
                    @foreach($disciplines as $d)
                        <option value="{{ $d->id }}" {{ request('discipline') == $d->id ? 'selected' : '' }}>{{ $d->discipline_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="year" class="form-select form-select-sm">
                    <option value="">-- Filter by Year --</option>
                    @foreach($availableYears as $y)
                        <option value="{{ $y }}" {{ request('year') == $y ? 'selected' : '' }}>Year {{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-secondary btn-sm w-100"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                @if(request()->has('search') || request()->has('discipline') || request()->has('year'))
                    <a href="{{ route('admin.archive.index') }}" class="btn btn-light btn-sm"><i class="bx bx-reset"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Archive Programs Grid -->
<div class="row g-4">
    @forelse($programs as $program)
        <div class="col-lg-4 col-md-6">
            <div class="card h-100 shadow-sm border-top border-4 {{ $program->program_status === 'completed' || $program->program_status === 'closed' ? 'border-secondary' : 'border-primary' }}">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-primary fs-6">{{ $program->program_code }}</span>
                        @if($program->program_status === 'completed')
                            <span class="badge bg-secondary"><i class="bx bx-archive me-1"></i> Completed & Archived</span>
                        @else
                            <span class="badge bg-success"><i class="bx bx-check me-1"></i> Active Scheme</span>
                        @endif
                    </div>
                    <h6 class="fw-bold text-dark mb-2">{{ $program->program_name }}</h6>
                    <p class="text-muted small mb-3"><i class="bx bx-atom me-1 text-info"></i> Discipline: <strong>{{ $program->discipline->discipline_name ?? 'N/A' }}</strong></p>

                    <div class="p-2 bg-light rounded border mb-3">
                        <div class="row text-center g-1">
                            <div class="col-4 border-end">
                                <small class="text-muted micro-text d-block">Parameters</small>
                                <span class="fw-bold text-dark">{{ $program->parameters->count() }}</span>
                            </div>
                            <div class="col-4 border-end">
                                <small class="text-muted micro-text d-block">Labs</small>
                                <span class="fw-bold text-dark">{{ $program->registrations->count() }}</span>
                            </div>
                            <div class="col-4">
                                <small class="text-muted micro-text d-block">Year</small>
                                <span class="fw-bold text-dark">{{ \Carbon\Carbon::parse($program->created_at)->format('Y') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted micro-text">Archived: {{ \Carbon\Carbon::parse($program->created_at)->format('d M Y') }}</small>
                        <a href="{{ route('admin.archive.program', $program->program_id) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bx bx-folder-open me-1"></i> Open Vault
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center text-muted py-5">
            <i class="bx bx-archive display-4 text-muted d-block mb-2"></i>
            No historical records found matching filter criteria.
        </div>
    @endforelse
</div>

@if($programs->hasPages())
    <div class="mt-4">
        {{ $programs->links() }}
    </div>
@endif
@endsection
