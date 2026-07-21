@extends('layouts.user')

@section('title', 'Phase 7 - Reports & Certificates Archive')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1"><i class="bx bx-award me-2 text-success"></i> Phase 7 – PT Reports & Certificates Vault</h4>
        <p class="text-muted small mb-0">Permanently access, view, and download your official ISO 17043 Evaluation Reports, parameter Z-Scores, and Certificates</p>
    </div>
    <div>
        <a href="{{ route('user.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i> Back to Dashboard
        </a>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form action="{{ route('user.reports.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-7">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bx bx-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search Sample Code, Program Code, Scheme Name..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="year" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- All Registration Years --</option>
                    @foreach($availableYears as $yr)
                        <option value="{{ $yr }}" {{ request('year') == $yr ? 'selected' : '' }}>Year {{ $yr }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                @if(request()->hasAny(['search', 'year']))
                    <a href="{{ route('user.reports.index') }}" class="btn btn-light btn-sm"><i class="bx bx-reset"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Reports Archive Master Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Registration #</th>
                        <th>Program Code</th>
                        <th>Sample Code</th>
                        <th>Registered Date</th>
                        <th>Evaluation Status</th>
                        <th class="text-end">Reports & Downloads</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrations as $reg)
                        @php
                            $hasReports = \Illuminate\Support\Facades\DB::table('reports')
                                ->where('program_id', $reg->program_id)
                                ->where('registration_id', $reg->registration_id)
                                ->exists() || ($reg->reports->count() > 0);
                            $isPublished = ($reg->program->program_status === 'completed') && $hasReports;
                        @endphp
                        <tr>
                            <td>
                                <span class="fw-bold text-primary">{{ $reg->registration_number }}</span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $reg->program->program_name }}</div>
                                <span class="badge bg-light text-dark border">{{ $reg->program->program_code }}</span>
                            </td>
                            <td>
                                @if($reg->sample)
                                    <span class="badge bg-primary font-monospace"><i class="bx bx-barcode me-1"></i> {{ $reg->sample->sample_code }}</span>
                                @else
                                    <span class="badge bg-light text-muted border">Pending Assignment</span>
                                @endif
                            </td>
                            <td class="small text-muted">{{ \Carbon\Carbon::parse($reg->registered_at)->format('d M Y') }}</td>
                            <td>
                                @if($isPublished)
                                    <span class="badge badge-soft-success py-2 px-3"><i class="bx bx-check-circle me-1"></i> Statistical Report Published</span>
                                @else
                                    <span class="badge badge-soft-warning py-2 px-3"><i class="bx bx-time me-1"></i> Evaluation Pending Publish</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="{{ route('user.invoice', $reg->registration_id) }}" class="btn btn-sm btn-outline-secondary text-nowrap" target="_blank" title="Download Tax Invoice">
                                        <i class="bx bx-receipt me-1"></i> Tax Invoice
                                    </a>

                                    @if($isPublished)
                                        <a href="{{ route('user.reports.individual', [$reg->program_id, $reg->registration_id]) }}" class="btn btn-sm btn-outline-primary text-nowrap" target="_blank" title="View Official PT Evaluation Report">
                                            <i class="bx bx-file me-1"></i> PT Report
                                        </a>
                                        <a href="{{ route('user.reports.certificate', [$reg->program_id, $reg->registration_id]) }}" class="btn btn-sm btn-outline-success text-nowrap" target="_blank" title="Download Certificate of Participation">
                                            <i class="bx bx-award me-1"></i> Certificate
                                        </a>
                                    @else
                                        <button class="btn btn-sm btn-outline-secondary text-nowrap opacity-50" disabled title="Reports & Certificates will be unlocked once Admin publishes final results">
                                            <i class="bx bx-lock-alt me-1"></i> PT Report
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary text-nowrap opacity-50" disabled title="Reports & Certificates will be unlocked once Admin publishes final results">
                                            <i class="bx bx-lock-alt me-1"></i> Certificate
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                <i class="bx bx-file-blank display-4 text-muted d-block mb-2"></i>
                                No PT reports or certificates generated yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($registrations->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $registrations->links() }}
        </div>
    @endif
</div>
@endsection
