<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PtProgram;
use App\Models\ProgramRegistration;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserDashboardController extends Controller
{
    public function index()
    {
        $lab = Auth::guard('lab')->user();

        // 1. Active PT Programs open for registration
        $activePrograms = PtProgram::with('parameters')
            ->where('program_status', 'open')
            ->where('registration_status', 'active')
            ->orderBy('registration_end_date', 'asc')
            ->get();

        // 2. Participant's Registrations & History
        $userRegistrations = ProgramRegistration::with(['program.parameters', 'sample.dispatch', 'payment', 'observations', 'reports'])
            ->where('lab_id', $lab->lab_id)
            ->orderBy('registered_at', 'desc')
            ->get();

        // 3. User Reports & Certificates
        $userReports = Report::whereIn('registration_id', $userRegistrations->pluck('registration_id'))
            ->orderBy('generated_at', 'desc')
            ->get();

        return view('user.dashboard', compact(
            'lab',
            'activePrograms',
            'userRegistrations',
            'userReports'
        ));
    }
}
