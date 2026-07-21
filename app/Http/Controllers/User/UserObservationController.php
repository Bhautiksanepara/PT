<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ProgramRegistration;
use App\Models\PtProgram;
use App\Models\Observation;
use App\Models\ObservationFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserObservationController extends Controller
{
    public function index(Request $request)
    {
        $lab = Auth::guard('lab')->user();

        $query = ProgramRegistration::with(['program.parameters', 'sample', 'observations'])
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

        $registrations = $query->orderBy('registered_at', 'desc')->paginate(10)->withQueryString();

        return view('user.observations.index', compact('lab', 'registrations'));
    }

    public function showForm($registration_id)
    {
        $lab = Auth::guard('lab')->user();

        $registration = ProgramRegistration::with(['program.parameters', 'sample'])
            ->where('lab_id', $lab->lab_id)
            ->findOrFail($registration_id);

        $program = $registration->program;
        $sample = $registration->sample;

        if (!$sample) {
            return redirect()->route('user.observations.index')->with('error', 'Sample code has not been assigned by Admin yet. Please wait for sample dispatch.');
        }

        // Deadline check
        $deadline = $program->submission_deadline ? \Carbon\Carbon::parse($program->submission_deadline) : null;
        $isPastDeadline = ($deadline && now()->greaterThan($deadline->endOfDay())) || ($program->program_status === 'completed');

        // Fetch existing observations
        $existingObservations = Observation::with('files')
            ->where('registration_id', $registration->registration_id)
            ->get()
            ->keyBy('parameter_id');

        return view('user.observations.form', compact(
            'registration',
            'program',
            'sample',
            'existingObservations',
            'isPastDeadline',
            'deadline'
        ));
    }

    public function store($registration_id, Request $request)
    {
        $lab = Auth::guard('lab')->user();

        $registration = ProgramRegistration::with(['program.parameters', 'sample'])
            ->where('lab_id', $lab->lab_id)
            ->findOrFail($registration_id);

        $program = $registration->program;
        $sample = $registration->sample;

        if (!$sample) {
            return redirect()->route('user.observations.index')->with('error', 'Sample code has not been assigned by Admin yet.');
        }

        // Deadline check
        $deadline = $program->submission_deadline ? \Carbon\Carbon::parse($program->submission_deadline) : null;
        if (($deadline && now()->greaterThan($deadline->endOfDay())) || ($program->program_status === 'completed')) {
            return redirect()->back()->with('error', 'Submission deadline has passed. This observation form is locked.');
        }

        $request->validate([
            'results' => 'required|array',
            'results.*.parameter_id' => 'required|integer',
            'results.*.result_value' => 'nullable|string',
            'results.*.test_method'  => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,xls,xlsx,doc,docx,jpg,jpeg,png|max:10240',
        ]);

        DB::transaction(function () use ($request, $registration, $sample, $lab) {
            $firstObsId = null;

            foreach ($request->results as $res) {
                if (!isset($res['parameter_id'])) continue;

                $obs = Observation::updateOrCreate(
                    [
                        'registration_id' => $registration->registration_id,
                        'parameter_id'    => $res['parameter_id'],
                    ],
                    [
                        'sample_id'    => $sample->sample_id,
                        'lab_id'       => $lab->lab_id,
                        'test_method'  => $res['test_method'] ?? null,
                        'result_value' => $res['result_value'] ?? null,
                        'unit'         => $res['unit'] ?? null,
                        'uncertainty'  => $res['uncertainty'] ?? null,
                        'remarks'      => $res['remarks'] ?? null,
                        'is_locked'    => 0, // Unlocked until deadline passes
                        'submitted_at' => now(),
                    ]
                );

                if (!$firstObsId) {
                    $firstObsId = $obs->observation_id;
                }
            }

            // Handle file attachment
            if ($request->hasFile('attachment') && $firstObsId) {
                $file = $request->file('attachment');
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
                $destinationPath = public_path('uploads/observations');

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                $file->move($destinationPath, $filename);
                $filePath = 'uploads/observations/' . $filename;

                ObservationFile::create([
                    'observation_id'    => $firstObsId,
                    'file_path'         => $filePath,
                    'original_filename' => $file->getClientOriginalName(),
                    'uploaded_at'        => now(),
                ]);
            }

            // Update sample status to tested
            $sample->update(['status' => 'tested']);
        });

        return redirect()->route('user.observations.index')->with('success', 'Test observations and raw data sheets submitted successfully!');
    }
}
