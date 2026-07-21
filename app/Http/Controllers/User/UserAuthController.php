<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Lab;
use App\Mail\UserWelcomeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserAuthController extends Controller
{
    public function showRegisterForm()
    {
        if (Auth::guard('lab')->check()) {
            return redirect()->route('user.dashboard');
        }

        return view('user.auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'laboratory_name' => 'required|string|max:255',
            'nabl_certificate_number' => 'nullable|string|max:100',
            'laboratory_type' => 'required|string|max:150',
            'gst_number' => 'required|string|max:50',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'pin_code' => 'required|string|max:20',
            'contact_person' => 'required|string|max:150',
            'designation' => 'required|string|max:100',
            'email' => 'required|string|email|max:255|unique:labs,email',
            'mobile_number' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Auto-Generate Unique Username (Pattern: LAB_SLUG_RANDOM)
        $cleanSlug = strtoupper(Str::slug(substr($validated['laboratory_name'], 0, 10), '_'));
        if (empty($cleanSlug)) {
            $cleanSlug = 'LAB';
        }
        
        do {
            $generatedUsername = 'LAB_' . $cleanSlug . '_' . rand(1000, 9999);
        } while (Lab::where('username', $generatedUsername)->exists());

        $rawPassword = $request->password;

        $lab = Lab::create([
            'laboratory_name' => $validated['laboratory_name'],
            'nabl_certificate_number' => $validated['nabl_certificate_number'] ?? null,
            'laboratory_type' => $validated['laboratory_type'],
            'gst_number' => strtoupper($validated['gst_number']),
            'address' => $validated['address'],
            'city' => $validated['city'],
            'state' => $validated['state'],
            'country' => $validated['country'],
            'pin_code' => $validated['pin_code'],
            'contact_person' => $validated['contact_person'],
            'designation' => $validated['designation'],
            'email' => strtolower($validated['email']),
            'mobile_number' => $validated['mobile_number'],
            'username' => $generatedUsername,
            'password_hash' => Hash::make($rawPassword),
            'status' => 'active',
        ]);

        // Send Welcome & Credential Email
        try {
            Mail::to($lab->email)->send(new UserWelcomeMail($lab, $rawPassword));
        } catch (\Exception $e) {
            // Soft fail log if mailer local driver is not configured
        }

        return redirect()->route('user.login')->with('success', "Registration successful! Your Auto-Generated User ID is '{$generatedUsername}'. Account details have been sent to your email.");
    }

    public function showLoginForm()
    {
        if (Auth::guard('lab')->check()) {
            return redirect()->route('user.dashboard');
        }

        return view('user.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string', // Accepts Email OR Auto-Generated Username
            'password' => 'required|string',
        ]);

        $loginInput = trim($request->login);

        // Determine if login is Email or Username
        $field = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $field => $loginInput,
            'password' => $request->password,
        ];

        if (Auth::guard('lab')->attempt($credentials, $request->has('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('user.dashboard'))->with('success', 'Logged in successfully!');
        }

        return back()->withErrors([
            'login' => 'Invalid User ID/Email or password.',
        ])->onlyInput('login');
    }

    public function logout(Request $request)
    {
        Auth::guard('lab')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('user.login')->with('success', 'Logged out successfully.');
    }
}
