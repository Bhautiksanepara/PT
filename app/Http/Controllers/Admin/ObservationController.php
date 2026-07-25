<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Observation;
use App\Models\PtProgram;
use App\Models\ProgramParameter;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ObservationController extends Controller
{
    public function index(Request $request)
    {
        $query = Observation::with(['sample', 'registration', 'lab', 'parameter.program', 'files']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('result_value', 'like', "%{$search}%")
                  ->orWhere('test_method', 'like', "%{$search}%")
                  ->orWhereHas('lab', function ($lq) use ($search) {
                      $lq->where('laboratory_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('sample', function ($sq) use ($search) {
                      $sq->where('sample_code', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('program_id')) {
            $query->whereHas('parameter', function ($pq) use ($request) {
                $pq->where('program_id', $request->program_id);
            });
        }

        if ($request->filled('parameter_id')) {
            $query->where('parameter_id', $request->parameter_id);
        }

        $observations = $query->orderBy('submitted_at', 'desc')->paginate(10)->withQueryString();
        $programs = PtProgram::orderBy('program_code')->get();

        return view('admin.observations.index', compact('observations', 'programs'));
    }

    public function show($observation_id)
    {
        $observation = Observation::with([
            'sample',
            'registration.program.parameters',
            'lab',
            'parameter.program',
            'files'
        ])->findOrFail($observation_id);

        // Fetch all other observations submitted for this registration
        $allObservations = Observation::with('parameter')
            ->where('registration_id', $observation->registration_id)
            ->get()
            ->keyBy('parameter_id');

        return view('admin.observations.show', compact('observation', 'allObservations'));
    }

    public function update(Request $request, $observation_id)
    {
        $observation = Observation::findOrFail($observation_id);

        $validated = $request->validate([
            'result_value' => 'required|string|max:100',
            'test_method'  => 'required|string|max:150',
            'unit'         => 'required|string|max:50',
            'uncertainty'  => 'nullable|string|max:50',
            'remarks'      => 'nullable|string',
            'is_locked'    => 'required|boolean',
        ]);

        $observation->update($validated);

        return back()->with('success', 'Observation result updated successfully!');
    }

    /**
     * Bulk update all observation rows for a registration at once.
     * Also handles creating NEW observations on behalf of labs (admin entry).
     *
     * Payload:
     *   observations[obs_id][field] => update existing rows
     *   new[parameter_id][field]    => create new rows for unsubmitted params
     */
    public function bulkUpdate(Request $request)
    {
        $rows      = $request->input('observations', []);
        $newRows   = $request->input('new', []);
        $redirectTo = $request->input('redirect_to', null);

        // ── 1. Update existing observation rows ──────────────────────────────
        foreach ($rows as $obsId => $data) {
            $obs = Observation::find((int) $obsId);
            if (!$obs) continue;

            $obs->result_value = $data['result_value'] ?? $obs->result_value;
            $obs->test_method  = $data['test_method']  ?? $obs->test_method;
            $obs->unit         = $data['unit']          ?? $obs->unit;
            $obs->uncertainty  = $data['uncertainty']   ?? null;
            $obs->remarks      = $data['remarks']       ?? null;
            $obs->is_locked    = isset($data['is_locked']) ? (bool)$data['is_locked'] : $obs->is_locked;
            $obs->save();

            if (!$redirectTo) {
                $redirectTo = $obsId;
            }
        }

        // ── 2. Create new observations on behalf of lab (Admin Entry) ────────
        foreach ($newRows as $parameterId => $data) {
            // Skip if admin left the result_value blank — nothing to save
            if (empty($data['result_value']) && empty($data['test_method'])) {
                continue;
            }

            $obs = Observation::updateOrCreate(
                [
                    'registration_id' => (int)($data['registration_id'] ?? 0),
                    'parameter_id'    => (int)$parameterId,
                ],
                [
                    'sample_id'    => (int)($data['sample_id']   ?? 0),
                    'lab_id'       => (int)($data['lab_id']       ?? 0),
                    'test_method'  => $data['test_method']         ?? null,
                    'result_value' => $data['result_value']        ?? null,
                    'unit'         => $data['unit']                ?? null,
                    'uncertainty'  => $data['uncertainty']         ?? null,
                    'remarks'      => $data['remarks']             ?? null,
                    'is_locked'    => isset($data['is_locked']) ? (bool)$data['is_locked'] : 0,
                    'submitted_at' => now(),
                ]
            );

            // Redirect to one of the newly created observations so the page reloads correctly
            if (!$redirectTo) {
                $redirectTo = $obs->observation_id;
            }
        }

        if (!$redirectTo) {
            return redirect()->route('admin.observations.index')
                ->with('success', 'No changes were saved.');
        }

        return redirect()->route('admin.observations.show', $redirectTo)
            ->with('success', 'All parameter observations saved successfully!');
    }

    public function exportCsv(Request $request)
    {
        $query = Observation::with(['sample', 'lab', 'parameter.program']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('result_value', 'like', "%{$search}%")
                  ->orWhereHas('lab', function ($lq) use ($search) {
                      $lq->where('laboratory_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('program_id')) {
            $query->whereHas('parameter', function ($pq) use ($request) {
                $pq->where('program_id', $request->program_id);
            });
        }

        $observations = $query->orderBy('submitted_at', 'desc')->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=observations_master_sheet_" . date('Y-m-d_H-i') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($observations) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'Observation ID',
                'Sample Code',
                'Program Code',
                'Laboratory Name',
                'Parameter Name',
                'Test Method',
                'Result Value',
                'Unit',
                'Status',
                'Submitted At'
            ]);

            foreach ($observations as $obs) {
                fputcsv($file, [
                    'OBS-' . str_pad($obs->observation_id, 4, '0', STR_PAD_LEFT),
                    $obs->sample->sample_code ?? 'N/A',
                    $obs->parameter->program->program_code ?? 'N/A',
                    $obs->lab->laboratory_name ?? 'N/A',
                    $obs->parameter->parameter_name ?? 'N/A',
                    $obs->test_method ?? 'N/A',
                    $obs->result_value ?? 'N/A',
                    $obs->unit ?? 'N/A',
                    $obs->is_locked ? 'Locked' : 'Submitted',
                    $obs->submitted_at ? \Carbon\Carbon::parse($obs->submitted_at)->format('Y-m-d H:i:s') : ''
                ]);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
