<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PtProgram;
use App\Models\ProgramParameter;
use App\Models\Observation;
use App\Services\StatsCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function freezeSchemeStats($program_id)
    {
        $program = PtProgram::with(['parameters', 'registrations.lab'])->findOrFail($program_id);

        // 1. Loop through parameters and save ISO 13528 stats into `statistical_results` table
        foreach ($program->parameters as $parameter) {
            $obsList = Observation::where('parameter_id', $parameter->parameter_id)->get();
            $rawValues = $obsList->pluck('result_value')->toArray();

            $stats = $this->statsCalculator->calculate($rawValues);

            DB::table('statistical_results')->updateOrInsert(
                [
                    'program_id' => $program->program_id,
                    'parameter_id' => $parameter->parameter_id,
                ],
                [
                    'mean' => $stats['mean'],
                    'median' => $stats['median'],
                    'standard_deviation' => $stats['std_dev'],
                    'robust_mean' => $stats['robust_mean'],
                    'robust_standard_deviation' => $stats['robust_sd'],
                    'assigned_value' => $stats['robust_mean'],
                    'calculated_at' => now(),
                ]
            );

            $statRecord = DB::table('statistical_results')
                ->where('program_id', $program->program_id)
                ->where('parameter_id', $parameter->parameter_id)
                ->first();

            // Populate `z_scores` child table for each observation
            foreach ($obsList as $obs) {
                $eval = $this->statsCalculator->calculateZScore((float)$obs->result_value, $stats['robust_mean'], $stats['robust_sd']);

                DB::table('z_scores')->updateOrInsert(
                    [
                        'observation_id' => $obs->observation_id,
                        'stat_id' => $statRecord->stat_id,
                    ],
                    [
                        'z_score' => $eval['z_score'],
                        'performance_status' => strtolower($eval['status']),
                        'created_at' => now(),
                    ]
                );
            }

            // Lock parameter observations
            Observation::where('parameter_id', $parameter->parameter_id)->update(['is_locked' => 1]);
        }

        // 2. Loop through registrations and generate static report snapshot entries in `reports` table
        foreach ($program->registrations as $registration) {
            DB::table('reports')->updateOrInsert(
                [
                    'program_id' => $program->program_id,
                    'registration_id' => $registration->registration_id,
                    'report_type' => 'individual',
                ],
                [
                    'file_path' => "uploads/reports/individual_report_P{$program->program_id}_R{$registration->registration_id}.pdf",
                    'qr_code' => "QR-IND-PROG{$program->program_id}-REG{$registration->registration_id}",
                    'digital_signature' => "SHA256-SIG-" . strtoupper(md5("PROG{$program->program_id}REG{$registration->registration_id}KEY")),
                    'generated_at' => now(),
                ]
            );
        }

        // Master Report Snapshot
        DB::table('reports')->updateOrInsert(
            [
                'program_id' => $program->program_id,
                'registration_id' => null,
                'report_type' => 'master',
            ],
            [
                'file_path' => "uploads/reports/master_report_P{$program->program_id}.pdf",
                'qr_code' => "QR-MASTER-PROG{$program->program_id}",
                'digital_signature' => "SHA256-SIG-MASTER-PROG{$program->program_id}",
                'generated_at' => now(),
            ]
        );

        // Parameter-wise Report Snapshots
        foreach ($program->parameters as $parameter) {
            DB::table('reports')->updateOrInsert(
                [
                    'program_id' => $program->program_id,
                    'parameter_id' => $parameter->parameter_id,
                    'report_type' => 'parameter',
                ],
                [
                    'file_path' => "uploads/reports/parameter_report_P{$program->program_id}_PM{$parameter->parameter_id}.pdf",
                    'qr_code' => "QR-PARAM-PROG{$program->program_id}-PARAM{$parameter->parameter_id}",
                    'digital_signature' => "SHA256-SIG-PARAM-P{$program->program_id}PM{$parameter->parameter_id}",
                    'generated_at' => now(),
                ]
            );
        }

        // Update Program Status to Completed & Registration Status to Closed
        $program->update([
            'program_status' => 'completed',
            'registration_status' => 'closed',
        ]);

        return back()->with('success', "Scheme '{$program->program_code}' successfully frozen! Statistical results & ISO 17043 report records locked into database.");
    }
}
