<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\PtProgram;
use App\Models\ProgramRegistration;
use App\Models\ReferralCode;
use App\Models\Payment;
use App\Mail\PaymentConfirmationMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class UserProgramRegistrationController extends Controller
{
    public function showRegisterForm($program_id)
    {
        $lab = Auth::guard('lab')->user();
        $program = PtProgram::with('parameters')->findOrFail($program_id);

        // Check if already registered
        $existingReg = ProgramRegistration::where('lab_id', $lab->lab_id)
            ->where('program_id', $program->program_id)
            ->first();

        if ($existingReg) {
            return redirect()->route('user.dashboard')->with('error', "Your laboratory is already registered for scheme {$program->program_code}.");
        }

        return view('user.registration_form', compact('lab', 'program'));
    }

    public function validateReferral(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'program_id' => 'required|integer',
        ]);

        $lab = Auth::guard('lab')->user();
        $program = PtProgram::findOrFail($request->program_id);
        $codeStr = strtoupper(trim($request->code));

        $refCode = ReferralCode::where('code', $codeStr)->first();

        if (!$refCode) {
            return response()->json(['valid' => false, 'message' => 'Invalid referral code.'], 400);
        }

        $check = $refCode->isValidForLab($lab->lab_id);
        if (!$check['valid']) {
            return response()->json(['valid' => false, 'message' => $check['message']], 400);
        }

        // Calculate Discount
        $originalFee = (float)$program->program_fee;
        if ($refCode->discount_type === 'percentage') {
            $discountAmount = ($originalFee * (float)$refCode->discount_value) / 100;
        } else {
            $discountAmount = (float)$refCode->discount_value;
        }

        if ($discountAmount > $originalFee) {
            $discountAmount = $originalFee;
        }

        $finalFee = $originalFee - $discountAmount;

        return response()->json([
            'valid' => true,
            'code' => $refCode->code,
            'discount_type' => $refCode->discount_type,
            'discount_value' => $refCode->discount_value,
            'discount_amount' => round($discountAmount, 2),
            'final_amount' => round($finalFee, 2),
            'message' => "Referral Code '{$refCode->code}' applied successfully!",
        ]);
    }

    public function createStripeIntent(Request $request)
    {
        $request->validate([
            'program_id' => 'required|integer',
            'referral_code' => 'nullable|string',
        ]);

        $lab = Auth::guard('lab')->user();
        $program = PtProgram::findOrFail($request->program_id);

        $originalFee = (float)$program->program_fee;
        $discountAmount = 0.00;

        if ($request->filled('referral_code')) {
            $codeStr = strtoupper(trim($request->referral_code));
            $refCode = ReferralCode::where('code', $codeStr)->first();
            if ($refCode && $refCode->isValidForLab($lab->lab_id)['valid']) {
                if ($refCode->discount_type === 'percentage') {
                    $discountAmount = ($originalFee * (float)$refCode->discount_value) / 100;
                } else {
                    $discountAmount = (float)$refCode->discount_value;
                }
                if ($discountAmount > $originalFee) {
                    $discountAmount = $originalFee;
                }
            }
        }

        $finalAmount = $originalFee - $discountAmount;

        $stripeService = new \App\Services\StripeService();
        $intent = $stripeService->createPaymentIntent($finalAmount, "PT Scheme Registration - {$program->program_code}", [
            'program_code' => $program->program_code,
            'lab_id' => $lab->lab_id,
        ]);

        return response()->json([
            'client_secret' => $intent['client_secret'],
            'payment_intent_id' => $intent['payment_intent_id'],
            'publishable_key' => $stripeService->getPublicKey(),
            'amount' => round($finalAmount, 2),
        ]);
    }

    public function submitRegistration(Request $request, $program_id)
    {
        $lab = Auth::guard('lab')->user();
        $program = PtProgram::findOrFail($program_id);

        $validated = $request->validate([
            'sample_quantity' => 'required|string|max:150',
            'shipping_address' => 'required|string',
            'billing_address' => 'required|string',
            'referral_code' => 'nullable|string',
            'payment_method' => 'required|string|max:100',
        ]);

        // Calculate Fee & Referral Code Discount
        $originalFee = (float)$program->program_fee;
        $discountAmount = 0.00;
        $appliedRefCode = null;

        if ($request->filled('referral_code')) {
            $codeStr = strtoupper(trim($request->referral_code));
            $refCode = ReferralCode::where('code', $codeStr)->first();

            if ($refCode && $refCode->isValidForLab($lab->lab_id)['valid']) {
                $appliedRefCode = $refCode;
                if ($refCode->discount_type === 'percentage') {
                    $discountAmount = ($originalFee * (float)$refCode->discount_value) / 100;
                } else {
                    $discountAmount = (float)$refCode->discount_value;
                }
                if ($discountAmount > $originalFee) {
                    $discountAmount = $originalFee;
                }
            }
        }

        $finalAmount = $originalFee - $discountAmount;

        $registration = DB::transaction(function () use ($lab, $program, $validated, $originalFee, $discountAmount, $finalAmount, $appliedRefCode) {
            // 1. Generate Registration Number
            $nextId = ProgramRegistration::max('registration_id') + 1;
            $regNum = 'REG-' . date('Y') . '-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            $reg = ProgramRegistration::create([
                'registration_number' => $regNum,
                'program_id' => $program->program_id,
                'lab_id' => $lab->lab_id,
                'referral_id' => $appliedRefCode ? $appliedRefCode->referral_id : null,
                'sample_quantity' => $validated['sample_quantity'],
                'shipping_address' => $validated['shipping_address'],
                'billing_address' => $validated['billing_address'],
                'discount_applied' => $discountAmount,
                'status' => 'confirmed',
                'registered_at' => now(),
            ]);

            // 2. Mark Referral Code as used if One Time Use rule is enabled
            if ($appliedRefCode && $appliedRefCode->is_one_time_use) {
                $appliedRefCode->update([
                    'is_used' => 1,
                    'used_by_lab_id' => $lab->lab_id,
                    'used_at' => now(),
                ]);
            }

            // Generate Stripe PaymentIntent / Transaction ID
            $stripeService = new \App\Services\StripeService();
            $stripeIntent = $stripeService->createPaymentIntent($finalAmount, "PT Scheme Registration - {$program->program_code}", [
                'program_code' => $program->program_code,
                'lab_id' => $lab->lab_id,
            ]);
            $txnId = $validated['stripe_payment_intent_id'] ?? $stripeIntent['payment_intent_id'];

            // 3. Create Successful Payment Record
            $payment = Payment::create([
                'registration_id' => $reg->registration_id,
                'amount' => $originalFee,
                'discount_amount' => $discountAmount,
                'final_amount' => $finalAmount,
                'payment_method' => $validated['payment_method'],
                'transaction_id' => $txnId,
                'payment_status' => 'success',
                'paid_at' => now(),
                'created_at' => now(),
            ]);

            // 4. Dispatch Payment Confirmation Email
            try {
                $reg->load(['lab', 'program', 'payment']);
                Mail::to($lab->email)->send(new PaymentConfirmationMail($reg, $payment));
            } catch (\Exception $e) {
                // Soft fail if local mailer fails
            }

            return $reg;
        });

        return redirect()->route('user.dashboard')->with('success', "Registration completed successfully! Your Registration Number is {$registration->registration_number}. Tax Invoice and scheme details are now available in your Dashboard.");
    }

    public function viewInvoice($registration_id)
    {
        $lab = Auth::guard('lab')->user();
        $registration = ProgramRegistration::with(['program.parameters', 'payment', 'lab'])
            ->where('lab_id', $lab->lab_id)
            ->findOrFail($registration_id);

        return view('user.invoice', compact('registration', 'lab'));
    }
}
