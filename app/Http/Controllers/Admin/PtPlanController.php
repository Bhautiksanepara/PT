<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PtProgram;
use App\Models\PtPlan;
use App\Models\AdminUser;
use Illuminate\Http\Request;

class PtPlanController extends Controller
{
    public function index()
    {
        $plans = PtPlan::with(['program', 'coordinator'])->orderBy('created_at', 'desc')->paginate(10);
        $programsWithoutPlans = PtProgram::doesntHave('plan')->orderBy('program_code')->get();
        return view('admin.plans.index', compact('plans', 'programsWithoutPlans'));
    }

    public function create($program_id)
    {
        $program = PtProgram::with(['parameters', 'plan'])->findOrFail($program_id);
        $coordinators = AdminUser::where('status', 'active')->orderBy('full_name')->get();
        $plan = $program->plan;

        return view('admin.plans.create', compact('program', 'coordinators', 'plan'));
    }

    public function store(Request $request, $program_id)
    {
        $program = PtProgram::findOrFail($program_id);

        $validated = $request->validate([
            'program_number' => 'required|string|max:100',
            'material' => 'required|string|max:255',
            'timeline' => 'nullable|string|max:255',
            'assigned_coordinator' => 'nullable|exists:admin_users,admin_id',
            'sample_quantity' => 'nullable|string|max:100',
            'sample_preparation_instructions' => 'nullable|string',
        ]);

        PtPlan::updateOrCreate(
            ['program_id' => $program->program_id],
            [
                'program_number' => $validated['program_number'],
                'material' => $validated['material'],
                'timeline' => $validated['timeline'] ?? null,
                'assigned_coordinator' => $validated['assigned_coordinator'] ?? null,
                'sample_quantity' => $validated['sample_quantity'] ?? null,
                'sample_preparation_instructions' => $validated['sample_preparation_instructions'] ?? null,
                'created_at' => now(),
            ]
        );

        return redirect()->route('admin.plans.show', $program->program_id)->with('success', 'Official PT Plan generated successfully!');
    }

    public function show($program_id)
    {
        $program = PtProgram::with(['parameters', 'plan.coordinator'])->findOrFail($program_id);

        if (!$program->plan) {
            return redirect()->route('admin.plans.create', $program->program_id)->with('info', 'No PT Plan exists for this program yet. Please create one.');
        }

        return view('admin.plans.show', compact('program'));
    }

    public function printPlan($program_id)
    {
        $program = PtProgram::with(['parameters', 'plan.coordinator'])->findOrFail($program_id);

        if (!$program->plan) {
            return redirect()->route('admin.plans.create', $program->program_id);
        }

        return view('admin.plans.print', compact('program'));
    }
}
