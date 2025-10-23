<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Biodata;
use App\Models\Book;
use App\Models\Course;
use App\Models\Review;
use App\Models\Subcourse;
use App\Models\UserFavourite;
use App\Models\UserPurchase;
use App\Models\UserRead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class HomeController extends Controller
{
    public function home(Request $request)
    {
        try {
            $userId = $request->user()->id;

            // Fetch counts in fewer queries
            $userFavouritesCount = UserFavourite::where('user_id', $userId)->count();
            $userReadsCount      = UserRead::where('user_id', $userId)->count();

            // Load book with law count directly (no need to pull all relations)
            $book = Book::withCount('bookLaws')->firstOrFail();

            // Remaining laws
            $userRemainingCount = max(0, $book->book_laws_count - $userReadsCount);

            // Check purchase existence (optimized exists query)
            $isPurchased = UserPurchase::where('user_id', $userId)
                ->where('book_id', null)
                ->where('payment_status', 'paid')
                ->exists();
            // $isPurchased = UserPurchase::where([
            //     ['user_id', $userId],
            //     ['book_id', $book->id],
            //     ['payment_status', 'paid'],
            // ])->exists();

            // If purchased → random from all laws
            // if ($isPurchased) {
                $lawOfTheDay = $book->bookLaws()->inRandomOrder()->first();
            // } else {
            //     $freeLawIds = $book->bookLaws()
            //         ->orderBy('id')
            //         ->limit($book->free_laws)
            //         ->pluck('id');

            //     $lawOfTheDay = $book->bookLaws()
            //         ->whereIn('id', $freeLawIds)
            //         ->inRandomOrder()
            //         ->first();
            // }

            // Check if the selected law is in favourites
            $isFavourite = $lawOfTheDay
                ? UserFavourite::where('user_id', $userId)
                ->where('book_law_id', $lawOfTheDay->id)
                ->exists()
                : false;

            // Check if the selected law is in reads
            $isRead = $lawOfTheDay
                ? UserRead::where('user_id', $userId)
                ->where('book_law_id', $lawOfTheDay->id)
                ->exists()
                : false;

            return response()->json([
                'user_favourites_count' => $userFavouritesCount,
                'user_reads_count'      => $userReadsCount,
                'user_remaining_count'  => $userRemainingCount,
                'is_purchased'          => $isPurchased,
                'law_of_the_day'     => $lawOfTheDay ? [
                    'id'      => $lawOfTheDay->id,
                    'name'    => $lawOfTheDay->title,
                    'content' => $lawOfTheDay->content,
                    'is_favourite' => $isFavourite,
                    'is_read' => $isRead,
                ] : null,
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error('API Home failed', ['error' => $th->getMessage()]);
            return response()->json([
                'message' => 'Something went wrong!'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
