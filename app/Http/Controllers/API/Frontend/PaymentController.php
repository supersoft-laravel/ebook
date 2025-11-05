<?php

namespace App\Http\Controllers\API\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends Controller
{
    public function createPaymentIntent(Request $request)
    {
        try {
            $user = $request->user();
            $amount = $request->amount; // Example: 10.50

            if (!$amount) {
                return response()->json(['error' => 'Amount is required'], 400);
            }

            Stripe::setApiKey(env('STRIPE_SECRET'));

            $intent = PaymentIntent::create([
                'amount' => round($amount * 100), // Convert to cents
                'currency' => 'usd',
                'metadata' => [
                    'user_id' => $user->id,
                    'email' => $user->email,
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            return response()->json([
                'clientSecret' => $intent->client_secret,
            ]);
        } catch (\Exception $e) {
            Log::error('Stripe PaymentIntent Failed', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
