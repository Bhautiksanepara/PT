@extends('layouts.app')

@section('title', 'Referral Code Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Referral Code & Discount System</h4>
        <p class="text-muted small mb-0">Manage promotional codes, percentage/fixed discounts, and referral rules</p>
    </div>
    <button type="button" class="btn btn-primary btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#createReferralModal">
        <i class="bx bx-plus me-1"></i> Create Referral Code
    </button>
</div>

<!-- Stat KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card card-stat text-center py-3 border-start border-primary border-4">
            <small class="text-muted fw-semibold">Active Codes</small>
            <h3 class="fw-bold text-primary mb-0 mt-1">{{ $totalActive }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat text-center py-3 border-start border-info border-4">
            <small class="text-muted fw-semibold">One-Time Use Codes</small>
            <h3 class="fw-bold text-info mb-0 mt-1">{{ $totalOneTime }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat text-center py-3 border-start border-warning border-4">
            <small class="text-muted fw-semibold">Client Specific Codes</small>
            <h3 class="fw-bold text-warning mb-0 mt-1">{{ $totalClientSpecific }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat text-center py-3 border-start border-success border-4">
            <small class="text-muted fw-semibold">Claimed / Used</small>
            <h3 class="fw-bold text-success mb-0 mt-1">{{ $totalUsed }}</h3>
        </div>
    </div>
</div>

<!-- Search & Filters -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form action="{{ route('admin.referrals.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bx bx-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search Promo Code or Client Lab..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="discount_type" class="form-select form-select-sm">
                    <option value="">-- Discount Type --</option>
                    <option value="percentage" {{ request('discount_type') == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                    <option value="fixed" {{ request('discount_type') == 'fixed' ? 'selected' : '' }}>Fixed Amount (₹)</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="rule" class="form-select form-select-sm">
                    <option value="">-- Rule Filter --</option>
                    <option value="one_time" {{ request('rule') == 'one_time' ? 'selected' : '' }}>One Time Use</option>
                    <option value="client_specific" {{ request('rule') == 'client_specific' ? 'selected' : '' }}>Client Specific</option>
                    <option value="expired" {{ request('rule') == 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-secondary btn-sm w-100"><i class="bx bx-filter-alt me-1"></i> Filter</button>
                @if(request()->has('search') || request()->has('discount_type') || request()->has('rule'))
                    <a href="{{ route('admin.referrals.index') }}" class="btn btn-light btn-sm"><i class="bx bx-reset"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Referral Codes Table -->
<div class="card">
    <div class="card-header bg-light">
        <h6 class="fw-bold mb-0"><i class="bx bx-purchase-tag-alt me-1 text-primary"></i> Referral & Promo Codes Registry</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Referral Code</th>
                        <th>Discount Value</th>
                        <th>Applied Rules</th>
                        <th>Assigned Client Lab</th>
                        <th>Status</th>
                        <th>Created Date</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($referralCodes as $code)
                        <tr>
                            <td>
                                <span class="badge bg-primary fs-6 font-monospace py-2 px-3"><i class="bx bx-purchase-tag me-1"></i> {{ $code->code }}</span>
                            </td>
                            <td>
                                @if($code->discount_type === 'percentage')
                                    <span class="fw-bold text-success fs-5">{{ number_format($code->discount_value, 0) }}% OFF</span>
                                @else
                                    <span class="fw-bold text-primary fs-5">₹{{ number_format($code->discount_value, 2) }} OFF</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @if($code->is_one_time_use)
                                        <span class="badge bg-soft-info text-info border"><i class="bx bx-check me-1"></i> One-Time Use</span>
                                    @else
                                        <span class="badge bg-light text-muted border">Multi-Use</span>
                                    @endif

                                    @if($code->is_client_specific)
                                        <span class="badge bg-soft-warning text-warning border"><i class="bx bx-user-check me-1"></i> Client Specific</span>
                                    @endif

                                    @if($code->expiry_date)
                                        <span class="badge bg-light text-dark border"><i class="bx bx-calendar me-1"></i> Exp: {{ $code->expiry_date }}</span>
                                    @else
                                        <span class="badge bg-light text-muted border">No Expiry</span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($code->is_client_specific && $code->clientLab)
                                    <div class="fw-semibold text-dark">{{ $code->clientLab->laboratory_name }}</div>
                                    <small class="text-muted">{{ $code->clientLab->email }}</small>
                                @else
                                    <span class="text-muted small">All Participating Labs</span>
                                @endif
                            </td>
                            <td>
                                @if($code->is_used)
                                    <span class="badge bg-secondary"><i class="bx bx-check-circle me-1"></i> Claimed / Used</span>
                                    @if($code->usedByLab)
                                        <small class="d-block text-muted micro-text">by {{ $code->usedByLab->laboratory_name }}</small>
                                    @endif
                                @elseif($code->expiry_date && \Carbon\Carbon::parse($code->expiry_date)->isPast())
                                    <span class="badge bg-danger"><i class="bx bx-time me-1"></i> Expired</span>
                                @else
                                    <span class="badge bg-success"><i class="bx bx-badge-check me-1"></i> Active</span>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($code->created_at)->format('d M Y') }}</td>
                            <td class="text-end">
                                <form action="{{ route('admin.referrals.destroy', $code->referral_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this referral code?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5">No referral codes found. Click "+ Create Referral Code" to add one!</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($referralCodes->hasPages())
    <div class="mt-4">
        {{ $referralCodes->links() }}
    </div>
@endif

<!-- Create Referral Code Modal -->
<div class="modal fade" id="createReferralModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.referrals.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h6 class="modal-title fw-bold"><i class="bx bx-plus-circle me-1 text-primary"></i> Create New Referral Code</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <!-- Code & Generator -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Referral / Promo Code <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" name="code" id="refCodeInput" class="form-control text-uppercase fw-bold font-monospace" placeholder="e.g. PROMO10, APEX15, WELCOME5" required>
                            <button type="button" class="btn btn-outline-secondary" onclick="generateRandomCode()">
                                <i class="bx bx-refresh me-1"></i> Generate Code
                            </button>
                        </div>
                    </div>

                    <!-- Discount Presets & Type -->
                    <div class="card bg-light p-3 border mb-3">
                        <label class="form-label small fw-semibold mb-2">Discount Type & Value Presets</label>
                        
                        <!-- Presets Quick Buttons -->
                        <div class="d-flex gap-2 mb-3">
                            <button type="button" class="btn btn-sm btn-outline-primary fw-bold" onclick="setDiscount('percentage', 5)">
                                5% OFF
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary fw-bold" onclick="setDiscount('percentage', 10)">
                                10% OFF
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary fw-bold" onclick="setDiscount('percentage', 15)">
                                15% OFF
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success fw-bold" onclick="setDiscount('fixed', 1000)">
                                ₹1000 Fixed OFF
                            </button>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Discount Type <span class="text-danger">*</span></label>
                                <select name="discount_type" id="discountTypeSelect" class="form-select" required>
                                    <option value="percentage">Percentage Discount (%)</option>
                                    <option value="fixed">Fixed Amount (₹)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Discount Value <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="discount_value" id="discountValueInput" class="form-control fw-bold" placeholder="e.g. 5, 10, 15 or 1000" required>
                            </div>
                        </div>
                    </div>

                    <!-- Referral Rules Configuration -->
                    <div class="card p-3 border">
                        <h6 class="fw-bold text-dark mb-3"><i class="bx bx-slider-alt me-1 text-primary"></i> Referral Rules Configuration</h6>

                        <!-- Rule 1: One Time Use -->
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_one_time_use" id="isOneTimeCheck" value="1">
                            <label class="form-check-label fw-semibold text-dark" for="isOneTimeCheck">
                                ✓ One Time Use Rule
                            </label>
                            <small class="text-muted d-block">Code becomes invalid after it is claimed once by a laboratory.</small>
                        </div>

                        <!-- Rule 2: Client Specific -->
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="is_client_specific" id="isClientCheck" value="1" onchange="toggleClientDropdown(this.checked)">
                            <label class="form-check-label fw-semibold text-dark" for="isClientCheck">
                                ✓ Client Specific Rule
                            </label>
                            <small class="text-muted d-block">Restrict code usage to a specific participating laboratory only.</small>
                        </div>

                        <div class="mb-3 ps-4 d-none" id="clientLabWrapper">
                            <label class="form-label small fw-semibold">Select Target Laboratory <span class="text-danger">*</span></label>
                            <select name="client_lab_id" class="form-select">
                                <option value="">-- Choose Laboratory --</option>
                                @foreach($labs as $l)
                                    <option value="{{ $l->lab_id }}">{{ $l->laboratory_name }} ({{ $l->email }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Rule 3: Expiry Date -->
                        <div class="mb-0 border-top pt-3">
                            <label class="form-label small fw-semibold">✓ Expiry Date Rule (Optional)</label>
                            <input type="date" name="expiry_date" class="form-control" min="{{ date('Y-m-d') }}">
                            <small class="text-muted">Code will automatically expire at midnight on this date.</small>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Referral Code</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function setDiscount(type, value) {
        document.getElementById('discountTypeSelect').value = type;
        document.getElementById('discountValueInput').value = value;
    }

    function toggleClientDropdown(isCheck) {
        var wrapper = document.getElementById('clientLabWrapper');
        if (isCheck) {
            wrapper.classList.remove('d-none');
        } else {
            wrapper.classList.add('d-none');
        }
    }

    function generateRandomCode() {
        var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        var result = 'PROMO';
        for (var i = 0; i < 5; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('refCodeInput').value = result;
    }
</script>
@endpush
