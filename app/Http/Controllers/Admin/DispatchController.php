<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispatch;
use App\Models\Sample;
use App\Models\PtProgram;
use App\Models\NotificationLog;
use App\Mail\SampleDispatchMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class DispatchController extends Controller
{
    public function index(Request $request)
    {
        $query = Dispatch::with(['sample.program', 'sample.registration.lab', 'dispatchedByAdmin']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('courier_name', 'like', "%{$search}%")
                  ->orWhere('tracking_number', 'like', "%{$search}%")
                  ->orWhereHas('sample', function ($sq) use ($search) {
                      $sq->where('sample_code', 'like', "%{$search}%");
                  })
                  ->orWhereHas('sample.registration.lab', function ($lq) use ($search) {
                      $lq->where('laboratory_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('program_id')) {
            $query->whereHas('sample', function ($sq) use ($request) {
                $sq->where('program_id', $request->program_id);
            });
        }

        $dispatches = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        $programs = PtProgram::orderBy('program_code')->get();

        return view('admin.dispatches.index', compact('dispatches', 'programs'));
    }

    public function programDispatches($program_id)
    {
        $program = PtProgram::with(['registrations.lab'])->findOrFail($program_id);
        $samples = Sample::where('program_id', $program->program_id)
            ->with(['registration.lab', 'batch', 'dispatches.dispatchedByAdmin'])
            ->orderBy('sample_code')
            ->get();

        return view('admin.dispatches.program', compact('program', 'samples'));
    }

    public function storeSingle(Request $request)
    {
        $validated = $request->validate([
            'sample_id' => 'required|exists:samples,sample_id',
            'dispatch_date' => 'required|date',
            'courier_name' => 'required|string|max:150',
            'tracking_number' => 'required|string|max:150',
        ]);

        $sample = Sample::with(['registration.lab', 'program'])->findOrFail($validated['sample_id']);
        $adminId = Auth::guard('admin')->id();

        DB::transaction(function () use ($sample, $validated, $adminId) {
            // 1. Create Dispatch record
            Dispatch::create([
                'sample_id' => $sample->sample_id,
                'dispatch_date' => $validated['dispatch_date'],
                'courier_name' => $validated['courier_name'],
                'tracking_number' => $validated['tracking_number'],
                'dispatched_by' => $adminId,
                'notification_sent' => 1,
                'notification_sent_at' => now(),
                'created_at' => now(),
            ]);

            // 2. Update Sample Status to 'dispatched'
            $sample->update(['status' => 'dispatched']);

            // 3. Create Notification Log entry & Send Email
            if ($sample->registration && $sample->registration->lab) {
                $lab = $sample->registration->lab;
                $subject = "Sample Dispatched: {$sample->sample_code} ({$sample->program->program_code})";
                $message = "Dear {$lab->laboratory_name},\n\nYour PT Sample item ({$sample->sample_code}) has been dispatched via {$validated['courier_name']} (Tracking #: {$validated['tracking_number']}).";

                NotificationLog::create([
                    'lab_id' => $lab->lab_id,
                    'notification_type' => 'dispatch',
                    'subject' => $subject,
                    'message' => $message,
                    'sent_at' => now(),
                ]);

                // Send Email via Mailable
                try {
                    Mail::to($lab->email)->send(new SampleDispatchMail(
                        $lab->laboratory_name,
                        $sample->sample_code,
                        $sample->program->program_code,
                        $validated['courier_name'],
                        $validated['tracking_number'],
                        $validated['dispatch_date'],
                        $sample->qr_code
                    ));
                } catch (\Exception $e) {
                    Log::error("Failed to send dispatch email to {$lab->email}: " . $e->getMessage());
                }
            }
        });

        return back()->with('success', "Sample {$sample->sample_code} dispatched successfully and notification email sent!");
    }

    public function storeBulk(Request $request, $program_id)
    {
        $program = PtProgram::findOrFail($program_id);

        $validated = $request->validate([
            'dispatch_date' => 'required|date',
            'courier_name' => 'required|string|max:150',
            'tracking_prefix' => 'required|string|max:100',
        ]);

        $pendingSamples = Sample::where('program_id', $program->program_id)
            ->where('status', 'pending')
            ->with(['registration.lab'])
            ->get();

        if ($pendingSamples->isEmpty()) {
            return back()->with('error', 'No pending samples found for this program to dispatch.');
        }

        $adminId = Auth::guard('admin')->id();
        $dispatchedCount = 0;

        DB::transaction(function () use ($pendingSamples, $validated, $adminId, $program, &$dispatchedCount) {
            foreach ($pendingSamples as $index => $sample) {
                $trackingNumber = $validated['tracking_prefix'] . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);

                Dispatch::create([
                    'sample_id' => $sample->sample_id,
                    'dispatch_date' => $validated['dispatch_date'],
                    'courier_name' => $validated['courier_name'],
                    'tracking_number' => $trackingNumber,
                    'dispatched_by' => $adminId,
                    'notification_sent' => 1,
                    'notification_sent_at' => now(),
                    'created_at' => now(),
                ]);

                $sample->update(['status' => 'dispatched']);

                if ($sample->registration && $sample->registration->lab) {
                    $lab = $sample->registration->lab;
                    NotificationLog::create([
                        'lab_id' => $lab->lab_id,
                        'notification_type' => 'dispatch',
                        'subject' => "Sample Dispatched: {$sample->sample_code} ({$program->program_code})",
                        'message' => "Dear {$lab->laboratory_name},\n\nYour PT Sample item ({$sample->sample_code}) has been dispatched via {$validated['courier_name']} (Tracking #: {$trackingNumber}).",
                        'sent_at' => now(),
                    ]);

                    try {
                        Mail::to($lab->email)->send(new SampleDispatchMail(
                            $lab->laboratory_name,
                            $sample->sample_code,
                            $program->program_code,
                            $validated['courier_name'],
                            $trackingNumber,
                            $validated['dispatch_date'],
                            $sample->qr_code
                        ));
                    } catch (\Exception $e) {
                        Log::error("Failed to send bulk dispatch email to {$lab->email}: " . $e->getMessage());
                    }
                }

                $dispatchedCount++;
            }
        });

        return back()->with('success', "Successfully dispatched {$dispatchedCount} samples and sent notifications to participant labs!");
    }

    public function resendNotification($dispatch_id)
    {
        $dispatch = Dispatch::with(['sample.registration.lab', 'sample.program'])->findOrFail($dispatch_id);

        if ($dispatch->sample && $dispatch->sample->registration && $dispatch->sample->registration->lab) {
            $lab = $dispatch->sample->registration->lab;
            
            NotificationLog::create([
                'lab_id' => $lab->lab_id,
                'notification_type' => 'dispatch_resend',
                'subject' => "Reminder: Sample Dispatched - {$dispatch->sample->sample_code}",
                'message' => "Dear {$lab->laboratory_name},\n\nReminder: Your sample ({$dispatch->sample->sample_code}) was dispatched via {$dispatch->courier_name} (Tracking #: {$dispatch->tracking_number}).",
                'sent_at' => now(),
            ]);

            $dispatch->update([
                'notification_sent' => 1,
                'notification_sent_at' => now(),
            ]);

            try {
                Mail::to($lab->email)->send(new SampleDispatchMail(
                    $lab->laboratory_name,
                    $dispatch->sample->sample_code,
                    $dispatch->sample->program->program_code,
                    $dispatch->courier_name,
                    $dispatch->tracking_number,
                    $dispatch->dispatch_date,
                    $dispatch->sample->qr_code
                ));
            } catch (\Exception $e) {
                Log::error("Failed to resend dispatch email to {$lab->email}: " . $e->getMessage());
            }
        }

        return back()->with('success', 'Dispatch notification re-sent and logged!');
    }
}
