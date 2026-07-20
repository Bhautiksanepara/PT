<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PtProgram;
use App\Models\ProgramParameter;
use App\Models\Observation;
use App\Services\StatsCalculatorService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StatisticalEngineController extends Controller
{
    protected $statsCalculator;

    public function __construct(StatsCalculatorService $statsCalculator)
    {
        $this->statsCalculator = $statsCalculator;
    }

    public function index(Request $request)
    {
        $query = PtProgram::with(['parameters.observations']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('program_code', 'like', "%{$search}%")
                  ->orWhere('program_name', 'like', "%{$search}%");
            });
        }

        $programs = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('admin.stats.index', compact('programs'));
    }

    public function parameterStats($program_id, $parameter_id)
    {
        $program = PtProgram::findOrFail($program_id);
        $parameter = ProgramParameter::where('program_id', $program->program_id)->findOrFail($parameter_id);

        $observations = Observation::where('parameter_id', $parameter->parameter_id)
            ->with(['lab', 'sample'])
            ->orderBy('submitted_at')
            ->get();

        // Extract numeric results
        $rawValues = $observations->pluck('result_value')->toArray();

        // Calculate ISO 13528 Statistics
        $stats = $this->statsCalculator->calculate($rawValues);

        $assignedValue = $stats['robust_mean'];
        $targetSd = $stats['robust_sd'];

        // Evaluate Z-Score for each participant
        $participantResults = [];
        $chartLabs = [];
        $chartZScores = [];
        $chartColors = [];

        $satisfactoryCount = 0;
        $warningCount = 0;
        $actionCount = 0;

        foreach ($observations as $obs) {
            $val = floatval($obs->result_value);
            $eval = $this->statsCalculator->calculateZScore($val, $assignedValue, $targetSd);

            if ($eval['status'] === 'satisfactory') {
                $satisfactoryCount++;
                $chartColors[] = '#22c55e'; // Green
            } elseif ($eval['status'] === 'warning') {
                $warningCount++;
                $chartColors[] = '#f59e0b'; // Yellow
            } else {
                $actionCount++;
                $chartColors[] = '#ef4444'; // Red
            }

            $labName = $obs->lab->laboratory_name ?? 'Unknown Lab';
            $chartLabs[] = $labName;
            $chartZScores[] = $eval['z_score'];

            $participantResults[] = [
                'observation_id' => $obs->observation_id,
                'sample_code'    => $obs->sample->sample_code ?? 'N/A',
                'lab_name'       => $labName,
                'email'          => $obs->lab->email ?? '',
                'test_method'    => $obs->test_method,
                'result_value'   => $obs->result_value,
                'unit'           => $obs->unit,
                'z_score'        => $eval['z_score'],
                'status'         => $eval['status'],
                'badge_class'    => $eval['badge_class'],
                'label'          => $eval['label'],
                'submitted_at'   => $obs->submitted_at,
            ];
        }

        $summaryCounts = [
            'satisfactory' => $satisfactoryCount,
            'warning'      => $warningCount,
            'action'       => $actionCount,
            'pass_rate'    => $stats['count'] > 0 ? round(($satisfactoryCount / $stats['count']) * 100, 1) : 0,
        ];

        return view('admin.stats.parameter', compact(
            'program',
            'parameter',
            'stats',
            'participantResults',
            'summaryCounts',
            'chartLabs',
            'chartZScores',
            'chartColors'
        ));
    }

    public function exportCsv($program_id, $parameter_id)
    {
        $program = PtProgram::findOrFail($program_id);
        $parameter = ProgramParameter::where('program_id', $program->program_id)->findOrFail($parameter_id);

        $observations = Observation::where('parameter_id', $parameter->parameter_id)
            ->with(['lab', 'sample'])
            ->orderBy('submitted_at')
            ->get();

        $rawValues = $observations->pluck('result_value')->toArray();
        $stats = $this->statsCalculator->calculate($rawValues);

        $assignedValue = $stats['robust_mean'];
        $targetSd = $stats['robust_sd'];

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=statistical_report_{$program->program_code}_{$parameter->parameter_name}_" . date('Y-m-d') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($program, $parameter, $stats, $observations, $assignedValue, $targetSd) {
            $file = fopen('php://output', 'w');

            // Header info
            fputcsv($file, ['ISO 17043 PROFICIENCY TESTING STATISTICAL REPORT']);
            fputcsv($file, ['Program Code', $program->program_code]);
            fputcsv($file, ['Program Name', $program->program_name]);
            fputcsv($file, ['Parameter Name', $parameter->parameter_name]);
            fputcsv($file, ['Standard Used', 'ISO 13528 Algorithm A']);
            fputcsv($file, []);

            // Summary Stats Block
            fputcsv($file, ['STATISTICAL SUMMARY METRICS']);
            fputcsv($file, ['Metric', 'Value']);
            fputcsv($file, ['Total Submissions (n)', $stats['count']]);
            fputcsv($file, ['Classical Mean', $stats['mean']]);
            fputcsv($file, ['Median', $stats['median']]);
            fputcsv($file, ['Classical Standard Deviation (SD)', $stats['std_dev']]);
            fputcsv($file, ['ISO 13528 Robust Mean (x*)', $stats['robust_mean']]);
            fputcsv($file, ['ISO 13528 Robust SD (s*)', $stats['robust_sd']]);
            fputcsv($file, ['Minimum Result', $stats['min']]);
            fputcsv($file, ['Maximum Result', $stats['max']]);
            fputcsv($file, []);

            // Participant Z-Scores Table
            fputcsv($file, ['PARTICIPANT Z-SCORE EVALUATION']);
            fputcsv($file, ['Sample Code', 'Laboratory Name', 'Test Method', 'Reported Result', 'Unit', 'Assigned Value (x*)', 'Target SD (s*)', 'Z-Score', 'Evaluation Performance']);

            foreach ($observations as $obs) {
                $val = floatval($obs->result_value);
                $eval = $this->statsCalculator->calculateZScore($val, $assignedValue, $targetSd);

                fputcsv($file, [
                    $obs->sample->sample_code ?? 'N/A',
                    $obs->lab->laboratory_name ?? 'N/A',
                    $obs->test_method,
                    $obs->result_value,
                    $obs->unit,
                    $assignedValue,
                    $targetSd,
                    $eval['z_score'],
                    strtoupper($eval['status'])
                ]);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
