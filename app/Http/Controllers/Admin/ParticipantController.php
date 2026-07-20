<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ParticipantController extends Controller
{
    public function index(Request $request)
    {
        $query = Lab::with(['registrations.payment', 'registrations.program']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('laboratory_name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile_number', 'like', "%{$search}%")
                  ->orWhere('lab_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $participants = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        // Calculate latest registration payment status for each participant
        foreach ($participants as $lab) {
            $latestReg = $lab->registrations->sortByDesc('registered_at')->first();
            if ($latestReg && $latestReg->payment) {
                $lab->latest_payment_status = $latestReg->payment->payment_status;
            } elseif ($latestReg) {
                $lab->latest_payment_status = 'pending';
            } else {
                $lab->latest_payment_status = 'no_registrations';
            }
        }

        return view('admin.participants.index', compact('participants'));
    }

    public function show($id)
    {
        $lab = Lab::with(['registrations.program', 'registrations.payment'])->findOrFail($id);
        
        $totalRegisteredPrograms = $lab->registrations->count();
        $totalPaidAmount = $lab->registrations->sum(function ($reg) {
            return $reg->payment && $reg->payment->payment_status === 'success' ? $reg->payment->final_amount : 0;
        });

        return view('admin.participants.show', compact('lab', 'totalRegisteredPrograms', 'totalPaidAmount'));
    }

    public function exportCsv(Request $request)
    {
        $query = Lab::with(['registrations.payment']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('laboratory_name', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $labs = $query->orderBy('created_at', 'desc')->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=participants_master_sheet_" . date('Y-m-d_H-i') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function () use ($labs) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, [
                'Participant ID',
                'Laboratory Name',
                'NABL Cert No',
                'Lab Type',
                'GST Number',
                'Contact Person',
                'Designation',
                'Email',
                'Mobile Number',
                'City',
                'State',
                'Account Status',
                'Latest Payment Status',
                'Created At'
            ]);

            foreach ($labs as $lab) {
                $latestReg = $lab->registrations->sortByDesc('registered_at')->first();
                $payStatus = 'No Registrations';
                if ($latestReg && $latestReg->payment) {
                    $payStatus = ucfirst($latestReg->payment->payment_status);
                } elseif ($latestReg) {
                    $payStatus = 'Pending Payment';
                }

                fputcsv($file, [
                    'LAB-' . str_pad($lab->lab_id, 4, '0', STR_PAD_LEFT),
                    $lab->laboratory_name,
                    $lab->nabl_certificate_number ?? 'N/A',
                    $lab->laboratory_type ?? 'N/A',
                    $lab->gst_number ?? 'N/A',
                    $lab->contact_person,
                    $lab->designation ?? 'N/A',
                    $lab->email,
                    $lab->mobile_number,
                    $lab->city ?? 'N/A',
                    $lab->state ?? 'N/A',
                    ucfirst($lab->status),
                    $payStatus,
                    $lab->created_at ? $lab->created_at->format('Y-m-d H:i') : ''
                ]);
            }

            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}
