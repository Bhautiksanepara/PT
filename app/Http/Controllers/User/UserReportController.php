<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PtProgram;
use App\Models\ProgramRegistration;
use App\Models\Observation;
use App\Models\StatisticalResult;
use App\Services\StatsCalculatorService;
use App\Services\DashboardAlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserReportController extends Controller
{
    public function index(Request $request, DashboardAlertService $dashboardAlertService)
    {
        $lab = Auth::guard('lab')->user();

        $query = ProgramRegistration::with(['program', 'sample', 'observations'])
            ->where('lab_id', $lab->lab_id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('registration_number', 'like', "%{$search}%")
                  ->orWhereHas('program', function ($pq) use ($search) {
                      $pq->where('program_code', 'like', "%{$search}%")
                        ->orWhere('program_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('sample', function ($sq) use ($search) {
                      $sq->where('sample_code', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('year')) {
            $query->whereYear('registered_at', $request->year);
        }

        $registrations = $query->orderBy('registered_at', 'desc')->paginate(10)->withQueryString();

        $availableYears = ProgramRegistration::where('lab_id', $lab->lab_id)
            ->selectRaw('YEAR(registered_at) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('user.reports.index', compact('lab', 'registrations', 'availableYears'));
    }

    public function viewIndividualReport($program_id, $registration_id, DashboardAlertService $dashboardAlertService)
    {
        $lab = Auth::guard('lab')->user();

        $registration = ProgramRegistration::with(['lab', 'program.parameters'])
            ->where('lab_id', $lab->lab_id)
            ->findOrFail($registration_id);

        $program = PtProgram::with('parameters')->findOrFail($program_id);

        // Enforce Admin Publish Check: Lock access until Admin publishes/freezes the program results
        $hasReports = \Illuminate\Support\Facades\DB::table('reports')
            ->where('program_id', $program->program_id)
            ->where('registration_id', $registration->registration_id)
            ->exists();

        if ($program->program_status !== 'forcefully_closed' && !$hasReports) {
            return redirect()->route('lab.dashboard')->with('error', 'PT evaluation results and reports for this program have not been published by the Admin yet.');
        }

        $observations = Observation::with('parameter')
            ->where('registration_id', $registration->registration_id)
            ->get();

        $parameterEvaluations = [];
        $chartParams = [];
        $chartZScores = [];
        $chartColors = [];

        foreach ($program->parameters as $param) {
            $obs = $observations->firstWhere('parameter_id', $param->parameter_id);

            // Get frozen or dynamic statistical results
            $frozenStat = StatisticalResult::where('program_id', $program->program_id)
                ->where('parameter_id', $param->parameter_id)
                ->first();

            if ($frozenStat) {
                $assignedVal = (float)$frozenStat->assigned_value;
                $targetSd = (float)$frozenStat->robust_standard_deviation;
            } else {
                $allObs = Observation::whereHas('registration', fn($q) => $q->where('program_id', $program->program_id))
                    ->where('parameter_id', $param->parameter_id)
                    ->pluck('result_value')
                    ->map(fn($v) => (float)$v)
                    ->filter()
                    ->values()
                    ->toArray();

                $stats = (new StatsCalculatorService())->calculate($allObs);
                $assignedVal = $stats['robust_mean'] ?? 0;
                $targetSd = $stats['robust_sd'] ?? 1;
            }

            $resultVal = $obs ? (float)$obs->result_value : null;
            $zScoreData = null;

            if ($resultVal !== null) {
                $zScoreData = (new StatsCalculatorService())->calculateZScore($resultVal, $assignedVal, $targetSd);

                $chartParams[] = $param->parameter_name;
                $chartZScores[] = $zScoreData['z_score'];
                $chartColors[] = $zScoreData['status'] === 'satisfactory' ? '#22c55e' : ($zScoreData['status'] === 'warning' ? '#f59e0b' : '#ef4444');
            }

            $parameterEvaluations[] = [
                'parameter_name' => $param->parameter_name,
                'test_method'    => $obs->test_method ?? $param->test_method ?? 'N/A',
                'unit'           => $obs->unit ?? $param->unit ?? '',
                'reported_value' => $obs ? $obs->result_value : 'N/A',
                'assigned_value' => number_format($assignedVal, 4),
                'target_sd'      => number_format($targetSd, 4),
                'z_score'        => $zScoreData['z_score'] ?? 0,
                'status'         => $zScoreData['status'] ?? 'N/A',
                'badge_class'    => $zScoreData['badge_class'] ?? 'bg-secondary',
                'label'          => $zScoreData['label'] ?? 'N/A',
                'remarks'        => $obs->remarks ?? 'N/A',
            ];
        }

        $sample = $registration->sample;
        $qrPayload = "ISO 17043 EVALUATION REPORT\nLAB: {$lab->laboratory_name}\nREG #: {$registration->registration_number}\nPROGRAM: {$program->program_code}\nSAMPLE ID: " . ($sample->sample_code ?? 'N/A') . "\nDATE: " . date('Y-m-d');

        // Mark this specific program's report alert as read now that the lab has opened it
        $dashboardAlertService->markReleasedReportAlertAsRead($lab->lab_id, $program->program_id);

        return view('user.reports.individual', [
            'program'              => $program,
            'registration'         => $registration,
            'lab'                  => $lab,
            'sample'               => $sample,
            'parameterEvaluations' => $parameterEvaluations,
            'chartParams'          => $chartParams,
            'chartZScores'         => $chartZScores,
            'chartColors'          => $chartColors,
            'qrPayload'            => $qrPayload,
        ]);
    }

    public function viewCertificate($program_id, $registration_id)
    {
        $lab = Auth::guard('lab')->user();

        $registration = ProgramRegistration::with(['lab', 'program'])
            ->where('lab_id', $lab->lab_id)
            ->findOrFail($registration_id);

        $program = PtProgram::findOrFail($program_id);

        $hasReports = \Illuminate\Support\Facades\DB::table('reports')
            ->where('program_id', $program->program_id)
            ->where('registration_id', $registration->registration_id)
            ->exists();

        if ($program->program_status !== 'forcefully_closed' && !$hasReports) {
            return redirect()->route('lab.dashboard')->with('error', 'Participation Certificate for this program has not been published by the Admin yet.');
        }

        return view('user.reports.certificate', compact('program', 'registration', 'lab'));
    }
}
