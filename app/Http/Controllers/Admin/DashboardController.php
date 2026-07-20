<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
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

        // Program Status counts for Donut Chart
        $programStatusCounts = [
            'draft' => DB::table('pt_programs')->where('program_status', 'draft')->count(),
            'open' => DB::table('pt_programs')->where('program_status', 'open')->count(),
            'closed' => DB::table('pt_programs')->where('program_status', 'closed')->count(),
            'completed' => DB::table('pt_programs')->where('program_status', 'completed')->count(),
        ];

        // 7. Dynamic Monthly Registrations & Revenue Trend (Filterable by Year / 12-Month Rolling)
        $chartRange = $request->get('chart_range', '12_months');

        $monthsLabels = [];
        $monthlyRegistrations = [];
        $monthlyRevenue = [];

        if (is_numeric($chartRange)) {
            $selectedYear = (int)$chartRange;
            for ($m = 1; $m <= 12; $m++) {
                $monthDate = Carbon::createFromDate($selectedYear, $m, 1);
                $monthsLabels[] = $monthDate->format('M Y');

                $regCount = DB::table('program_registrations')
                    ->whereYear('registered_at', $selectedYear)
                    ->whereMonth('registered_at', $m)
                    ->count();
                $monthlyRegistrations[] = $regCount;

                $revSum = DB::table('payments')
                    ->where('payment_status', 'success')
                    ->whereYear('paid_at', $selectedYear)
                    ->whereMonth('paid_at', $m)
                    ->sum('final_amount');
                $monthlyRevenue[] = (float) $revSum;
            }
        } else {
            // Rolling Last 12 Months
            for ($i = 11; $i >= 0; $i--) {
                $monthDate = Carbon::now()->subMonths($i);
                $monthsLabels[] = $monthDate->format('M Y');
                $year = $monthDate->year;
                $month = $monthDate->month;

                $regCount = DB::table('program_registrations')
                    ->whereYear('registered_at', $year)
                    ->whereMonth('registered_at', $month)
                    ->count();
                $monthlyRegistrations[] = $regCount;

                $revSum = DB::table('payments')
                    ->where('payment_status', 'success')
                    ->whereYear('paid_at', $year)
                    ->whereMonth('paid_at', $month)
                    ->sum('final_amount');
                $monthlyRevenue[] = (float) $revSum;
            }
        }

        // Available years in database for dropdown filter
        $years1 = DB::table('program_registrations')->whereNotNull('registered_at')->selectRaw('YEAR(registered_at) as year')->pluck('year')->toArray();
        $years2 = DB::table('payments')->whereNotNull('paid_at')->selectRaw('YEAR(paid_at) as year')->pluck('year')->toArray();

        $availableYears = array_values(array_unique(array_filter(array_merge($years1, $years2))));
        rsort($availableYears);

        if (empty($availableYears)) {
            $availableYears = [(int)date('Y')];
        }

        // Recent Registrations (Last 5)
        $recentRegistrations = DB::table('program_registrations')
            ->join('labs', 'program_registrations.lab_id', '=', 'labs.lab_id')
            ->join('pt_programs', 'program_registrations.program_id', '=', 'pt_programs.program_id')
            ->leftJoin('payments', 'program_registrations.registration_id', '=', 'payments.registration_id')
            ->select(
                'program_registrations.registration_id',
                'program_registrations.registration_number',
                'program_registrations.status',
                'program_registrations.registered_at',
                'labs.laboratory_name',
                'pt_programs.program_code',
                'payments.payment_status'
            )
            ->orderBy('program_registrations.registered_at', 'desc')
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
            'chartRange',
            'availableYears',
            'recentRegistrations'
        ));
    }
}
