<?php

namespace App\Http\Controllers\API\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Billing;
use App\Models\Book;
use App\Models\UserPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Stripe\Stripe;
use Stripe\Charge;

class StoreController extends Controller
{
    public function getStore(Request $request)
    {
        try {
            $user = $request->user();
            $book = Book::firstOrFail();
            $books = Book::with('bookType')->where('id', '!=', $book->id)->get();

            // Check if purchased
            $isPurchased = UserPurchase::where('user_id', $user->id)
                ->where('book_id', null)
                ->where('payment_status', 'paid')
                ->exists();

            $data = $books->map(function ($book) use ($user) {
                return [
                    'id' => $book->id,
                    'title' => $book->title,
                    'slug' => $book->slug,
                    'price' => $book->price,
                    'amazon_link' => $book->amazon_link ?? null,
                    'book_type' => $book->bookType->name ?? null,
                    'description' => $book->description ? Str::limit($book->description, 20, '...') : null,
                    'image' => url($book->image) ?? null,
                    'is_purchased' => UserPurchase::where('user_id', $user->id)
                        ->where('book_id', $book->id)
                        ->where('payment_status', 'paid')
                        ->exists(),
                ];
            });

            return response()->json([
                'complete_books' => $data,
                'is_premium' => $isPurchased
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error('API get store failed', ['error' => $th->getMessage()]);
            return response()->json([
                'message' => 'Something went wrong!'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function checkout(Request $request)
    {
        try {
            $user = $request->user();
            $billing = Billing::where('user_id', $user->id)->first();
            if (!$billing) {
                $billing = [];
            } else {
                $billing = [
                    'firstname' => $billing->firstname,
                    'lastname' => $billing->lastname,
                    'email' => $billing->email,
                    'phone' => $billing->phone,
                    'address' => $billing->address,
                    'city' => $billing->city,
                    'state' => $billing->state,
                    'zip' => $billing->zip,
                    'country' => $billing->country,
                ];
            }

            return response()->json([
                'billing' => $billing,
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error('API get checkout details failed', ['error' => $th->getMessage()]);
            return response()->json([
                'message' => 'Something went wrong!'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function checkoutSubmit(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'firstname' => 'required|string',
                'lastname' => 'required|string',
                'email' => 'required|email',
                'phone' => 'required|string',
                'address' => 'required|string',
                'city' => 'required|string',
                'state' => 'required|string',
                'zip' => 'required|string',
                'country' => 'required|string',
                'payment_type' => 'required|in:card,paypal,stripe,cod,authorize.net',
                'price' => 'required|string',
                'book_id' => 'nullable|exists:books,id',
                // 'stripeToken' => 'required_if:payment_type,stripe|nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], Response::HTTP_BAD_REQUEST);
            }

            // ✅ Save or update billing info
            $billing = Billing::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'firstname' => $request->firstname,
                    'lastname' => $request->lastname,
                    'email' => $request->email,
                    'phone' => $request->phone,
                    'address' => $request->address,
                    'city' => $request->city,
                    'state' => $request->state,
                    'zip' => $request->zip,
                    'country' => $request->country,
                ]
            );

            // if ($request->payment_type === 'stripe') {
            //     Stripe::setApiKey(env('STRIPE_SECRET'));

            //     try {
            //         $charge = Charge::create([
            //             'amount' => $request->price * 100, // Convert to cents
            //             'currency' => 'usd',
            //             'source' => $request->stripeToken,
            //             'description' => 'Payment for book ID: ' . $request->book_id,
            //             'receipt_email' => $request->email, // Billing email
            //             'metadata' => [
            //                 'billing_first_name' => $request->firstname,
            //                 'billing_last_name' => $request->lastname,
            //                 'billing_email' => $request->email,
            //                 'billing_phone' => $request->phone,
            //                 'billing_address' => $request->address,
            //                 'billing_city' => $request->city,
            //                 'billing_state' => $request->state,
            //                 'billing_zip' => $request->zip,
            //                 'billing_country' => $request->country,
            //             ],
            //         ]);
            //     } catch (\Exception $e) {
            //         DB::rollBack();
            //         Log::error('Stripe Charge Failed', ['error' => $e->getMessage()]);
            //         return response()->json([
            //             'success' => false,
            //             'message' => 'Payment could not be processed: ' . $e->getMessage(),
            //         ], Response::HTTP_INTERNAL_SERVER_ERROR);
            //     }

            //     if ($charge->status !== 'succeeded') {
            //         DB::rollBack();
            //         Log::error('Stripe Payment Failed', ['charge' => $charge]);
            //         return response()->json([
            //             'success' => false,
            //             'message' => 'Payment failed. Please try again.',
            //         ], Response::HTTP_INTERNAL_SERVER_ERROR);
            //     }
            // }
            if ($request->payment_type === 'stripe') {
                // No need to charge again, payment already done on frontend
                $paymentStatus = 'paid';
            } else {
                // Handle other payment types if needed
                $paymentStatus = 'pending';
            }

            $order = new UserPurchase();
            $order->order_no = 'TEMP';
            $order->user_id = $user->id;
            $order->billing_id = $billing->id;
            $order->book_id = $request->book_id;
            $order->payment_type = $request->payment_type;
            $order->amount = $request->price;
            $order->payment_status = 'paid';
            $order->save();

            $order->order_no = 'ORD-' . date('Y') . '-' . str_pad($order->id, 3, '0', STR_PAD_LEFT);
            $order->save();

            if($request->book_id){
                $order = new UserPurchase();
                $order->order_no = 'TEMP';
                $order->user_id = $user->id;
                $order->billing_id = $billing->id;
                $order->book_id = null;
                $order->payment_type = $request->payment_type;
                $order->amount = 0.00;
                $order->payment_status = 'paid';
                $order->save();

                $order->order_no = 'ORD-' . date('Y') . '-' . str_pad($order->id, 3, '0', STR_PAD_LEFT);
                $order->save();
            }

            app('notificationService')->notifyUsers([$user], 'Order Confirmed', "Order #{$order->order_no} has been confirmed.");

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'order_no' => $order->order_no
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('API checkoutSubmit failed', ['error' => $th->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong!'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getPurchaseHistory(Request $request)
    {
        try {
            $user = $request->user();
            // Check if purchased
            $purchases = UserPurchase::with('book','billing')->where('user_id', $user->id)
                ->get();

            $data = $purchases->map(function ($purchase) {
                return [
                    'id' => $purchase->id,
                    'payment_type' => $purchase->payment_type,
                    'amount' => $purchase->amount,
                    'payment_status' => $purchase->payment_status,
                    'book' => $purchase->book ?? null,
                    'purchase_date' => $purchase->created_at->format('M d, Y'),
                ];
            });

            return response()->json([
                'purchases' => $data,
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error('API get purchase history failed', ['error' => $th->getMessage()]);
            return response()->json([
                'message' => 'Something went wrong!'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
