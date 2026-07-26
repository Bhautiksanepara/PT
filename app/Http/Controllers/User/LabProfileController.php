<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LabProfileController extends Controller
{
    public function edit()
    {
        return view('user.profile.complete', ['lab' => auth('lab')->user()]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'center_name' => 'required|string|max:255',
            'laboratory_name' => 'required|string|max:255',
            'address' => 'required|string',
            'gst_number' => 'required|string|max:50',
            'mobile_number' => 'required|string|max:20',
            'laboratory_type' => 'required|string|max:150',
            'nabl_certificate_number' => 'required|string|max:100',
            'designation' => 'required|string|max:100',
        ]);

        auth('lab')->user()->update(array_merge($validated, [
            'gst_number' => strtoupper($validated['gst_number']),
            'profile_completed_at' => now(),
        ]));

        return redirect()->route('user.dashboard')->with('success', 'Your laboratory profile is complete. Portal access is now enabled.');
    }

    public function showPasswordForm()
    {
        return view('user.profile.change_password');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $lab = auth('lab')->user();
        if (!Hash::check($validated['current_password'], $lab->password_hash)) {
            return back()->withErrors(['current_password' => 'Your current password is incorrect.']);
        }

        $lab->update(['password_hash' => Hash::make($validated['password'])]);

        return back()->with('success', 'Password changed successfully.');
    }
}
