<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SampleBatch;
use App\Models\PtProgram;
use App\Models\AdminUser;
use App\Models\HomogeneityTest;
use App\Models\StabilityTest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SampleBatchController extends Controller
{
    public function index(Request $request)
    {
        $query = SampleBatch::with(['program', 'preparedByAdmin', 'verifiedByAdmin'])->withCount('samples');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('batch_number', 'like', "%{$search}%")
                  ->orWhere('material', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        $batches = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        $programs = PtProgram::orderBy('program_code')->get();

        return view('admin.batches.index', compact('batches', 'programs'));
    }

    public function create(Request $request, $program_id = null)
    {
        $selectedProgramId = $program_id ?? $request->query('program_id');
        $programs = PtProgram::orderBy('program_code')->get();

        if ($programs->isEmpty()) {
            return redirect()->route('admin.programs.create')->with('error', 'Please create a PT Program first.');
        }

        $program = $selectedProgramId ? PtProgram::with('plan', 'parameters')->find($selectedProgramId) : null;

        $adminUsers = AdminUser::where('status', 'active')->orderBy('full_name')->get();

        $defaultBatchNumber = '';
        if ($program) {
            // Default batch number recommendation: BATCH-{PROGRAM_CODE}-{NEXT_NUM} (Unique Check)
            $count = SampleBatch::where('program_id', $program->program_id)->count() + 1;
            do {
                $defaultBatchNumber = 'BATCH-' . $program->program_code . '-' . str_pad($count, 2, '0', STR_PAD_LEFT);
                $count++;
            } while (SampleBatch::where('batch_number', $defaultBatchNumber)->exists());
        }

        return view('admin.batches.create', compact('program', 'programs', 'adminUsers', 'defaultBatchNumber'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'program_id' => 'required|exists:pt_programs,program_id',
            'batch_number' => 'required|string|max:100|unique:sample_batches,batch_number',
            'material' => 'required|string|max:255',
            'quantity' => 'required|string|max:100',
            'prepared_by' => 'nullable|exists:admin_users,admin_id',
            'verified_by' => 'nullable|exists:admin_users,admin_id',
            'preparation_date' => 'nullable|date',
            'status' => 'required|in:in_preparation,testing,approved,rejected',
            
            // Homogeneity Test fields
            'homogeneity_date' => 'nullable|date',
            'homogeneity_result' => 'nullable|string|max:255',
            'homogeneity_remarks' => 'nullable|string',

            // Stability Test fields
            'stability_date' => 'nullable|date',
            'stability_result' => 'nullable|string|max:255',
            'stability_remarks' => 'nullable|string',

            // Reference values
            'reference_tests' => 'nullable|array',
            'reference_tests.*' => 'nullable|array',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $batch = SampleBatch::create([
                'program_id' => $validated['program_id'],
                'batch_number' => $validated['batch_number'],
                'material' => $validated['material'],
                'quantity' => $validated['quantity'],
                'prepared_by' => $validated['prepared_by'] ?? null,
                'verified_by' => $validated['verified_by'] ?? null,
                'preparation_date' => $validated['preparation_date'] ?? null,
                'status' => $validated['status'],
                'created_at' => now(),
            ]);

            // Save initial Homogeneity Test record if result provided
            if (!empty($validated['homogeneity_result'])) {
                HomogeneityTest::create([
                    'batch_id' => $batch->batch_id,
                    'test_date' => $validated['homogeneity_date'] ?? now()->toDateString(),
                    'result' => $validated['homogeneity_result'],
                    'performed_by' => $validated['prepared_by'] ?? null,
                    'remarks' => $validated['homogeneity_remarks'] ?? null,
                ]);
            }

            // Save initial Stability Test record if result provided
            if (!empty($validated['stability_result'])) {
                StabilityTest::create([
                    'batch_id' => $batch->batch_id,
                    'test_date' => $validated['stability_date'] ?? now()->toDateString(),
                    'result' => $validated['stability_result'],
                    'performed_by' => $validated['prepared_by'] ?? null,
                    'remarks' => $validated['stability_remarks'] ?? null,
                ]);
            }

            // Save Reference values replicates
            if ($request->has('reference_tests') && is_array($request->reference_tests)) {
                foreach ($request->reference_tests as $runIdx => $paramVals) {
                    if (!is_array($paramVals)) continue;
                    foreach ($paramVals as $paramId => $val) {
                        if ($val !== null && $val !== '') {
                            \App\Models\BatchParameterReferenceValue::create([
                                'batch_id' => $batch->batch_id,
                                'program_id' => $batch->program_id,
                                'parameter_id' => $paramId,
                                'replicate_number' => $runIdx,
                                'reference_value' => floatval($val),
                            ]);
                        }
                    }
                }
            }
        });

        return redirect()->route('admin.batches.index')->with('success', 'Sample Production Batch created successfully!');
    }

    public function show($batch_id)
    {
        $batch = SampleBatch::with([
            'program',
            'preparedByAdmin',
            'verifiedByAdmin',
            'homogeneityTests.performedByAdmin',
            'stabilityTests.performedByAdmin',
            'samples.registration.lab'
        ])->findOrFail($batch_id);

        return view('admin.batches.show', compact('batch'));
    }

    public function edit($batch_id)
    {
        $batch = SampleBatch::with(['homogeneityTests', 'stabilityTests', 'referenceValues'])->findOrFail($batch_id);
        $program = $batch->program;
        $adminUsers = AdminUser::where('status', 'active')->orderBy('full_name')->get();

        $latestHomogeneity = $batch->homogeneityTests->first();
        $latestStability = $batch->stabilityTests->first();
        $groupedReferenceValues = $batch->referenceValues->groupBy('replicate_number');

        return view('admin.batches.edit', compact('batch', 'program', 'adminUsers', 'latestHomogeneity', 'latestStability', 'groupedReferenceValues'));
    }

    public function update(Request $request, $batch_id)
    {
        $batch = SampleBatch::findOrFail($batch_id);

        $validated = $request->validate([
            'batch_number' => 'required|string|max:100|unique:sample_batches,batch_number,' . $batch_id . ',batch_id',
            'material' => 'required|string|max:255',
            'quantity' => 'required|string|max:100',
            'prepared_by' => 'nullable|exists:admin_users,admin_id',
            'verified_by' => 'nullable|exists:admin_users,admin_id',
            'preparation_date' => 'nullable|date',
            'status' => 'required|in:in_preparation,testing,approved,rejected',

            // Homogeneity Test fields
            'homogeneity_date' => 'nullable|date',
            'homogeneity_result' => 'nullable|string|max:255',
            'homogeneity_remarks' => 'nullable|string',

            // Stability Test fields
            'stability_date' => 'nullable|date',
            'stability_result' => 'nullable|string|max:255',
            'stability_remarks' => 'nullable|string',

            // Reference values
            'reference_tests' => 'nullable|array',
            'reference_tests.*' => 'nullable|array',
        ]);

        DB::transaction(function () use ($batch, $validated, $request) {
            $batch->update([
                'batch_number' => $validated['batch_number'],
                'material' => $validated['material'],
                'quantity' => $validated['quantity'],
                'prepared_by' => $validated['prepared_by'] ?? null,
                'verified_by' => $validated['verified_by'] ?? null,
                'preparation_date' => $validated['preparation_date'] ?? null,
                'status' => $validated['status'],
            ]);

            // Update/Create Homogeneity
            if (!empty($validated['homogeneity_result'])) {
                HomogeneityTest::updateOrCreate(
                    ['batch_id' => $batch->batch_id],
                    [
                        'test_date' => $validated['homogeneity_date'] ?? now()->toDateString(),
                        'result' => $validated['homogeneity_result'],
                        'performed_by' => $validated['prepared_by'] ?? null,
                        'remarks' => $validated['homogeneity_remarks'] ?? null,
                    ]
                );
            }

            // Update/Create Stability
            if (!empty($validated['stability_result'])) {
                StabilityTest::updateOrCreate(
                    ['batch_id' => $batch->batch_id],
                    [
                        'test_date' => $validated['stability_date'] ?? now()->toDateString(),
                        'result' => $validated['stability_result'],
                        'performed_by' => $validated['prepared_by'] ?? null,
                        'remarks' => $validated['stability_remarks'] ?? null,
                    ]
                );
            }

            // Clear and rewrite Reference values replicates
            \App\Models\BatchParameterReferenceValue::where('batch_id', $batch->batch_id)->delete();
            if ($request->has('reference_tests') && is_array($request->reference_tests)) {
                foreach ($request->reference_tests as $runIdx => $paramVals) {
                    if (!is_array($paramVals)) continue;
                    foreach ($paramVals as $paramId => $val) {
                        if ($val !== null && $val !== '') {
                            \App\Models\BatchParameterReferenceValue::create([
                                'batch_id' => $batch->batch_id,
                                'program_id' => $batch->program_id,
                                'parameter_id' => $paramId,
                                'replicate_number' => $runIdx,
                                'reference_value' => floatval($val),
                            ]);
                        }
                    }
                }
            }
        });

        return redirect()->route('admin.batches.show', $batch->batch_id)->with('success', 'Sample batch updated successfully!');
    }

    public function approve($batch_id)
    {
        $batch = SampleBatch::findOrFail($batch_id);
        $batch->update(['status' => 'approved']);

        return back()->with('success', "Sample Batch {$batch->batch_number} has been approved for assignment.");
    }
}
