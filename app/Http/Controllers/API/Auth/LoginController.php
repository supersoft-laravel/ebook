<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class LoginController extends Controller
{
    /**
     * User Login Attempt for API
     */
    public function login_attempt(Request $request)
    {
        // Validate the input
        $rules = [
            'email' => 'required|max:50',
            'password' => 'required',
            'fcm_token' => 'nullable|string'
        ];

        // If Captcha is enabled, validate captcha response
        if (config('captcha.version') !== 'no_captcha') {
            $rules['g-recaptcha-response'] = 'required|captcha';
        } else {
            $rules['g-recaptcha-response'] = 'nullable';
        }

        // Validate the request
        $validate = Validator::make($request->all(), $rules);

        if ($validate->fails()) {
            return response()->json([
                'message' => 'Validation Error!',
                'errors' => $validate->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            // Determine whether the input is an email or username
            $userfind = null;
            if (filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
                // If it's an email, search by email
                $userfind = User::where('email', $request->email)->first();
            } else {
                // If it's not an email, assume it's a username and search by username
                $userfind = User::where('username', $request->email)->first();
            }

            if ($userfind) {
                // Check if the password is correct
                if (Hash::check($request->password, $userfind->password)) {
                    // Generate a new Sanctum token for the user
                    $token = $userfind->createToken($userfind->name, ['auth_token'])->plainTextToken;

                    if($request->fcm_token){
                        // 🔥 Delete existing same FCM token (if assigned to another user)
                        UserDevice::where('fcm_token', $request->fcm_token)
                            ->where('user_id', '!=', $userfind->id)
                            ->delete();
                            
                        UserDevice::updateOrCreate(
                            [
                                'user_id' => $userfind->id,
                            ],
                            [
                                'fcm_token' => $request->fcm_token,
                            ]
                        );
                    }

                    return response()->json([
                        'message' => 'Login successfully!',
                        'user' => $userfind->only(['id', 'name', 'email']),
                        'token' => $token
                    ], Response::HTTP_OK);
                } else {
                    return response()->json([
                        'message' => 'Password is incorrect.'
                    ], Response::HTTP_UNAUTHORIZED);
                }
            } else {
                return response()->json([
                    'message' => 'User not found.'
                ], Response::HTTP_UNAUTHORIZED);
            }
        } catch (\Throwable $th) {
            Log::error("Failed to Login: " . $th->getMessage());

            return response()->json([
                'message' => 'Something went wrong! Please try again later.',
                'error' => $th->getMessage(),  // <-- TEMPORARY
                'line' => $th->getLine(),      // <-- TEMPORARY
                'file' => $th->getFile(),      // <-- TEMPORARY
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
