<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Lab;
use App\Models\LabSignupVerification;
use App\Mail\LabEmailVerificationMail;
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
            'center_name' => 'nullable|string|max:255',
            'contact_person' => 'required|string|max:150',
            'email' => 'required|string|email|max:150',
            'address' => 'required|string',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'pin_code' => 'required|string|max:20',
            'mobile_number' => 'required|string|max:20',
        ]);

        $otp = (string) random_int(100000, 999999);
        $verification = LabSignupVerification::updateOrCreate(
            ['email' => strtolower($validated['email'])],
            array_merge($validated, [
                'email' => strtolower($validated['email']),
                'otp_hash' => Hash::make($otp),
                'expires_at' => now()->addMinutes(10),
            ])
        );

        try {
            Mail::to($verification->email)->send(new LabEmailVerificationMail($verification, $otp));
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['email' => 'We could not send the verification email. Please try again.']);
        }

        return redirect()->route('user.register.verify', ['email' => $verification->email])
            ->with('success', 'A six-digit verification code has been sent to your email address.');
    }

    public function showVerificationForm(Request $request)
    {
        return view('user.auth.verify_email', ['email' => $request->query('email')]);
    }

    public function verifyEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|digits:6',
        ]);

        $verification = LabSignupVerification::where('email', strtolower($request->email))->first();
        if (!$verification || now()->greaterThan($verification->expires_at) || !Hash::check($request->otp, $verification->otp_hash)) {
            return back()->withInput()->withErrors(['otp' => 'The verification code is invalid or has expired.']);
        }

        $generatedUsername = $this->generateUsername($verification->contact_person);
        $rawPassword = Str::password(12, true, true, true, false);

        $lab = Lab::create([
            'laboratory_name' => $verification->laboratory_name,
            'center_name' => $verification->center_name,
            'address' => $verification->address,
            'city' => $verification->city,
            'state' => $verification->state,
            'country' => $verification->country,
            'pin_code' => $verification->pin_code,
            'contact_person' => $verification->contact_person,
            'email' => $verification->email,
            'mobile_number' => $verification->mobile_number,
            'username' => $generatedUsername,
            'password_hash' => Hash::make($rawPassword),
            'status' => 'active',
        ]);

        $verification->delete();

        try {
            Mail::to($lab->email)->send(new UserWelcomeMail($lab, $rawPassword));
        } catch (\Exception $e) {
            // The verified account remains available even if the mail transport is temporarily unavailable.
        }

        return redirect()->route('user.login')->with('success', 'Email verified. Your User ID and password have been sent to your email.');
    }

    public function resendVerificationOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $verification = LabSignupVerification::where('email', strtolower($request->email))->firstOrFail();
        $otp = (string) random_int(100000, 999999);
        $verification->update(['otp_hash' => Hash::make($otp), 'expires_at' => now()->addMinutes(10)]);

        Mail::to($verification->email)->send(new LabEmailVerificationMail($verification, $otp));

        return back()->with('success', 'A new verification code has been sent.');
    }

    private function generateUsername(string $contactPerson): string
    {
        $cleanSlug = strtoupper(Str::slug(substr($contactPerson, 0, 10), '_')) ?: 'LAB';
        do {
            $username = 'LAB_' . $cleanSlug . '_' . random_int(1000, 9999);
        } while (Lab::where('username', $username)->exists());

        return $username;
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
