<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispatch;
use App\Models\ProgramRegistration;
use App\Models\PtPlan;
use App\Models\PtProgram;
use App\Models\ReferralCode;
use App\Models\Sample;
use App\Models\SampleBatch;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminListExportController extends Controller
{
    private function csv(string $name, array $columns, iterable $rows): StreamedResponse
    {
        return new StreamedResponse(function () use ($columns, $rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($rows as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        }, 200, [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=' . $name . '_' . date('Y-m-d_H-i') . '.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }

    public function programs(Request $request): StreamedResponse
    {
        $query = PtProgram::with(['discipline'])->withCount(['parameters', 'registrations']);
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn ($q) => $q->where('program_code', 'like', "%{$search}%")
                ->orWhere('program_name', 'like', "%{$search}%")
                ->orWhereHas('discipline', fn ($dq) => $dq->where('discipline_name', 'like', "%{$search}%")));
        }
        if ($request->filled('status')) {
            $today = date('Y-m-d');
            if (in_array($request->status, ['draft', 'reopen', 'forcefully_closed'])) {
                $query->where('program_status', $request->status);
            } elseif ($request->status === 'open') {
                $query->where(fn ($q) => $q->where('program_status', 'open')->orWhere(fn ($sub) => $sub->whereNull('program_status')->whereNotNull('registration_start_date')->whereNotNull('registration_end_date')->where('registration_start_date', '<=', $today)->where('registration_end_date', '>=', $today)));
            } elseif ($request->status === 'completed') {
                $query->where(fn ($q) => $q->where('program_status', 'completed')->orWhere(fn ($sub) => $sub->whereNull('program_status')->whereNotNull('submission_deadline')->where('submission_deadline', '<', $today)));
            }
        }
        if ($request->filled('registration_status')) $query->where('registration_status', $request->registration_status);
        $programs = $query->orderBy('created_at', 'desc')->get();
        return $this->csv('pt_programs', ['Program Code', 'Program Name', 'Discipline', 'Scheme Code', 'Fee', 'Parameters', 'Registrations', 'Registration Status', 'Program Status', 'Registration Start', 'Registration End', 'Dispatch Date', 'Submission Deadline', 'Report Date', 'Created At'], $programs->map(fn ($p) => [$p->program_code, $p->program_name, data_get($p, 'discipline.discipline_name', 'General'), $p->scheme_code, $p->program_fee, $p->parameters_count, $p->registrations_count, $p->computed_registration_status, $p->computed_program_status, $p->registration_start_date, $p->registration_end_date, $p->dispatch_date, $p->submission_deadline, $p->report_date, optional($p->created_at)->format('Y-m-d H:i')]));
    }

    public function plans(): StreamedResponse
    {
        $plans = PtPlan::with(['program', 'coordinator'])->orderBy('created_at', 'desc')->get();
        return $this->csv('official_pt_plans', ['Program Code', 'Program Number', 'Material / Matrix', 'Timeline', 'Coordinator', 'Coordinator Email', 'Sample Quantity', 'Preparation Instructions', 'Created At'], $plans->map(fn ($p) => [data_get($p, 'program.program_code', 'N/A'), $p->program_number, $p->material, $p->timeline, data_get($p, 'coordinator.full_name', 'Not Assigned'), data_get($p, 'coordinator.email', ''), $p->sample_quantity, $p->sample_preparation_instructions, $p->created_at ? \Carbon\Carbon::parse($p->created_at)->format('Y-m-d H:i') : '']));
    }

    public function batches(Request $request): StreamedResponse
    {
        $query = SampleBatch::with(['program', 'preparedByAdmin', 'verifiedByAdmin'])->withCount('samples');
        if ($request->filled('search')) { $search = $request->search; $query->where(fn ($q) => $q->where('batch_number', 'like', "%{$search}%")->orWhere('material', 'like', "%{$search}%")); }
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('program_id')) $query->where('program_id', $request->program_id);
        $batches = $query->orderBy('created_at', 'desc')->get();
        return $this->csv('sample_production', ['Batch Number', 'Program Code', 'Material', 'Quantity', 'Prepared By', 'Verified By', 'Preparation Date', 'Status', 'Samples', 'Created At'], $batches->map(fn ($b) => [$b->batch_number, data_get($b, 'program.program_code', 'N/A'), $b->material, $b->quantity, data_get($b, 'preparedByAdmin.full_name', 'N/A'), data_get($b, 'verifiedByAdmin.full_name', 'N/A'), $b->preparation_date, ucfirst(str_replace('_', ' ', $b->status)), $b->samples_count, $b->created_at ? \Carbon\Carbon::parse($b->created_at)->format('Y-m-d H:i') : '']));
    }

    public function samples(Request $request): StreamedResponse
    {
        $query = Sample::with(['program', 'registration.lab', 'batch']);
        if ($request->filled('search')) { $search = $request->search; $query->where(fn ($q) => $q->where('sample_code', 'like', "%{$search}%")->orWhereHas('registration.lab', fn ($lq) => $lq->where('laboratory_name', 'like', "%{$search}%"))->orWhereHas('registration', fn ($rq) => $rq->where('registration_number', 'like', "%{$search}%"))); }
        if ($request->filled('program_id')) $query->where('program_id', $request->program_id);
        if ($request->filled('status')) $query->where('status', $request->status);
        $samples = $query->orderBy('created_at', 'desc')->get();
        return $this->csv('sample_assignments', ['Sample Code', 'Program Code', 'Registration Number', 'Laboratory', 'Batch Number', 'QR Code', 'Status', 'Assigned At'], $samples->map(fn ($s) => [$s->sample_code, data_get($s, 'program.program_code', 'N/A'), data_get($s, 'registration.registration_number', 'N/A'), data_get($s, 'registration.lab.laboratory_name', 'N/A'), data_get($s, 'batch.batch_number', 'N/A'), $s->qr_code, ucfirst($s->status), $s->created_at ? \Carbon\Carbon::parse($s->created_at)->format('Y-m-d H:i') : '']));
    }

    public function dispatches(Request $request): StreamedResponse
    {
        $query = Dispatch::with(['sample.program', 'sample.registration.lab', 'dispatchedByAdmin']);
        if ($request->filled('search')) { $search = $request->search; $query->where(fn ($q) => $q->where('courier_name', 'like', "%{$search}%")->orWhere('tracking_number', 'like', "%{$search}%")->orWhereHas('sample', fn ($sq) => $sq->where('sample_code', 'like', "%{$search}%"))->orWhereHas('sample.registration.lab', fn ($lq) => $lq->where('laboratory_name', 'like', "%{$search}%"))); }
        if ($request->filled('program_id')) $query->whereHas('sample', fn ($sq) => $sq->where('program_id', $request->program_id));
        $dispatches = $query->orderBy('created_at', 'desc')->get();
        return $this->csv('sample_dispatches', ['Sample Code', 'Program Code', 'Laboratory', 'Courier', 'Tracking Number', 'Dispatch Date', 'Dispatched By', 'Notification Sent', 'Received At', 'Arrival Condition'], $dispatches->map(fn ($d) => [data_get($d, 'sample.sample_code', 'N/A'), data_get($d, 'sample.program.program_code', 'N/A'), data_get($d, 'sample.registration.lab.laboratory_name', 'N/A'), $d->courier_name, $d->tracking_number, $d->dispatch_date, data_get($d, 'dispatchedByAdmin.full_name', 'System Admin'), $d->notification_sent ? 'Yes' : 'No', $d->received_at, $d->arrival_condition]));
    }

    public function stats(Request $request): StreamedResponse
    {
        $query = PtProgram::with(['parameters.observations']);
        if ($request->filled('search')) { $search = $request->search; $query->where(fn ($q) => $q->where('program_code', 'like', "%{$search}%")->orWhere('program_name', 'like', "%{$search}%")); }
        $rows = $query->orderBy('created_at', 'desc')->get()->flatMap(fn ($p) => $p->parameters->map(function ($param) use ($p) {
            $count = $param->observations->count();
            return [$p->program_code, $p->program_name, $param->parameter_name, $param->test_method, $param->unit, $count, $count >= 3 ? 'Ready for Analysis' : ($count ? 'Insufficient Submissions' : 'No Results Yet')];
        }));
        return $this->csv('statistical_analysis', ['Program Code', 'Program Name', 'Parameter', 'Test Method', 'Unit', 'Submissions', 'Analysis Status'], $rows);
    }

    public function referrals(Request $request): StreamedResponse
    {
        $query = ReferralCode::with(['clientLab', 'usedByLab', 'creator']);
        if ($request->filled('search')) { $search = $request->search; $query->where(fn ($q) => $q->where('code', 'like', "%{$search}%")->orWhereHas('clientLab', fn ($lq) => $lq->where('laboratory_name', 'like', "%{$search}%"))); }
        if ($request->filled('discount_type')) $query->where('discount_type', $request->discount_type);
        if ($request->filled('rule')) { if ($request->rule === 'one_time') $query->where('is_one_time_use', 1); elseif ($request->rule === 'client_specific') $query->where('is_client_specific', 1); elseif ($request->rule === 'expired') $query->where('expiry_date', '<', date('Y-m-d')); }
        $codes = $query->orderBy('created_at', 'desc')->get();
        return $this->csv('referral_codes', ['Code', 'Discount Type', 'Discount Value', 'One-Time Use', 'Client Specific', 'Client Laboratory', 'Expiry Date', 'Used', 'Used By', 'Created By', 'Created At'], $codes->map(fn ($c) => [$c->code, ucfirst($c->discount_type), $c->discount_value, $c->is_one_time_use ? 'Yes' : 'No', $c->is_client_specific ? 'Yes' : 'No', data_get($c, 'clientLab.laboratory_name', 'All Participating Labs'), $c->expiry_date, $c->is_used ? 'Yes' : 'No', data_get($c, 'usedByLab.laboratory_name', ''), data_get($c, 'creator.full_name', ''), $c->created_at ? \Carbon\Carbon::parse($c->created_at)->format('Y-m-d H:i') : '']));
    }

    public function reports(Request $request): StreamedResponse
    {
        $query = ProgramRegistration::with(['lab', 'program', 'sample']);
        if ($request->filled('search')) { $search = $request->search; $query->where(fn ($q) => $q->where('registration_number', 'like', "%{$search}%")->orWhereHas('lab', fn ($lq) => $lq->where('laboratory_name', 'like', "%{$search}%"))->orWhereHas('program', fn ($pq) => $pq->where('program_code', 'like', "%{$search}%"))); }
        if ($request->filled('program_id')) $query->where('program_id', $request->program_id);
        $registrations = $query->orderBy('registered_at', 'desc')->get();
        return $this->csv('reports_and_certificates', ['Registration Number', 'Laboratory', 'Laboratory Email', 'Program Code', 'Program Name', 'Sample Code', 'Registration Status', 'Registered At'], $registrations->map(fn ($r) => [$r->registration_number, data_get($r, 'lab.laboratory_name', 'N/A'), data_get($r, 'lab.email', ''), data_get($r, 'program.program_code', 'N/A'), data_get($r, 'program.program_name', 'N/A'), data_get($r, 'sample.sample_code', 'Unassigned'), ucfirst($r->status), $r->registered_at]));
    }
}
