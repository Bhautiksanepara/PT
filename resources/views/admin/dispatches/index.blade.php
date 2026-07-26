@extends('layouts.app')

@section('title', 'Dispatch Management Master Sheet')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Dispatch Management — Master Sheet</h4>
        <p class="text-muted small mb-0">Track courier dispatches, tracking numbers, and notification status</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.dispatches.export', request()->all()) }}" class="btn btn-outline-success btn-sm shadow-sm fw-semibold">
            <i class="bx bx-download me-1"></i> Export (CSV/Excel)
        </a>
        <button type="button" class="btn btn-primary btn-sm shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#quickDispatchProgramModal">
            <i class="bx bx-package me-1"></i> + New Dispatch by Program
        </button>
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
                <div class="mb-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bx bx-search"></i></span>
                        <input type="text" id="modalSearchInput" class="form-control border-start-0" placeholder="Search program by code or name..." onkeyup="filterModalPrograms()">
                    </div>
                </div>
                <div class="list-group" style="max-height: 280px; overflow-y: auto;">
                    @forelse($programs as $prog)
                        <a href="{{ route('admin.dispatches.program', $prog->program_id) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center dispatch-modal-item" data-search="{{ strtolower($prog->program_code . ' ' . $prog->program_name) }}">
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
                                @if($dispatch->received_at)
                                    @if($dispatch->arrival_condition === 'damaged' || $dispatch->arrival_condition === 'leaked')
                                        <span class="badge bg-danger py-1 px-2"><i class="bx bx-error me-1"></i> Received: {{ ucfirst($dispatch->arrival_condition) }}</span>
                                    @else
                                        <span class="badge badge-soft-success py-1 px-2"><i class="bx bx-check-double me-1"></i> Received Intact</span>
                                    @endif
                                    <small class="text-muted d-block micro-text">{{ \Carbon\Carbon::parse($dispatch->received_at)->format('d M H:i') }}</small>
                                @elseif($dispatch->notification_sent)
                                    <span class="badge badge-soft-info py-1 px-2"><i class="bx bx-truck me-1"></i> In Transit</span>
                                @else
                                    <span class="badge badge-soft-warning py-1 px-2">Pending</span>
                                @endif
                            </td>
                            <td class="text-end text-nowrap">
                                <div class="d-inline-flex gap-2 align-items-center justify-content-end">
                                    @if($dispatch->sample)
                                        <button type="button" class="btn btn-outline-dark btn-sm" data-bs-toggle="modal" data-bs-target="#adminQrMasterModal{{ $dispatch->dispatch_id }}">
                                            <i class="bx bx-qr-scan me-1"></i> QR Sticker
                                        </button>
                                    @endif

                                    <form action="{{ route('admin.dispatches.resend', $dispatch->dispatch_id) }}" method="POST" class="d-inline m-0">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-primary btn-sm">
                                            <i class="bx bx-mail-send me-1"></i> Resend Email
                                        </button>
                                    </form>
                                </div>

                                @if($dispatch->sample)
                                    <!-- QR Code Sticker Modal -->
                                    <div class="modal fade text-start" id="adminQrMasterModal{{ $dispatch->dispatch_id }}" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header bg-dark text-white py-3">
                                                    <h6 class="modal-title fw-bold text-white mb-0"><i class="bx bx-qr-scan text-primary me-1"></i> Printable Sample Bottle QR Sticker</h6>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body text-center p-4" id="adminMasterPrintableQr{{ $dispatch->dispatch_id }}">
                                                    @php
                                                        $sampleCode = $dispatch->sample->sample_code ?? 'N/A';
                                                        $programCode = $dispatch->sample->program->program_code ?? 'N/A';
                                                        $labName = $dispatch->sample->registration->lab->laboratory_name ?? 'Laboratory';
                                                        $qrPayload = "SAMPLE ID: {$sampleCode}\nPROGRAM: {$programCode}\nLAB: {$labName}\nTRACKING: {$dispatch->tracking_number}";
                                                    @endphp

                                                    <div class="p-3 bg-light rounded border d-inline-block mb-3 shadow-sm">
                                                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data={{ urlencode($qrPayload) }}" alt="Sample QR Code" style="width: 160px; height: 160px;">
                                                        <div class="mt-2 font-monospace fw-bold text-primary fs-5">{{ $sampleCode }}</div>
                                                    </div>

                                                    <div class="bg-light p-3 rounded border text-start mb-2">
                                                        <div class="row g-2 small">
                                                            <div class="col-6"><span class="text-muted d-block">Program Code:</span> <strong>{{ $programCode }}</strong></div>
                                                            <div class="col-6"><span class="text-muted d-block">Sample Code:</span> <code class="fw-bold text-dark font-monospace">{{ $sampleCode }}</code></div>
                                                            <div class="col-6 mt-2"><span class="text-muted d-block">Courier:</span> <strong>{{ $dispatch->courier_name }}</strong></div>
                                                            <div class="col-6 mt-2"><span class="text-muted d-block">Tracking #:</span> <code>{{ $dispatch->tracking_number }}</code></div>
                                                            <div class="col-12 mt-2"><span class="text-muted d-block">Participant Lab:</span> <strong>{{ $labName }}</strong></div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light py-2 justify-content-between">
                                                    <button type="button" class="btn btn-primary btn-sm" onclick="printMasterAdminQr('adminMasterPrintableQr{{ $dispatch->dispatch_id }}')">
                                                        <i class="bx bx-printer me-1"></i> Print Bottle Sticker
                                                    </button>
                                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
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
    @if($dispatches->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $dispatches->links() }}
        </div>
    @endif
</div>

<script>
function printMasterAdminQr(elementId) {
    var printContents = document.getElementById(elementId).innerHTML;
    var originalContents = document.body.innerHTML;
    document.body.innerHTML = '<div style="padding: 40px; text-align: center;">' + printContents + '</div>';
    window.print();
    document.body.innerHTML = originalContents;
    window.location.reload();
}

function filterModalPrograms() {
    var input = document.getElementById('modalSearchInput');
    var filter = input.value.toLowerCase();
    var items = document.querySelectorAll('.dispatch-modal-item');

    items.forEach(function(item) {
        var text = item.getAttribute('data-search') || '';
        if (text.indexOf(filter) > -1) {
            item.style.display = "";
        } else {
            item.style.display = "none";
        }
    });
}
</script>
@endsection
