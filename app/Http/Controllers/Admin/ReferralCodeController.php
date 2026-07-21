<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReferralCode;
use App\Models\Lab;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReferralCodeController extends Controller
{
    public function index(Request $request)
    {
        $query = ReferralCode::with(['clientLab', 'usedByLab', 'creator']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhereHas('clientLab', function ($lq) use ($search) {
                      $lq->where('laboratory_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('discount_type')) {
            $query->where('discount_type', $request->discount_type);
        }

        if ($request->filled('rule')) {
            $rule = $request->rule;
            if ($rule === 'one_time') {
                $query->where('is_one_time_use', 1);
            } elseif ($rule === 'client_specific') {
                $query->where('is_client_specific', 1);
            } elseif ($rule === 'expired') {
                $query->where('expiry_date', '<', date('Y-m-d'));
            }
        }

        $referralCodes = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        $labs = Lab::orderBy('laboratory_name')->get();

        // Stat Counters
        $totalActive = ReferralCode::where(function ($q) {
            $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', date('Y-m-d'));
        })->where('is_used', 0)->count();

        $totalOneTime = ReferralCode::where('is_one_time_use', 1)->count();
        $totalClientSpecific = ReferralCode::where('is_client_specific', 1)->count();
        $totalUsed = ReferralCode::where('is_used', 1)->count();

        return view('admin.referrals.index', compact(
            'referralCodes',
            'labs',
            'totalActive',
            'totalOneTime',
            'totalClientSpecific',
            'totalUsed'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'               => 'required|string|max:50|unique:referral_codes,code',
            'discount_type'      => 'required|in:percentage,fixed',
            'discount_value'     => 'required|numeric|min:0.01',
            'is_one_time_use'    => 'nullable|boolean',
            'is_client_specific' => 'nullable|boolean',
            'client_lab_id'      => 'nullable|required_if:is_client_specific,1|exists:labs,lab_id',
            'expiry_date'        => 'nullable|date|after_or_equal:today',
        ]);

        $adminId = auth()->guard('admin')->id() ?? 1;

        ReferralCode::create([
            'code'               => strtoupper(trim($validated['code'])),
            'discount_type'      => $validated['discount_type'],
            'discount_value'     => $validated['discount_value'],
            'is_one_time_use'    => $request->has('is_one_time_use') ? 1 : 0,
            'is_client_specific' => $request->has('is_client_specific') ? 1 : 0,
            'client_lab_id'      => $request->has('is_client_specific') ? $request->client_lab_id : null,
            'expiry_date'        => $validated['expiry_date'] ?? null,
            'is_used'            => 0,
            'created_by'         => $adminId,
            'created_at'         => now(),
        ]);

        return redirect()->route('admin.referrals.index')->with('success', 'Referral Code created successfully with configured rules!');
    }

    public function destroy($referral_id)
    {
        $code = ReferralCode::findOrFail($referral_id);
        $code->delete();

        return back()->with('success', 'Referral Code deleted successfully!');
    }
}
