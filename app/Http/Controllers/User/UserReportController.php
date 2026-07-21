<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PtProgram;
use App\Models\ProgramRegistration;
use App\Models\Observation;
use App\Models\StatisticalResult;
use App\Services\StatsCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserReportController extends Controller
{
    public function viewIndividualReport($program_id, $registration_id)
    {
        $lab = Auth::guard('lab')->user();

        $registration = ProgramRegistration::with(['lab', 'program.parameters'])
            ->where('lab_id', $lab->lab_id)
            ->findOrFail($registration_id);

        $program = PtProgram::with('parameters')->findOrFail($program_id);

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

        return view('user.reports.certificate', compact('program', 'registration', 'lab'));
    }
}
