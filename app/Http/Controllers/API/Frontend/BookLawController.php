<?php

namespace App\Http\Controllers\API\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookLaw;
use App\Models\UserFavourite;
use App\Models\UserPurchase;
use App\Models\UserRead;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class BookLawController extends Controller
{
    private function userCanAccessLaw(int $userId, int $lawId): bool
    {
        // Load book (assuming single book setup)
        $book = Book::with('bookLaws')->firstOrFail();

        // Check if purchased
        $isPurchased = UserPurchase::where([
            ['user_id', $userId],
            ['book_id', $book->id],
            ['payment_status', 'paid'],
        ])->exists();

        // If purchased → can access all laws
        if ($isPurchased) {
            return $book->bookLaws->contains('id', $lawId);
        }

        // Not purchased → only first free_laws
        $accessibleLawIds = $book->bookLaws()
            ->orderBy('id')
            ->limit($book->free_laws)
            ->pluck('id')
            ->toArray();

        return in_array($lawId, $accessibleLawIds);
    }

    public function addToFavourite(Request $request, $id)
    {
        try {
            $userId = $request->user()->id;
            $user = $request->user();

            // ✅ Use helper function
            if (!$this->userCanAccessLaw($userId, $id)) {
                return response()->json([
                    'message' => 'You cannot favourite this law without purchase.'
                ], Response::HTTP_FORBIDDEN);
            }

            // Toggle favourite
            $userFavourite = UserFavourite::where('user_id', $userId)
                ->where('book_law_id', $id)
                ->first();

            $bookLaw = BookLaw::with('book')->find($id);

            if ($userFavourite) {
                $userFavourite->delete();
                $message = 'Law removed from favourites successfully!';

                app('notificationService')->notifyUsers([$user], 'Removed from Favourite', "Law #{$bookLaw->id} of book {$bookLaw->book->name} has been removed from your favourites");
            } else {
                $userFavourite = new UserFavourite();
                $userFavourite->user_id = $userId;
                $userFavourite->book_law_id = $id;
                $userFavourite->save();
                $message = 'Law added to favourites successfully!';

                app('notificationService')->notifyUsers([$user], 'Added to Favourite', "Law #{$bookLaw->id} of book {$bookLaw->book->name} has been added to your favourites");
            }

            return response()->json([
                'message' => $message
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error('API Add to Favourite failed', ['error' => $th->getMessage()]);
            return response()->json([
                'message' => 'Something went wrong!'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function markAsReadLaw(Request $request, $id)
    {
        try {
            $userId = $request->user()->id;
            $user = $request->user();

            // ✅ Use helper to ensure law is accessible
            if (!$this->userCanAccessLaw($userId, $id)) {
                return response()->json([
                    'message' => 'You cannot mark this law as read without purchase.'
                ], Response::HTTP_FORBIDDEN);
            }

            $userRead = UserRead::where('user_id', $userId)
                ->where('book_law_id', $id)
                ->first();

            if ($userRead) {
                $message = 'Law already marked as read!';
            } else {
                $userRead = new UserRead();
                $userRead->user_id = $userId;
                $userRead->book_law_id = $id;
                $userRead->save();
                $message = 'Law marked as read successfully!';

                $bookLaw = BookLaw::with('book')->find($id);
                app('notificationService')->notifyUsers([$user], 'Marked as Read', "Law #{$bookLaw->id} of book {$bookLaw->book->name} has been marked as read.");
            }


            return response()->json([
                'message' => $message
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error('API Mark as Read failed', ['error' => $th->getMessage()]);
            return response()->json([
                'message' => 'Something went wrong!'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getLaws(Request $request)
    {
        try {
            $user = $request->user();
            $book = Book::firstOrFail();

            // Check if purchased
            $isPurchased = UserPurchase::where('user_id', $user->id)
                ->where('book_id', $book->id)
                ->where('payment_status', 'paid')
                ->exists();

            // Fetch law IDs in user favourites (for quick lookup)
            $favouriteLawIds = UserFavourite::where('user_id', $user->id)
                ->pluck('book_law_id')
                ->toArray();

            // Build query for laws with only required fields
            $query = BookLaw::where('book_id', $book->id)
                ->select('id', 'title', 'content');

            if (!$isPurchased) {
                $query->limit($book->free_laws);
            }

            $laws = $query->get()->map(function ($law) use ($favouriteLawIds) {
                return [
                    'id'          => $law->id,
                    'title'       => $law->title,
                    'content'     => $law->content,
                    'is_favourite' => in_array($law->id, $favouriteLawIds),
                ];
            });

            return response()->json([
                'laws' => $laws
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error('API get laws failed', ['error' => $th->getMessage()]);
            return response()->json([
                'message' => 'Something went wrong!'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
