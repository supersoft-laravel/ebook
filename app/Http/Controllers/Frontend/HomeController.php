<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    public function privacyPolicy()
    {
        return view('frontend.privacy-policy');
    }

    public function deleteAccount()
    {
        return view('frontend.delete-account');
    }
    public function deleteAccountRequest(Request $request)
    {
        $rules = [
            'email_username' => 'required|max:50',
            'password' => 'required',
        ];

        // If captcha is used
        // if (config('captcha.version') !== 'no_captcha') {
        //     $rules['g-recaptcha-response'] = 'required|captcha';
        // } else {
        //     $rules['g-recaptcha-response'] = 'nullable';
        // }

        $validate = Validator::make($request->all(), $rules);
        if ($validate->fails()) {
            return Redirect::back()->withErrors($validate)->withInput($request->all())->with('error', 'Validation Error!');
        }

        try {
            DB::beginTransaction();
            // Determine whether the input is an email or username
            $userfind = null;
            if (filter_var($request->email_username, FILTER_VALIDATE_EMAIL)) {
                // If it's an email, search by email
                $userfind = User::where('email', $request->email_username)->first();
            } else {
                // If it's not an email, assume it's a username and search by username
                $userfind = User::where('username', $request->email_username)->first();
            }

            if ($userfind) {
                // Check if the password is correct
                if (Hash::check($request->password, $userfind->password)) {
                    // Password matched
                    Auth::attempt(['email' => $userfind->email, 'password' => $request->password]);

                    if (Auth::check()) {
                        $userfind->delete();
                        Auth::logout();

                        DB::commit();
                        return redirect()->route('frontend.delete-account')->with('success', 'Your account has been deleted successfully.');
                    } else {
                        return redirect()->back()->withInput($request->all())->with('error', 'Authentication Error');
                    }
                } else {
                    return redirect()->back()->withInput($request->all())->with('error', 'Password is mismatch');
                }
            } else {
                return redirect()->back()->withInput($request->all())->with('error', "Invalid credentials");
            }
        } catch (\Throwable $th) {
            // throw $th;
            DB::rollBack();
            Log::error('User Account Delete Failed', ['error' => $th->getMessage()]);
            return redirect()->back()->with('error', "Something went wrong! Please try again later");
        }
    }
}
