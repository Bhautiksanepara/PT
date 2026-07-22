@extends('layouts.app')

@section('title', 'Reports & Certificates Directory')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Reports & Certificates Directory</h4>
        <p class="text-muted small mb-0">Generate, view, and print ISO 17043 individual evaluation reports, certificates, and master matrix reports</p>
    </div>
</div>

<!-- Filter Card -->
<div class="card mb-4">
    <div class="card-body py-3">
        <form action="{{ route('admin.reports.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bx bx-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search Registration #, Lab Name, Program..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-4">
                <select name="program_id" class="form-select form-select-sm">
                    <option value="">-- Filter by PT Program --</option>
                    @foreach($programs as $p)
                        <option value="{{ $p->program_id }}" {{ request('program_id') == $p->program_id ? 'selected' : '' }}>
                            {{ $p->program_code }} - {{ $p->program_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-1">
                <button type="submit" class="btn btn-secondary btn-sm w-100"><i class="bx bx-filter-alt me-1"></i> Search</button>
                @if(request()->has('search') || request()->has('program_id'))
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-light btn-sm"><i class="bx bx-reset"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Programs Master Reports Banner -->
<div class="card mb-4 bg-primary text-white shadow-sm">
    <div class="card-body d-flex justify-content-between align-items-center py-3 flex-wrap gap-3">
        <div>
            <h6 class="fw-bold text-white mb-1"><i class="bx bx-table me-1"></i> Master Consolidated Z-Score Reports</h6>
            <small class="text-white-50">Select a program to view all participants and parameters in a single unified matrix report.</small>
        </div>
        
        <div class="d-flex align-items-center gap-2" style="min-width: 380px; position: relative;">
            <div class="flex-grow-1">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bx bx-search"></i></span>
                    <input type="text" id="masterReportSearch" class="form-control border-start-0 text-dark" placeholder="Quick search program..." onkeyup="filterMasterReports()">
                </div>
                <select id="masterReportSelect" class="form-select form-select-sm text-dark mt-1" style="max-height: 180px; position: absolute; left: 0; right: 0; z-index: 1050; display: none;" size="5">
                    <option value="" disabled selected>-- Select Program --</option>
                    @foreach($programs as $prog)
                        <option value="{{ $prog->program_id }}" data-search="{{ strtolower($prog->program_code . ' ' . $prog->program_name) }}">
                            {{ $prog->program_code }} - {{ $prog->program_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button onclick="goToMasterReport()" class="btn btn-light btn-sm text-primary fw-bold text-nowrap">
                <i class="bx bx-show me-1"></i> View Matrix
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('masterReportSearch');
    var select = document.getElementById('masterReportSelect');

    if (searchInput && select) {
        searchInput.addEventListener('focus', function() {
            select.style.display = 'block';
        });

        document.addEventListener('click', function(e) {
            if (e.target !== select && e.target !== searchInput && !select.contains(e.target)) {
                select.style.display = 'none';
            }
        });

        select.addEventListener('change', function() {
            var selectedOption = select.options[select.selectedIndex];
            if (selectedOption && selectedOption.value) {
                searchInput.value = selectedOption.text;
                select.style.display = 'none';
            }
        });
    }
});

function filterMasterReports() {
    var input = document.getElementById('masterReportSearch');
    var filter = input.value.toLowerCase();
    var select = document.getElementById('masterReportSelect');
    var options = select.options;
    
    select.style.display = 'block';

    for (var i = 0; i < options.length; i++) {
        var opt = options[i];
        if (opt.disabled) continue;
        
        var searchVal = opt.getAttribute('data-search') || '';
        if (searchVal.indexOf(filter) > -1) {
            opt.style.display = "";
        } else {
            opt.style.display = "none";
        }
    }
}

function goToMasterReport() {
    var select = document.getElementById('masterReportSelect');
    var programId = select.value;
    if (!programId) {
        alert('Please select a PT Program first.');
        return;
    }
    window.location.href = "{{ url('admin/programs') }}/" + programId + "/master-report";
}
</script>

<!-- Participant Registration Reports Table -->
<div class="card">
    <div class="card-header bg-light">
        <h6 class="fw-bold mb-0"><i class="bx bx-detail me-1 text-primary"></i> Individual Participant Reports & Certificates</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Registration #</th>
                        <th>Participant Laboratory</th>
                        <th>PT Program</th>
                        <th>Sample Code</th>
                        <th>Registration Date</th>
                        <th class="text-end">Generated Documents</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrations as $reg)
                        <tr>
                            <td class="fw-bold text-primary">{{ $reg->registration_number }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $reg->lab->laboratory_name ?? 'N/A' }}</div>
                                <small class="text-muted"><i class="bx bx-envelope me-1"></i> {{ $reg->lab->email ?? 'N/A' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-primary me-1">{{ $reg->program->program_code ?? 'N/A' }}</span>
                                <small class="text-muted d-block text-truncate" style="max-width:180px;">{{ $reg->program->program_name ?? 'N/A' }}</small>
                            </td>
                            <td>
                                @if($reg->sample)
                                    <span class="badge bg-light text-dark border"><i class="bx bx-qr-scan me-1"></i> {{ $reg->sample->sample_code }}</span>
                                @else
                                    <span class="badge bg-warning text-dark">Unassigned</span>
                                @endif
                            </td>
                            <td>{{ \Carbon\Carbon::parse($reg->registered_at)->format('d M Y') }}</td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.reports.individual', [$reg->program_id, $reg->registration_id]) }}" class="btn btn-outline-primary">
                                        <i class="bx bx-file me-1"></i> PT Evaluation Report
                                    </a>
                                    <a href="{{ route('admin.reports.certificate', [$reg->program_id, $reg->registration_id]) }}" class="btn btn-outline-success">
                                        <i class="bx bx-award me-1"></i> Certificate
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">No participant registrations found for report generation.</td></tr>
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
