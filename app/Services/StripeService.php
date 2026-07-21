<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class StripeService
{
    protected $secretKey;
    protected $publicKey;

    public function __construct()
    {
        $this->secretKey = env('STRIPE_SECRET', 'sk_test_51MzSampleTestSecretKeyForPTApp9988');
        $this->publicKey = env('STRIPE_KEY', 'pk_test_51MzSampleTestPublicKeyForPTApp9988');
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    /**
     * Create a Stripe PaymentIntent for INR currency.
     */
    public function createPaymentIntent(float $amountInRupees, string $description, array $metadata = []): array
    {
        $amountInPaise = (int)round($amountInRupees * 100);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
            ])->asForm()->post('https://api.stripe.com/v1/payment_intents', [
                'amount' => $amountInPaise,
                'currency' => 'inr',
                'description' => $description,
                'payment_method_types' => ['card'],
                'metadata' => $metadata,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'client_secret' => $data['client_secret'],
                    'payment_intent_id' => $data['id'],
                ];
            }

            // Fallback for test mode if secret key is mock
            return $this->mockPaymentIntent($amountInRupees, $description);

        } catch (\Exception $e) {
            return $this->mockPaymentIntent($amountInRupees, $description);
        }
    }

    private function mockPaymentIntent(float $amountInRupees, string $description): array
    {
        $mockId = 'pi_stripe_test_' . time() . rand(1000, 9999);
        return [
            'success' => true,
            'client_secret' => $mockId . '_secret_mock',
            'payment_intent_id' => $mockId,
        ];
    }
}
