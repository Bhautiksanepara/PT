<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PtProgram;
use App\Models\ProgramParameter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PtProgramController extends Controller
{
    public function index(Request $request)
    {
        $query = PtProgram::withCount(['parameters', 'registrations']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('program_code', 'like', "%{$search}%")
                  ->orWhere('program_name', 'like', "%{$search}%")
                  ->orWhere('discipline', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('program_status', $request->status);
        }

        if ($request->filled('registration_status')) {
            $query->where('registration_status', $request->registration_status);
        }

        $programs = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('admin.programs.index', compact('programs'));
    }

    public function create()
    {
        return view('admin.programs.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'program_code' => 'required|string|max:50|unique:pt_programs,program_code',
            'program_name' => 'required|string|max:255',
            'discipline' => 'nullable|string|max:150',
            'scheme_code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'program_fee' => 'required|numeric|min:0',
            'registration_start_date' => 'nullable|date',
            'registration_end_date' => 'nullable|date|after_or_equal:registration_start_date',
            'dispatch_date' => 'nullable|date',
            'submission_deadline' => 'nullable|date',
            'report_date' => 'nullable|date',
            'registration_status' => 'required|in:upcoming,active,closed',
            'program_status' => 'required|in:draft,open,closed,completed',
            'parameters' => 'required|array|min:1',
            'parameters.*.parameter_name' => 'required|string|max:150',
            'parameters.*.test_method' => 'nullable|string|max:150',
            'parameters.*.unit' => 'nullable|string|max:50',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $program = PtProgram::create([
                'program_code' => $validated['program_code'],
                'program_name' => $validated['program_name'],
                'discipline' => $validated['discipline'] ?? null,
                'scheme_code' => $validated['scheme_code'] ?? null,
                'description' => $validated['description'] ?? null,
                'program_fee' => $validated['program_fee'],
                'registration_start_date' => $validated['registration_start_date'] ?? null,
                'registration_end_date' => $validated['registration_end_date'] ?? null,
                'dispatch_date' => $validated['dispatch_date'] ?? null,
                'submission_deadline' => $validated['submission_deadline'] ?? null,
                'report_date' => $validated['report_date'] ?? null,
                'registration_status' => $validated['registration_status'],
                'program_status' => $validated['program_status'],
                'created_by' => Auth::guard('admin')->id(),
            ]);

            foreach ($validated['parameters'] as $paramData) {
                if (!empty($paramData['parameter_name'])) {
                    ProgramParameter::create([
                        'program_id' => $program->program_id,
                        'parameter_name' => $paramData['parameter_name'],
                        'test_method' => $paramData['test_method'] ?? null,
                        'unit' => $paramData['unit'] ?? null,
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
        return view('admin.programs.edit', compact('program'));
    }

    public function update(Request $request, $id)
    {
        $program = PtProgram::findOrFail($id);

        $validated = $request->validate([
            'program_code' => 'required|string|max:50|unique:pt_programs,program_code,' . $id . ',program_id',
            'program_name' => 'required|string|max:255',
            'discipline' => 'nullable|string|max:150',
            'scheme_code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'program_fee' => 'required|numeric|min:0',
            'registration_start_date' => 'nullable|date',
            'registration_end_date' => 'nullable|date',
            'dispatch_date' => 'nullable|date',
            'submission_deadline' => 'nullable|date',
            'report_date' => 'nullable|date',
            'registration_status' => 'required|in:upcoming,active,closed',
            'program_status' => 'required|in:draft,open,closed,completed',
            'parameters' => 'required|array|min:1',
            'parameters.*.parameter_name' => 'required|string|max:150',
            'parameters.*.test_method' => 'nullable|string|max:150',
            'parameters.*.unit' => 'nullable|string|max:50',
        ]);

        DB::transaction(function () use ($program, $validated) {
            $program->update([
                'program_code' => $validated['program_code'],
                'program_name' => $validated['program_name'],
                'discipline' => $validated['discipline'] ?? null,
                'scheme_code' => $validated['scheme_code'] ?? null,
                'description' => $validated['description'] ?? null,
                'program_fee' => $validated['program_fee'],
                'registration_start_date' => $validated['registration_start_date'] ?? null,
                'registration_end_date' => $validated['registration_end_date'] ?? null,
                'dispatch_date' => $validated['dispatch_date'] ?? null,
                'submission_deadline' => $validated['submission_deadline'] ?? null,
                'report_date' => $validated['report_date'] ?? null,
                'registration_status' => $validated['registration_status'],
                'program_status' => $validated['program_status'],
            ]);

            // Sync Parameters: remove old and insert updated ones
            ProgramParameter::where('program_id', $program->program_id)->delete();

            foreach ($validated['parameters'] as $paramData) {
                if (!empty($paramData['parameter_name'])) {
                    ProgramParameter::create([
                        'program_id' => $program->program_id,
                        'parameter_name' => $paramData['parameter_name'],
                        'test_method' => $paramData['test_method'] ?? null,
                        'unit' => $paramData['unit'] ?? null,
                        'created_at' => now(),
                    ]);
                }
            }
        });

        return redirect()->route('admin.programs.index')->with('success', 'PT Program updated successfully!');
    }

    public function close($id)
    {
        $program = PtProgram::findOrFail($id);
        $program->update([
            'program_status' => 'closed',
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
}
