<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PtProgram;
use App\Models\ProgramRegistration;
use App\Models\ProgramParameter;
use App\Models\Observation;
use App\Models\Sample;
use App\Services\StatsCalculatorService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected $statsCalculator;

    public function __construct(StatsCalculatorService $statsCalculator)
    {
        $this->statsCalculator = $statsCalculator;
    }

    public function index(Request $request)
    {
        $query = ProgramRegistration::with(['lab', 'program', 'sample']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('registration_number', 'like', "%{$search}%")
                  ->orWhereHas('lab', function ($lq) use ($search) {
                      $lq->where('laboratory_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('program', function ($pq) use ($search) {
                      $pq->where('program_code', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        $registrations = $query->orderBy('registered_at', 'desc')->paginate(15)->withQueryString();
        $programs = PtProgram::orderBy('program_code')->get();

        return view('admin.reports.index', compact('registrations', 'programs'));
    }

    public function individualReport($program_id, $registration_id)
    {
        $program = PtProgram::with('parameters')->findOrFail($program_id);
        $registration = ProgramRegistration::with(['lab', 'sample'])->where('program_id', $program->program_id)->findOrFail($registration_id);

        $lab = $registration->lab;
        $sample = $registration->sample;

        $parameterEvaluations = [];
        $chartParams = [];
        $chartZScores = [];
        $chartColors = [];

        foreach ($program->parameters as $param) {
            $allObs = Observation::where('parameter_id', $param->parameter_id)->pluck('result_value')->toArray();
            $stats = $this->statsCalculator->calculate($allObs);

            $labObs = Observation::where('parameter_id', $param->parameter_id)
                ->where('registration_id', $registration->registration_id)
                ->first();

            if ($labObs && is_numeric($labObs->result_value)) {
                $resultVal = floatval($labObs->result_value);
                $eval = $this->statsCalculator->calculateZScore($resultVal, $stats['robust_mean'], $stats['robust_sd']);

                $chartParams[] = $param->parameter_name;
                $chartZScores[] = $eval['z_score'];
                $chartColors[] = $eval['status'] === 'satisfactory' ? '#22c55e' : ($eval['status'] === 'warning' ? '#f59e0b' : '#ef4444');

                $parameterEvaluations[] = [
                    'parameter_name' => $param->parameter_name,
                    'test_method'    => $labObs->test_method,
                    'unit'           => $labObs->unit,
                    'reported_value' => $labObs->result_value,
                    'assigned_value' => $stats['robust_mean'],
                    'target_sd'      => $stats['robust_sd'],
                    'z_score'        => $eval['z_score'],
                    'status'         => $eval['status'],
                    'badge_class'    => $eval['badge_class'],
                    'label'          => $eval['label'],
                    'remarks'        => $labObs->remarks,
                ];
            }
        }

        $qrPayload = "ISO 17043 EVALUATION REPORT\nLAB: {$lab->laboratory_name}\nREG #: {$registration->registration_number}\nPROGRAM: {$program->program_code}\nSAMPLE ID: " . ($sample->sample_code ?? 'N/A') . "\nDATE: " . date('Y-m-d');

        return view('admin.reports.individual', compact(
            'program',
            'registration',
            'lab',
            'sample',
            'parameterEvaluations',
            'chartParams',
            'chartZScores',
            'chartColors',
            'qrPayload'
        ));
    }

    public function certificate($program_id, $registration_id)
    {
        $program = PtProgram::findOrFail($program_id);
        $registration = ProgramRegistration::with(['lab', 'sample'])->where('program_id', $program->program_id)->findOrFail($registration_id);

        $lab = $registration->lab;
        $sample = $registration->sample;

        $qrPayload = "ISO 17043 CERTIFICATE OF PARTICIPATION\nLABORATORY: {$lab->laboratory_name}\nNABL #: {$lab->nabl_certificate_number}\nPROGRAM: {$program->program_name} ({$program->program_code})\nISSUE DATE: " . date('Y-m-d');

        return view('admin.reports.certificate', compact('program', 'registration', 'lab', 'sample', 'qrPayload'));
    }

    public function parameterReport($program_id, $parameter_id)
    {
        $program = PtProgram::findOrFail($program_id);
        $parameter = ProgramParameter::where('program_id', $program->program_id)->findOrFail($parameter_id);

        $observations = Observation::where('parameter_id', $parameter->parameter_id)
            ->with(['lab', 'sample'])
            ->orderBy('submitted_at')
            ->get();

        $rawValues = $observations->pluck('result_value')->toArray();
        $stats = $this->statsCalculator->calculate($rawValues);

        $participantEvaluations = [];
        foreach ($observations as $obs) {
            $val = floatval($obs->result_value);
            $eval = $this->statsCalculator->calculateZScore($val, $stats['robust_mean'], $stats['robust_sd']);
            $participantEvaluations[] = array_merge(['obs' => $obs], $eval);
        }

        return view('admin.reports.parameter', compact('program', 'parameter', 'stats', 'participantEvaluations'));
    }

    public function masterReport($program_id)
    {
        $program = PtProgram::with(['parameters', 'registrations.lab', 'registrations.sample'])->findOrFail($program_id);

        $parameters = $program->parameters;
        $paramStats = [];

        foreach ($parameters as $param) {
            $vals = Observation::where('parameter_id', $param->parameter_id)->pluck('result_value')->toArray();
            $paramStats[$param->parameter_id] = $this->statsCalculator->calculate($vals);
        }

        $matrix = [];
        foreach ($program->registrations as $reg) {
            $row = [
                'reg' => $reg,
                'lab' => $reg->lab,
                'sample' => $reg->sample,
                'evaluations' => [],
            ];

            foreach ($parameters as $param) {
                $obs = Observation::where('parameter_id', $param->parameter_id)
                    ->where('registration_id', $reg->registration_id)
                    ->first();

                if ($obs && is_numeric($obs->result_value)) {
                    $st = $paramStats[$param->parameter_id];
                    $eval = $this->statsCalculator->calculateZScore(floatval($obs->result_value), $st['robust_mean'], $st['robust_sd']);
                    $row['evaluations'][$param->parameter_id] = $eval;
                } else {
                    $row['evaluations'][$param->parameter_id] = null;
                }
            }

            $matrix[] = $row;
        }

        return view('admin.reports.master', compact('program', 'parameters', 'paramStats', 'matrix'));
    }
}
