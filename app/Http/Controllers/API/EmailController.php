<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Auth\Events\Verified;

class EmailController extends Controller
{
    //check mail exist and verified
    public function checkVerifyEmail(EmailVerificationRequest $request)
    {
        DB::beginTransaction();
        try {
            if ($request->user()->hasVerifiedEmail()) {
                return response()->json(['message' => 'Email already verified'], Response::HTTP_BAD_REQUEST);
            }
            $request->user()->markEmailAsVerified();
            event(new Verified($request->user()));
            DB::commit();
            return response()->json(['message' => 'Email verified successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return response()->json(['message' => 'Email not verified'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    // send mail for email verification
    public function sendVerificationEmail(Request $request)
    {
        try {
            if ($request->user()->hasVerifiedEmail()) {
                return response()->json(['message' => __('Email already verified')], Response::HTTP_BAD_REQUEST);
            }

            $request->user()->sendEmailVerificationNotification();

            return response()->json(['message' => __('Email verification link sent on your email id.')], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return response()->json(['error' => __('Unable to send email verification link')], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
