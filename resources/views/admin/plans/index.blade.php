@extends('layouts.app')

@section('title', 'Official PT Plans')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Official PT Plan Directory</h4>
        <p class="text-muted small mb-0">ISO 17043 PT Scheme Plans, Preparation Instructions & Coordinators</p>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary btn-sm shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#quickCreatePlanModal">
            <i class="bx bx-plus me-1"></i> Create PT Plan
        </button>
    </div>
</div>

<!-- Quick Create Plan Modal -->
<div class="modal fade" id="quickCreatePlanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold"><i class="bx bx-task text-primary me-1"></i> Select PT Program to Plan</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Choose a PT program without a plan below to generate preparation instructions and assign a coordinator.</p>
                <div class="mb-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bx bx-search"></i></span>
                        <input type="text" id="modalSearchInput" class="form-control border-start-0" placeholder="Search program by code or name..." onkeyup="filterModalPrograms()">
                    </div>
                </div>
                <div class="list-group" style="max-height: 280px; overflow-y: auto;">
                    @forelse($programsWithoutPlans as $prog)
                        <a href="{{ route('admin.plans.create', $prog->program_id) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center plan-modal-item" data-search="{{ strtolower($prog->program_code . ' ' . $prog->program_name) }}">
                            <div>
                                <span class="fw-bold text-primary">{{ $prog->program_code }}</span> — {{ $prog->program_name }}
                            </div>
                            <i class="bx bx-chevron-right text-muted"></i>
                        </a>
                    @empty
                        <div class="text-center py-3 text-muted">No programs without plans available. All programs are planned!</div>
                    @endforelse
                </div>
            </div>
        </div>
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

@push('scripts')
<script>
    function filterModalPrograms() {
        var input = document.getElementById('modalSearchInput');
        var filter = input.value.toLowerCase();
        var items = document.querySelectorAll('.plan-modal-item');
        
        items.forEach(function(item) {
            var searchData = item.getAttribute('data-search');
            if (searchData.indexOf(filter) > -1) {
                item.style.setProperty('display', 'flex', 'important');
            } else {
                item.style.setProperty('display', 'none', 'important');
            }
        });
    }
</script>
@endpush
@endsection
