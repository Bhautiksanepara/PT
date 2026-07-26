@extends('layouts.app')

@section('title', 'Participant Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Participant Management — Master Sheet</h4>
        <p class="text-muted small mb-0">View registered laboratories, contact details, status & payment history</p>
    </div>
    <div>
        <a href="{{ route('admin.participants.export', request()->all()) }}" class="btn btn-outline-success btn-sm px-3 shadow-sm">
            <i class="bx bx-download me-1"></i> Export Master Sheet (CSV/Excel)
        </a>
    </div>
</div>

<!-- Search & Filters Card -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form action="{{ route('admin.participants.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bx bx-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search by Laboratory Name, Contact Person, Email, Mobile..." value="{{ request('search') }}">
                </div>
            </div>

            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">-- Filter Account Status --</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>

            <div class="col-md-4 d-flex gap-1">
                <button type="submit" class="btn btn-secondary btn-sm w-100"><i class="bx bx-filter-alt me-1"></i> Apply Filter</button>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('admin.participants.index') }}" class="btn btn-light btn-sm"><i class="bx bx-reset"></i> Reset</a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Participants Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Participant ID</th>
                        <th>Laboratory Name</th>
                        <th>Contact Person</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Account Status</th>
                        <th>Payment Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($participants as $lab)
                        <tr>
                            <td class="fw-bold text-primary">LAB-{{ str_pad($lab->lab_id, 4, '0', STR_PAD_LEFT) }}</td>
                            <td>
                                <div class="fw-semibold text-dark">
                                    {{ $lab->laboratory_name }}
                                    @if(!$lab->profile_completed_at)
                                        <span class="badge bg-warning text-dark ms-1" style="font-size: 0.65rem;">Incomplete Profile</span>
                                    @endif
                                </div>
                                @if($lab->city || $lab->state)
                                    <small class="text-muted"><i class="bx bx-map-pin"></i> {{ $lab->city }}, {{ $lab->state }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="fw-medium text-dark">{{ $lab->contact_person ?? 'N/A' }}</div>
                                <small class="text-muted">{{ $lab->designation }}</small>
                            </td>
                            <td><a href="mailto:{{ $lab->email }}" class="text-decoration-none">{{ $lab->email }}</a></td>
                            <td>{{ $lab->mobile_number ?? 'N/A' }}</td>
                            <td>
                                @if($lab->status === 'active')
                                    <span class="badge badge-soft-success"><i class="bx bx-check-circle me-1"></i> Active</span>
                                @elseif($lab->status === 'inactive')
                                    <span class="badge badge-soft-secondary">Inactive</span>
                                @else
                                    <span class="badge badge-soft-danger"><i class="bx bx-block me-1"></i> Suspended</span>
                                @endif
                            </td>
                            <td>
                                @if($lab->latest_payment_status === 'success')
                                    <span class="badge badge-soft-success"><i class="bx bx-check me-1"></i> Paid</span>
                                @elseif($lab->latest_payment_status === 'pending')
                                    <span class="badge badge-soft-warning"><i class="bx bx-time me-1"></i> Payment Pending</span>
                                @else
                                    <span class="badge bg-light text-muted border">No Registrations</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.participants.show', $lab->lab_id) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="bx bx-show me-1"></i> View Profile
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="bx bx-group display-4 text-muted d-block mb-2"></i>
                                No laboratories found matching your criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($participants->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $participants->links() }}
        </div>
    @endif
</div>
@endsection
