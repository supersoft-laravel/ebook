<?php

namespace App\Http\Controllers\API\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Profile;
use App\Models\UserFavourite;
use App\Models\UserPurchase;
use App\Models\UserRead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends Controller
{
    public function getProfile(Request $request)
    {
        try {
            $user = $request->user();
            $profile = Profile::where('user_id', $user->id)->firstOrFail();

            // Fetch counts in fewer queries
            $userFavouritesCount = UserFavourite::where('user_id', $user->id)->count();
            $userReadsCount      = UserRead::where('user_id', $user->id)->count();

            // Load book with law count directly (no need to pull all relations)
            $book = Book::withCount('bookLaws')->firstOrFail();

            // Remaining laws
            $userRemainingCount = max(0, $book->book_laws_count - $userReadsCount);

            // Completion percentage
            $completionPercentage = $book->book_laws_count > 0
                ? round(($userReadsCount / $book->book_laws_count) * 100, 2)
                : 0;

            // Check purchase existence (optimized exists query)
            $isPurchased = UserPurchase::where([
                ['user_id', $user->id],
                ['book_id', $book->id],
                ['payment_status', 'paid'],
            ])->exists();



            return response()->json([
                'user_favourites_count' => $userFavouritesCount,
                'user_reads_count'      => $userReadsCount,
                'user_remaining_count'  => $userRemainingCount,
                'completion_percentage' => $completionPercentage,
                'is_purchased'          => $isPurchased,
                'user'     => [
                    'id'      => $user->id,
                    'name'    => $user->name,
                    'email' => $user->email,
                    'profile_image' => url($profile->profile_image),
                ],
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            Log::error('API Profile failed', ['error' => $th->getMessage()]);
            return response()->json([
                'message' => 'Something went wrong!'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateProfilePicture(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'profile_image' => 'required|image|mimes:jpeg,png,jpg|max_size',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            DB::beginTransaction();

            $user = $request->user();
            $profile = Profile::where('user_id', $user->id)->firstOrFail();
            if ($request->hasFile('profile_image')) {
                if (isset($profile->profile_image) && File::exists(public_path($profile->profile_image))) {
                    File::delete(public_path($profile->profile_image));
                }

                $profileImage = $request->file('profile_image');
                $profileImage_ext = $profileImage->getClientOriginalExtension();
                $profileImage_name = time() . '_profileImage.' . $profileImage_ext;

                $profileImage_path = 'uploads/profile-images';
                $profileImage->move(public_path($profileImage_path), $profileImage_name);
                $profile->profile_image = $profileImage_path . "/" . $profileImage_name;
            }
            $profile->save();

            app('notificationService')->notifyUsers([$user], 'Profile Updated', "Your profile picture has been updated successfully.");

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
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
}
