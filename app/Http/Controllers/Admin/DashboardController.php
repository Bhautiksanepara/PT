<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Total Participants
        $totalParticipants = DB::table('labs')->count();

        // 2. Active Programs
        $activePrograms = DB::table('pt_programs')->where('program_status', 'open')->count();

        // 3. Total Revenue
        $totalRevenue = DB::table('payments')->where('payment_status', 'success')->sum('final_amount');

        // 4. Pending Dispatches
        $pendingDispatches = DB::table('samples')->where('status', 'pending')->count();

        // 5. Submitted Observations
        $submittedObservations = DB::table('observations')->whereNotNull('submitted_at')->count();

        // 6. Pending Reports
        $pendingReports = DB::table('pt_programs')->where('program_status', 'closed')->count();

        // Program Status counts for Donut Chart (REAL DATA)
        $programStatusCounts = [
            'draft' => DB::table('pt_programs')->where('program_status', 'draft')->count(),
            'open' => DB::table('pt_programs')->where('program_status', 'open')->count(),
            'closed' => DB::table('pt_programs')->where('program_status', 'closed')->count(),
            'completed' => DB::table('pt_programs')->where('program_status', 'completed')->count(),
        ];

        // 7. Monthly Registrations & Revenue Trend (REAL DATA - Last 6 Months)
        $monthsLabels = [];
        $monthlyRegistrations = [];
        $monthlyRevenue = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $monthLabel = $monthDate->format('M Y');
            $year = $monthDate->year;
            $month = $monthDate->month;

            $monthsLabels[] = $monthLabel;

            // Monthly Registrations count
            $regCount = DB::table('program_registrations')
                ->whereYear('registered_at', $year)
                ->whereMonth('registered_at', $month)
                ->count();
            $monthlyRegistrations[] = $regCount;

            // Monthly Successful Revenue sum
            $revSum = DB::table('payments')
                ->where('payment_status', 'success')
                ->whereYear('paid_at', $year)
                ->whereMonth('paid_at', $month)
                ->sum('final_amount');
            $monthlyRevenue[] = (float) $revSum;
        }

        // Recent Registrations (Last 5)
        $recentRegistrations = DB::table('program_registrations')
            ->join('labs', 'program_registrations.lab_id', '=', 'labs.lab_id')
            ->join('pt_programs', 'program_registrations.program_id', '=', 'pt_programs.program_id')
            ->leftJoin('payments', 'program_registrations.registration_id', '=', 'payments.registration_id')
            ->select(
                'program_registrations.registration_number',
                'program_registrations.registered_at',
                'labs.laboratory_name',
                'pt_programs.program_code',
                'program_registrations.status as registration_status',
                'payments.payment_status'
            )
            ->orderBy('program_registrations.registered_at', 'desc')
            ->limit(5)
            ->get();

        // Notifications Log
        $notifications = DB::table('notifications_log')
            ->orderBy('sent_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalParticipants',
            'activePrograms',
            'totalRevenue',
            'pendingDispatches',
            'submittedObservations',
            'pendingReports',
            'programStatusCounts',
            'monthsLabels',
            'monthlyRegistrations',
            'monthlyRevenue',
            'recentRegistrations',
            'notifications'
        ));
    }
}
