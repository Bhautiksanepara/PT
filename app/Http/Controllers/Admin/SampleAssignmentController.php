<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sample;
use App\Models\PtProgram;
use App\Models\SampleBatch;
use App\Models\ProgramRegistration;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class SampleAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Sample::with(['program', 'registration.lab', 'batch']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sample_code', 'like', "%{$search}%")
                  ->orWhereHas('registration.lab', function ($lq) use ($search) {
                      $lq->where('laboratory_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('registration', function ($rq) use ($search) {
                      $rq->where('registration_number', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $samples = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        $programs = PtProgram::orderBy('program_code')->get();

        return view('admin.samples.index', compact('samples', 'programs'));
    }

    public function programSamples($program_id)
    {
        $program = PtProgram::with(['registrations.lab', 'registrations.payment'])->findOrFail($program_id);
        $approvedBatches = SampleBatch::where('program_id', $program->program_id)
            ->where('status', 'approved')
            ->orderBy('batch_number')
            ->get();
        $allBatches = SampleBatch::where('program_id', $program->program_id)->orderBy('batch_number')->get();

        $assignedSamples = Sample::where('program_id', $program->program_id)
            ->with(['registration.lab', 'batch'])
            ->get()
            ->keyBy('registration_id');

        $currentYear = date('Y');
        $latestSample = Sample::where('sample_code', 'like', "PT-{$currentYear}-%")
            ->orderBy('sample_id', 'desc')
            ->first();

        $nextSeq = 1;
        if ($latestSample && preg_match('/PT-\d{4}-(\d+)/', $latestSample->sample_code, $matches)) {
            $nextSeq = (int)$matches[1] + 1;
        }
        $nextSampleCode = 'PT-' . $currentYear . '-' . str_pad($nextSeq, 3, '0', STR_PAD_LEFT);

        return view('admin.samples.program', compact('program', 'approvedBatches', 'allBatches', 'assignedSamples', 'nextSampleCode'));
    }

    public function bulkAssign(Request $request, $program_id)
    {
        $program = PtProgram::findOrFail($program_id);

        $validated = $request->validate([
            'batch_id' => 'required|exists:sample_batches,batch_id',
        ]);

        $batch = SampleBatch::findOrFail($validated['batch_id']);

        // Fetch all confirmed registrations for this program
        $registrations = ProgramRegistration::where('program_id', $program->program_id)
            ->where('status', 'confirmed')
            ->orderBy('registered_at')
            ->get();

        if ($registrations->isEmpty()) {
            return back()->with('error', 'No confirmed registrations found for this program to assign samples.');
        }

        $assignedCount = 0;
        $currentYear = date('Y');

        // Determine starting sequence number (e.g. PT-2026-001)
        $latestSample = Sample::where('sample_code', 'like', "PT-{$currentYear}-%")
            ->orderBy('sample_id', 'desc')
            ->first();

        $nextSeq = 1;
        if ($latestSample && preg_match('/PT-\d{4}-(\d+)/', $latestSample->sample_code, $matches)) {
            $nextSeq = (int)$matches[1] + 1;
        }

        DB::transaction(function () use ($registrations, $batch, $program, &$assignedCount, &$nextSeq, $currentYear) {
            foreach ($registrations as $reg) {
                // Check if already assigned
                $existing = Sample::where('registration_id', $reg->registration_id)->first();
                if (!$existing) {
                    $sampleCode = 'PT-' . $currentYear . '-' . str_pad($nextSeq, 3, '0', STR_PAD_LEFT);

                    Sample::create([
                        'sample_code' => $sampleCode,
                        'program_id' => $program->program_id,
                        'registration_id' => $reg->registration_id,
                        'batch_id' => $batch->batch_id,
                        'qr_code' => 'QR-' . $sampleCode,
                        'status' => 'pending',
                        'created_at' => now(),
                    ]);

                    $nextSeq++;
                    $assignedCount++;
                }
            }
        });

        return back()->with('success', "Successfully auto-assigned {$assignedCount} sample codes using Batch {$batch->batch_number}!");
    }

    public function assignSingle(Request $request)
    {
        $existingSample = Sample::where('registration_id', $request->registration_id)->first();
        $sampleCodeRule = Rule::unique('samples', 'sample_code');
        if ($existingSample) {
            $sampleCodeRule->ignore($existingSample->sample_id, 'sample_id');
        }

        $validated = $request->validate([
            'registration_id' => 'required|exists:program_registrations,registration_id',
            'batch_id' => 'nullable|exists:sample_batches,batch_id',
            'sample_code' => ['required', 'string', 'max:50', $sampleCodeRule],
        ], [
            'sample_code.unique' => "Sample code '{$request->sample_code}' is already assigned to another laboratory. Please use a unique sample code.",
        ]);

        $reg = ProgramRegistration::with('program')->findOrFail($validated['registration_id']);

        $batchId = $validated['batch_id'] ?? null;
        if (!$batchId) {
            $defaultBatch = SampleBatch::firstOrCreate(
                ['program_id' => $reg->program_id, 'batch_number' => 'BATCH-' . $reg->program->program_code . '-01'],
                [
                    'material' => 'Standard PT Reference Sample',
                    'quantity_produced' => 100,
                    'unit' => 'Units',
                    'status' => 'approved',
                    'prepared_by' => auth()->id() ?? 1,
                    'created_at' => now(),
                ]
            );
            $batchId = $defaultBatch->batch_id;
        }

        Sample::updateOrCreate(
            ['registration_id' => $reg->registration_id],
            [
                'sample_code' => $validated['sample_code'],
                'program_id' => $reg->program_id,
                'batch_id' => $batchId,
                'qr_code' => 'QR-' . $validated['sample_code'],
                'status' => 'pending',
                'created_at' => now(),
            ]
        );

        return back()->with('success', "Sample code {$validated['sample_code']} assigned to {$reg->lab->laboratory_name} successfully!");
    }
}
