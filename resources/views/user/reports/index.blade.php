@extends('layouts.user')

@section('title', 'Phase 7 - Reports & Certificates Archive')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-1"><i class="bx bx-award me-2 text-success"></i>Phase 7 – PT Reports & Certificates Release</h3>
            <p class="text-muted small mb-0">Permanently access and download your official PT Performance Reports, Z-Score evaluations, and Certificates.</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($registrations->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bx bx-file-blank fs-1 mb-2 text-secondary"></i>
                    <p class="mb-0">No PT reports or certificates generated yet.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Registration #</th>
                                <th>PT Program</th>
                                <th>Sample Code</th>
                                <th>Registered Date</th>
                                <th>Evaluation Status</th>
                                <th>Download Options</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($registrations as $reg)
                                <tr>
                                    <td>
                                        <span class="fw-bold text-dark">{{ $reg->registration_number }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $reg->program->program_name ?? 'N/A' }}</div>
                                        <small class="text-muted">Scheme: {{ $reg->program->scheme_code ?: $reg->program->program_code }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary border">{{ $reg->sample->sample_code ?? 'N/A' }}</span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($reg->registered_at)->format('d M Y') }}</td>
                                    <td>
                                        @if($reg->program->program_status === 'completed' || $reg->program->program_status === 'closed')
                                            <span class="badge badge-soft-success py-2 px-3"><i class="bx bx-check-circle me-1"></i> Statistical Report Released</span>
                                        @else
                                            <span class="badge badge-soft-info py-2 px-3"><i class="bx bx-time me-1"></i> In Evaluation</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('lab.reports.show', $reg->registration_id) }}" class="btn btn-sm btn-success rounded-pill">
                                                <i class="bx bx-show me-1"></i> View PT Report
                                            </a>
                                            <a href="{{ route('lab.payments.success', $reg->registration_id) }}" class="btn btn-sm btn-outline-dark rounded-pill">
                                                <i class="bx bx-receipt me-1"></i> Invoice
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
