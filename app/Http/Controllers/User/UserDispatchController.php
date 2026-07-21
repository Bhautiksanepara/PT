<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ProgramRegistration;
use App\Models\Dispatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserDispatchController extends Controller
{
    public function index(Request $request)
    {
        $lab = Auth::guard('lab')->user();

        $query = ProgramRegistration::with(['program', 'sample.dispatch'])
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
                      $sq->where('sample_code', 'like', "%{$search}%")
                        ->orWhereHas('dispatch', function ($dq) use ($search) {
                            $dq->where('courier_name', 'like', "%{$search}%")
                              ->orWhere('tracking_number', 'like', "%{$search}%");
                        });
                  });
            });
        }

        $registrations = $query->orderBy('registered_at', 'desc')->paginate(10)->withQueryString();

        return view('user.dispatches.index', compact('lab', 'registrations'));
    }

    public function confirmReceipt($registration_id, Request $request)
    {
        $lab = Auth::guard('lab')->user();

        $registration = ProgramRegistration::with('sample.dispatch')
            ->where('lab_id', $lab->lab_id)
            ->findOrFail($registration_id);

        $sample = $registration->sample;
        if (!$sample || !$sample->dispatch) {
            return redirect()->back()->with('error', 'No sample dispatch record found for this scheme.');
        }

        $request->validate([
            'arrival_condition' => 'required|in:intact,damaged,leaked',
            'condition_notes'   => 'nullable|string|max:500',
        ]);

        $dispatch = $sample->dispatch;
        $dispatch->update([
            'received_at'       => now(),
            'arrival_condition' => $request->arrival_condition,
            'condition_notes'   => $request->condition_notes,
        ]);

        $sample->update(['status' => 'received']);

        if ($request->arrival_condition === 'intact') {
            return redirect()->back()->with('success', 'Sample parcel receipt confirmed! Condition recorded as Intact & Sealed.');
        } else {
            return redirect()->back()->with('error', 'Sample condition reported as Damaged/Leaked. System has flagged Admin for re-dispatch inspection.');
        }
    }

    public function viewPackingSlip($registration_id)
    {
        $lab = Auth::guard('lab')->user();

        $registration = ProgramRegistration::with(['program.parameters', 'sample.dispatch'])
            ->where('lab_id', $lab->lab_id)
            ->findOrFail($registration_id);

        $program = $registration->program;
        $sample = $registration->sample;

        if (!$sample || !$sample->dispatch) {
            return redirect()->back()->with('error', 'Sample dispatch packing slip is not available yet.');
        }

        $dispatch = $sample->dispatch;

        return view('user.dispatches.slip', compact('lab', 'registration', 'program', 'sample', 'dispatch'));
    }

    public static function getCourierTrackingUrl($courierName, $trackingNumber)
    {
        $courierLower = strtolower($courierName);
        if (str_contains($courierLower, 'bluedart')) {
            return "https://www.bluedart.com/tracking?track=" . urlencode($trackingNumber);
        } elseif (str_contains($courierLower, 'dtdc')) {
            return "https://www.dtdc.in/tracking/tracking_results.asp?TknNo=" . urlencode($trackingNumber);
        } elseif (str_contains($courierLower, 'fedex')) {
            return "https://www.fedex.com/fedextrack/?trknbr=" . urlencode($trackingNumber);
        } elseif (str_contains($courierLower, 'dhl')) {
            return "https://www.dhl.com/in-en/home/tracking.html?tracking-id=" . urlencode($trackingNumber);
        } elseif (str_contains($courierLower, 'post') || str_contains($courierLower, 'speed')) {
            return "https://www.indiapost.gov.in/_layouts/15/dop.portal.tracking/trackconsignment.aspx";
        }
        return "https://www.google.com/search?q=" . urlencode($courierName . " tracking " . $trackingNumber);
    }
}
