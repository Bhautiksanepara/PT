<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PtProgram;
use App\Models\ProgramRegistration;
use App\Models\Observation;
use App\Models\Dispatch;
use App\Models\Payment;
use Illuminate\Http\Request;

class ArchiveController extends Controller
{
    public function index(Request $request)
    {
        $query = PtProgram::with(['parameters', 'registrations.lab', 'discipline']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('program_code', 'like', "%{$search}%")
                  ->orWhere('program_name', 'like', "%{$search}%")
                  ->orWhereHas('discipline', function ($dq) use ($search) {
                      $dq->where('discipline_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('discipline')) {
            $query->whereHas('discipline', function ($dq) use ($request) {
                $dq->where('discipline_id', $request->discipline);
            });
        }

        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }

        $programs = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // Available years for dropdown filter
        $availableYears = PtProgram::selectRaw('YEAR(created_at) as year')
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        // Load all disciplines for the filter dropdown from the disciplines master table
        $disciplines = \App\Models\DisciplineMaster::orderBy('discipline_name')->get();

        return view('admin.archive.index', compact('programs', 'availableYears', 'disciplines'));
    }

    public function showProgram($program_id)
    {
        $program = PtProgram::with([
            'parameters',
            'discipline',
            'plan',
            'batches',
            'registrations.lab',
            'registrations.payment',
            'registrations.sample.dispatch'
        ])->findOrFail($program_id);

        $totalRegistrations = $program->registrations->count();
        $totalObservations = Observation::whereIn('registration_id', $program->registrations->pluck('registration_id'))->count();
        $totalDispatches = Dispatch::whereIn('sample_id', $program->registrations->pluck('sample.sample_id')->filter())->count();
        $totalRevenue = Payment::whereIn('registration_id', $program->registrations->pluck('registration_id'))->sum('final_amount');

        return view('admin.archive.show', compact('program', 'totalRegistrations', 'totalObservations', 'totalDispatches', 'totalRevenue'));
    }
}
