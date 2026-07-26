<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PtProgram;
use App\Models\ProgramRegistration;
use App\Models\Report;
use App\Services\DashboardAlertService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserDashboardController extends Controller
{
    public function index(DashboardAlertService $dashboardAlertService)
    {
        $lab = Auth::guard('lab')->user();

        // 1. Participant's Registrations & History
        $userRegistrations = ProgramRegistration::with(['program.parameters', 'sample.dispatch', 'payment', 'observations', 'reports'])
            ->where('lab_id', $lab->lab_id)
            ->orderBy('registered_at', 'desc')
            ->get();

        $registeredProgramIds = $userRegistrations->pluck('program_id')->toArray();

        // 2. Active PT Programs open for registration (plus any already registered programs)
        $activePrograms = PtProgram::with(['parameters', 'discipline'])
            ->where(function ($query) use ($registeredProgramIds) {
                $query->where(function ($q) {
                    $q->whereDate('registration_start_date', '<=', today())
                      ->whereDate('registration_end_date', '>=', today())
                      ->where(function ($sq) {
                          $sq->whereNull('registration_status')
                             ->orWhere('registration_status', '!=', 'closed');
                      })
                      ->where(function ($sq) {
                          $sq->whereNull('program_status')
                             ->orWhereNotIn('program_status', ['draft', 'forcefully_closed', 'completed']);
                      });
                })
                ->orWhereIn('program_id', $registeredProgramIds);
            })
            ->get();

        // Sort: Unregistered first (0), Registered last (1), preserving registration_end_date order
        $activePrograms = $activePrograms->sortBy(function ($program) use ($registeredProgramIds) {
            return in_array($program->program_id, $registeredProgramIds) ? 1 : 0;
        })->values();

        // 3. User Reports & Certificates
        $userReports = Report::whereIn('registration_id', $userRegistrations->pluck('registration_id'))
            ->orderBy('generated_at', 'desc')
            ->get();

        $dashboardAlerts = $dashboardAlertService->labAlerts($lab->lab_id);

        return view('user.dashboard', compact(
            'lab',
            'activePrograms',
            'userRegistrations',
            'userReports',
            'dashboardAlerts'
        ));
    }
}
