<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PtProgram;
use App\Models\ProgramParameter;
use App\Models\Observation;
use App\Services\StatsCalculatorService;
use App\Http\Controllers\Admin\StatisticalEngineController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PtProgramController extends Controller
{
    public function index(Request $request)
    {
        $query = PtProgram::with(['discipline'])->withCount(['parameters', 'registrations']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('program_code', 'like', "%{$search}%")
                  ->orWhere('program_name', 'like', "%{$search}%")
                  ->orWhereHas('discipline', function ($dq) use ($search) {
                      $dq->where('discipline_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $status = $request->status;
            $today = date('Y-m-d');
            if ($status === 'draft') {
                $query->where('program_status', 'draft');
            } elseif ($status === 'reopen') {
                $query->where('program_status', 'reopen');
            } elseif ($status === 'forcefully_closed') {
                $query->where('program_status', 'forcefully_closed');
            } elseif ($status === 'open') {
                $query->where(function($q) use ($today) {
                    $q->where('program_status', 'open')
                      ->orWhere(function($sub) use ($today) {
                          $sub->whereNull('program_status')
                              ->whereNotNull('registration_start_date')
                              ->whereNotNull('registration_end_date')
                              ->where('registration_start_date', '<=', $today)
                              ->where('registration_end_date', '>=', $today);
                      });
                });
            } elseif ($status === 'completed') {
                $query->where(function($q) use ($today) {
                    $q->where('program_status', 'completed')
                      ->orWhere(function($sub) use ($today) {
                          $sub->whereNull('program_status')
                              ->whereNotNull('submission_deadline')
                              ->where('submission_deadline', '<', $today);
                      });
                });
            }
        }

        if ($request->filled('registration_status')) {
            $query->where('registration_status', $request->registration_status);
        }

        $programs = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('admin.programs.index', compact('programs'));
    }

    public function create()
    {
        $disciplines = \App\Models\DisciplineMaster::with('parameters')->orderBy('discipline_name')->get();
        return view('admin.programs.create', compact('disciplines'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'program_code' => 'required|string|max:50|unique:pt_programs,program_code',
            'program_name' => 'required|string|max:255',
            'discipline_id' => 'required|string',
            'custom_discipline' => 'required_if:discipline_id,other|nullable|string|max:150',
            'custom_short_code' => 'required_if:discipline_id,other|nullable|string|max:10',
            'scheme_code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'program_fee' => 'required|numeric|min:0',
            'registration_start_date' => 'nullable|date',
            'registration_end_date' => 'nullable|date|after_or_equal:registration_start_date',
            'dispatch_date' => 'nullable|date',
            'submission_deadline' => 'nullable|date',
            'report_date' => 'nullable|date',
            'program_status' => 'nullable|in:draft,reopen,forcefully_closed',
            'parameters' => 'required|array|min:1',
            'parameters.*.parameter_name' => 'required|string|max:150',
            'parameters.*.test_method' => 'nullable|string|max:150',
            'parameters.*.unit' => 'nullable|string|max:50',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $disciplineId = $validated['discipline_id'];
            if ($disciplineId === 'other') {
                $disciplineName = trim($validated['custom_discipline']);
                $shortCode = strtoupper(trim($validated['custom_short_code']));
                
                $disc = \App\Models\DisciplineMaster::firstOrCreate(
                    ['discipline_name' => $disciplineName],
                    ['short_code' => $shortCode]
                );
                $disciplineId = $disc->id;
            }

            $program = PtProgram::create([
                'program_code' => $validated['program_code'],
                'program_name' => $validated['program_name'],
                'discipline_id' => $disciplineId,
                'scheme_code' => $validated['scheme_code'] ?? null,
                'description' => $validated['description'] ?? null,
                'program_fee' => $validated['program_fee'],
                'registration_start_date' => $validated['registration_start_date'] ?? null,
                'registration_end_date' => $validated['registration_end_date'] ?? null,
                'dispatch_date' => $validated['dispatch_date'] ?? null,
                'submission_deadline' => $validated['submission_deadline'] ?? null,
                'report_date' => $validated['report_date'] ?? null,
                'program_status' => $validated['program_status'] ?: null,
                'created_by' => Auth::guard('admin')->id(),
            ]);

            foreach ($validated['parameters'] as $paramData) {
                if (!empty($paramData['parameter_name'])) {
                    $paramName = trim($paramData['parameter_name']);
                    $testMethod = trim($paramData['test_method'] ?? '');
                    $unit = trim($paramData['unit'] ?? '');

                    // Auto save to master parameters
                    \App\Models\ParameterMaster::firstOrCreate([
                        'discipline_id' => $disciplineId,
                        'parameter_name' => $paramName,
                        'test_method' => $testMethod ?: null,
                        'unit' => $unit ?: null,
                    ]);

                    ProgramParameter::create([
                        'program_id' => $program->program_id,
                        'parameter_name' => $paramName,
                        'test_method' => $testMethod ?: null,
                        'unit' => $unit ?: null,
                        'created_at' => now(),
                    ]);
                }
            }
        });

        return redirect()->route('admin.programs.index')->with('success', 'PT Program created successfully!');
    }

    public function show($id)
    {
        $program = PtProgram::with(['parameters', 'creator', 'registrations.lab'])->findOrFail($id);
        return view('admin.programs.show', compact('program'));
    }

    public function edit($id)
    {
        $program = PtProgram::with('parameters')->findOrFail($id);
        $disciplines = \App\Models\DisciplineMaster::with('parameters')->orderBy('discipline_name')->get();
        return view('admin.programs.edit', compact('program', 'disciplines'));
    }

    public function update(Request $request, $id)
    {
        $program = PtProgram::findOrFail($id);

        $validated = $request->validate([
            'program_code' => 'required|string|max:50|unique:pt_programs,program_code,' . $id . ',program_id',
            'program_name' => 'required|string|max:255',
            'discipline_id' => 'required|string',
            'custom_discipline' => 'required_if:discipline_id,other|nullable|string|max:150',
            'custom_short_code' => 'required_if:discipline_id,other|nullable|string|max:10',
            'scheme_code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'program_fee' => 'required|numeric|min:0',
            'registration_start_date' => 'nullable|date',
            'registration_end_date' => 'nullable|date',
            'dispatch_date' => 'nullable|date',
            'submission_deadline' => 'nullable|date',
            'report_date' => 'nullable|date',
            'program_status' => 'nullable|in:draft,reopen,forcefully_closed',
            'parameters' => 'required|array|min:1',
            'parameters.*.parameter_name' => 'required|string|max:150',
            'parameters.*.test_method' => 'nullable|string|max:150',
            'parameters.*.unit' => 'nullable|string|max:50',
        ]);

        DB::transaction(function () use ($program, $validated) {
            $disciplineId = $validated['discipline_id'];
            if ($disciplineId === 'other') {
                $disciplineName = trim($validated['custom_discipline']);
                $shortCode = strtoupper(trim($validated['custom_short_code']));
                
                $disc = \App\Models\DisciplineMaster::firstOrCreate(
                    ['discipline_name' => $disciplineName],
                    ['short_code' => $shortCode]
                );
                $disciplineId = $disc->id;
            }

            $program->update([
                'program_code' => $validated['program_code'],
                'program_name' => $validated['program_name'],
                'discipline_id' => $disciplineId,
                'scheme_code' => $validated['scheme_code'] ?? null,
                'description' => $validated['description'] ?? null,
                'program_fee' => $validated['program_fee'],
                'registration_start_date' => $validated['registration_start_date'] ?? null,
                'registration_end_date' => $validated['registration_end_date'] ?? null,
                'dispatch_date' => $validated['dispatch_date'] ?? null,
                'submission_deadline' => $validated['submission_deadline'] ?? null,
                'report_date' => $validated['report_date'] ?? null,
                'program_status' => $validated['program_status'] ?: null,
            ]);

            // Sync Parameters: remove old and insert updated ones if observations don't exist
            $hasObservations = Observation::whereIn('parameter_id', ProgramParameter::where('program_id', $program->program_id)->pluck('parameter_id'))->exists();

            if (!$hasObservations) {
                ProgramParameter::where('program_id', $program->program_id)->delete();

                foreach ($validated['parameters'] as $paramData) {
                    if (!empty($paramData['parameter_name'])) {
                        $paramName = trim($paramData['parameter_name']);
                        $testMethod = trim($paramData['test_method'] ?? '');
                        $unit = trim($paramData['unit'] ?? '');

                        // Auto save to master parameters
                        \App\Models\ParameterMaster::firstOrCreate([
                            'discipline_id' => $disciplineId,
                            'parameter_name' => $paramName,
                            'test_method' => $testMethod ?: null,
                            'unit' => $unit ?: null,
                        ]);

                        ProgramParameter::create([
                            'program_id' => $program->program_id,
                            'parameter_name' => $paramName,
                            'test_method' => $testMethod ?: null,
                            'unit' => $unit ?: null,
                            'created_at' => now(),
                        ]);
                    }
                }
            }
        });

        // Auto-freeze statistics and lock tables if program status is forcefully_closed
        if ($validated['program_status'] === 'forcefully_closed') {
            $statsEngine = new StatisticalEngineController(new StatsCalculatorService());
            $statsEngine->freezeSchemeStats($program->program_id);
        }

        return redirect()->route('admin.programs.index')->with('success', 'PT Program updated successfully!');
    }

    public function close($id)
    {
        $program = PtProgram::findOrFail($id);
        $program->update([
            'program_status' => 'forcefully_closed',
            'registration_status' => 'closed',
        ]);

        return back()->with('success', "Program {$program->program_code} has been closed.");
    }

    public function toggleRegistrationStatus($id)
    {
        $program = PtProgram::findOrFail($id);
        $newStatus = $program->registration_status === 'active' ? 'closed' : 'active';
        
        $program->update(['registration_status' => $newStatus]);

        $message = $newStatus === 'active' ? "Registration window opened for {$program->program_code}." : "Registration window closed for {$program->program_code}.";
        return back()->with('success', $message);
    }

    public function getNextCode(Request $request)
    {
        $disciplineId = $request->query('discipline_id');
        $year = $request->query('year', date('Y'));

        $shortCode = 'TEMP';
        if (is_numeric($disciplineId)) {
            $disc = \App\Models\DisciplineMaster::find($disciplineId);
            if ($disc) {
                $shortCode = $disc->short_code;
            }
        } elseif ($request->filled('custom_short_code')) {
            $shortCode = strtoupper(trim($request->custom_short_code));
        }

        $count = PtProgram::where('discipline_id', $disciplineId)
            ->where(function($q) use ($year) {
                $q->whereYear('registration_start_date', $year)
                  ->orWhereYear('created_at', $year);
            })
            ->count() + 1;

        do {
            $sequence = str_pad($count, 2, '0', STR_PAD_LEFT);
            $nextCode = "PT-{$shortCode}-{$year}-{$sequence}";
            $count++;
        } while (PtProgram::where('program_code', $nextCode)->exists());

        return response()->json([
            'next_code' => $nextCode,
        ]);
    }
}
