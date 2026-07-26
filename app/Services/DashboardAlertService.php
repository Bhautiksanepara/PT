<?php

namespace App\Services;

use App\Models\DashboardAlertRead;
use App\Models\ProgramRegistration;
use App\Models\PtProgram;
use App\Models\Report;
use App\Models\Sample;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardAlertService
{
    public function adminAlerts(): Collection
    {
        $today = today();
        $reminderDate = $today->copy()->addDays(2)->toDateString();
        $alerts = collect();

        $programs = PtProgram::whereNotNull('dispatch_date')
            ->whereDate('dispatch_date', '<=', $reminderDate)
            ->orderBy('dispatch_date')
            ->get();

        $pendingSamplesByProgram = Sample::with('dispatch')
            ->whereIn('program_id', $programs->pluck('program_id'))
            ->get()
            ->filter(fn ($sample) => !$sample->dispatch)
            ->groupBy('program_id');

        $programs->each(function (PtProgram $program) use ($alerts, $pendingSamplesByProgram) {
                $pendingSamples = $pendingSamplesByProgram->get($program->program_id, collect());
                if ($pendingSamples->isEmpty()) {
                    return;
                }

                $sampleCodes = $pendingSamples->pluck('sample_code')->filter()->implode(', ');
                $alerts->push([
                    'type' => 'warning',
                    'icon' => 'bx-package',
                    'title' => 'Samples pending dispatch',
                    'message' => "{$program->program_code}: {$pendingSamples->count()} sample(s) pending — {$sampleCodes}.",
                    'url' => route('admin.dispatches.program', $program->program_id),
                ]);
            });

        PtProgram::whereNotNull('report_date')
            ->whereDate('report_date', '<=', $reminderDate)
            ->orderBy('report_date')
            ->get()
            ->each(function (PtProgram $program) use ($alerts) {
                if (Report::where('program_id', $program->program_id)->exists()) {
                    return;
                }

                $alerts->push([
                    'type' => 'info',
                    'icon' => 'bx-line-chart',
                    'title' => 'Report release pending',
                    'message' => "Review and release the statistical report for {$program->program_code} — {$program->program_name}.",
                    'url' => route('admin.stats.index'),
                ]);
            });

        return $alerts;
    }

    public function labAlerts(int $labId): Collection
    {
        $today = today();
        $submissionAlertDate = $today->copy()->addDays(2)->toDateString();
        $readReports = DashboardAlertRead::where('lab_id', $labId)
            ->where('alert_type', 'report_released')
            ->get()
            ->keyBy('program_id');

        return ProgramRegistration::with(['program.parameters', 'registeredParameters', 'observations', 'reports', 'sample.dispatch'])
            ->where('lab_id', $labId)
            ->orderBy('registered_at', 'desc')
            ->get()
            ->flatMap(function (ProgramRegistration $registration) use ($submissionAlertDate, $readReports) {
                $program = $registration->program;
                if (!$program) {
                    return [];
                }

                $alerts = [];

                $registeredParameters = $registration->registeredParameters->isNotEmpty()
                    ? $registration->registeredParameters
                    : $program->parameters;
                $submittedParameterIds = $registration->observations->pluck('parameter_id')->unique();
                $pendingParameters = $registeredParameters->whereNotIn('parameter_id', $submittedParameterIds);
                $deadline = $program->submission_deadline ? Carbon::parse($program->submission_deadline)->toDateString() : null;

                if ($deadline && $deadline <= $submissionAlertDate
                    && !in_array($program->program_status, ['forcefully_closed', 'completed'])
                    && $pendingParameters->isNotEmpty()) {
                    $parameterNames = $pendingParameters->pluck('parameter_name')->implode(', ');
                    $alerts[] = [
                        'type' => 'warning',
                        'icon' => 'bx-time-five',
                        'title' => 'Result submission pending',
                        'message' => "{$program->program_code}: submit {$pendingParameters->count()} remaining parameter(s) — {$parameterNames}.",
                        'url' => route('user.observations.form', $registration->registration_id),
                    ];
                }

                // Dispatch alert: sample dispatched but lab hasn't confirmed receipt yet
                $sample = $registration->sample;
                if ($sample && $sample->dispatch && !$sample->dispatch->received_at) {
                    $alerts[] = [
                        'type'    => 'info',
                        'icon'    => 'bx-package',
                        'title'   => 'Sample dispatched — confirm receipt',
                        'message' => "{$program->program_code}: Sample {$sample->sample_code} dispatched via {$sample->dispatch->courier_name} (Tracking: {$sample->dispatch->tracking_number}). Please confirm receipt.",
                        'url'     => route('user.dispatches.index'),
                    ];
                }

                // Report released alert: only when THIS registration has its own individual report generated
                $thisRegistrationReport = $registration->reports->first();
                if ($thisRegistrationReport) {
                    // Suppress only if lab already read it AFTER this report was generated
                    $readRecord = $readReports->get($program->program_id);
                    $alreadyRead = $readRecord && $readRecord->read_at >= $thisRegistrationReport->generated_at;

                    if (!$alreadyRead) {
                        $alerts[] = [
                            'type'    => 'success',
                            'icon'    => 'bx-file',
                            'title'   => 'PT report released',
                            'message' => "Your report for {$program->program_code} — {$program->program_name} is now available.",
                            'url'     => route('user.reports.index'),
                        ];
                    }
                }

                return $alerts;
            })
            ->values();
    }

    /**
     * Mark the report alert as read for a specific lab + program (called when lab views the individual report).
     */
    public function markReleasedReportAlertAsRead(int $labId, int $programId): void
    {
        DashboardAlertRead::updateOrCreate(
            ['lab_id' => $labId, 'program_id' => $programId, 'alert_type' => 'report_released'],
            ['read_at' => now()]
        );
    }

    /**
     * Mark all released report alerts as read for a lab (legacy bulk method).
     */
    public function markReleasedReportAlertsAsRead(int $labId): void
    {
        ProgramRegistration::where('lab_id', $labId)
            ->whereHas('reports')
            ->pluck('program_id')
            ->unique()
            ->each(fn ($programId) => DashboardAlertRead::updateOrCreate(
                ['lab_id' => $labId, 'program_id' => $programId, 'alert_type' => 'report_released'],
                ['read_at' => now()]
            ));
    }
}
