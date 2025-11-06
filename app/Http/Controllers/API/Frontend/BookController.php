<?php

namespace App\Http\Controllers\API\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookLaw;
use App\Models\UserFavourite;
use App\Models\UserPurchase;
use App\Models\UserRead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class BookController extends Controller
{
    public function allBooks(Request $request)
    {
        try {
            $book = Book::withCount('bookLaws')->first();

            $books = [];

            if ($book) {
                $books[] = [
                    'id'         => $book->id,
                    'title'      => $book->title,
                    'author'     => $book->author,
                    'price'      => $book->price,
                    'laws_count' => $book->book_laws_count,
                ];
            }

            return response()->json([
                'books' => $books,
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error('API All Books failed', ['error' => $th->getMessage()]);
            return response()->json(['message' => 'Something went wrong!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    // public function allBooks(Request $request)
    // {
    //     try {
    //         $query = Book::withCount('bookLaws');

    //         $books = $query->get()->map(function ($book) {
    //             return [
    //                 'id'             => $book->id,
    //                 'title'          => $book->title,
    //                 'author'         => $book->author,
    //                 'price'          => $book->price,
    //                 'laws_count'     => $book->book_laws_count,
    //             ];
    //         });

    //         return response()->json([
    //             'books' => $books,
    //         ], Response::HTTP_OK);
    //     } catch (\Throwable $th) {
    //         Log::error('API All Books failed', ['error' => $th->getMessage()]);
    //         return response()->json(['message' => 'Something went wrong!'], Response::HTTP_INTERNAL_SERVER_ERROR);
    //     }
    // }

    public function getLaws(Request $request, $id)
    {
        try {
            $user = $request->user();
            $book = Book::find($id);

            if (!$book) {
                return response()->json([
                    'message' => 'Book not found!'
                ], Response::HTTP_NOT_FOUND);
            }

            // Check if purchased
            $isPurchased = UserPurchase::where('user_id', $user->id)
                ->where('book_id', $book->id)
                ->where('payment_status', 'paid')
                ->exists();

            Log::info('User purchase status', [
                'user_id' => $user->id,
                'book_id' => $book->id,
                'is_purchased' => $isPurchased
            ]);

            // Fetch favourites & reads
            $favouriteLawIds = UserFavourite::where('user_id', $user->id)->pluck('book_law_id')->toArray();
            $readLawIds = UserRead::where('user_id', $user->id)->pluck('book_law_id')->toArray();

            if ($isPurchased) {

                $query = BookLaw::where('book_id', $book->id)
                    ->select('id', 'book_id', 'title', 'content');

                if ($user->read_mode === 'sequential') {
                    $query->orderBy('id', 'asc');
                } else {
                    $query->inRandomOrder();
                }

                $laws = $query->get();
            } else {
                // FREE USER
                $laws = BookLaw::where('book_id', $book->id)
                    ->select('id', 'book_id', 'title', 'content')
                    ->orderBy('id', 'asc') // always get first X
                    ->take($book->free_laws)
                    ->get()
                    ->toArray(); // convert to array

                if ($user->read_mode !== 'sequential') {
                    Log::alert('Shuffling ONLY first free laws', [
                        'user_id' => $user->id,
                        'book_id' => $book->id
                    ]);
                    shuffle($laws); // shuffle only the first free_laws
                }

                $laws = collect($laws);
            }

            // ✅ Map here only once!
            $laws = $laws->map(function ($law) use ($favouriteLawIds, $readLawIds) {
                return [
                    'id'           => $law['id'] ?? $law->id,
                    'book_id'      => $law['book_id'] ?? $law->book_id,
                    'title'        => $law['title'] ?? $law->title,
                    'content'      => $law['content'] ?? $law->content,
                    'is_favourite' => in_array($law['id'] ?? $law->id, $favouriteLawIds),
                    'is_read'      => in_array($law['id'] ?? $law->id, $readLawIds),
                ];
            });

            return response()->json([
                'laws' => $laws
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error('API get book laws failed', ['error' => $th->getMessage()]);
            return response()->json([
                'message' => 'Something went wrong!'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
