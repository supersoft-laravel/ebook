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
use Symfony\Component\HttpFoundation\Response;

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
                    'description' => $book->description ?? null,
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

            // ✅ Create order with a temporary placeholder order_no (to satisfy NOT NULL)
            $order = new UserPurchase();
            $order->order_no = 'TEMP'; // temporary
            $order->user_id = $user->id;
            $order->billing_id = $billing->id;
            $order->book_id = $request->book_id;
            $order->payment_type = $request->payment_type;
            $order->amount = $request->price;
            $order->payment_status = 'paid';
            $order->save();

            // ✅ Now generate real order number using the ID
            $order->order_no = 'ORD-' . date('Y') . '-' . str_pad($order->id, 3, '0', STR_PAD_LEFT);
            $order->save();

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
