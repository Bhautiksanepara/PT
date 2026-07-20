@extends('layouts.app')

@section('title', 'Official PT Plans')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Official PT Plan Directory</h4>
        <p class="text-muted small mb-0">ISO 17043 PT Scheme Plans, Preparation Instructions & Coordinators</p>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Program Code</th>
                        <th>Program Number</th>
                        <th>Material / Matrix</th>
                        <th>Assigned Coordinator</th>
                        <th>Sample Quantity</th>
                        <th>Created At</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $plan)
                        <tr>
                            <td class="fw-bold text-primary">{{ $plan->program->program_code ?? 'N/A' }}</td>
                            <td class="fw-semibold text-dark">{{ $plan->program_number }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $plan->material }}</span></td>
                            <td>
                                @if($plan->coordinator)
                                    <div class="fw-semibold text-dark">{{ $plan->coordinator->full_name }}</div>
                                    <small class="text-muted">{{ $plan->coordinator->email }}</small>
                                @else
                                    <span class="text-muted small">Not Assigned</span>
                                @endif
                            </td>
                            <td>{{ $plan->sample_quantity ?? 'N/A' }}</td>
                            <td class="small text-muted">{{ \Carbon\Carbon::parse($plan->created_at)->format('d M Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.plans.show', $plan->program_id) }}" class="btn btn-outline-primary btn-sm me-1">
                                    <i class="bx bx-show me-1"></i> View Plan
                                </a>
                                <a href="{{ route('admin.plans.print', $plan->program_id) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                                    <i class="bx bx-printer me-1"></i> Print / PDF
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bx bx-file display-4 text-muted d-block mb-2"></i>
                                No PT Plans generated yet. Go to <a href="{{ route('admin.programs.index') }}">PT Programs</a> and click "Generate Plan".
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($plans->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $plans->links() }}
        </div>
    @endif
</div>
@endsection
