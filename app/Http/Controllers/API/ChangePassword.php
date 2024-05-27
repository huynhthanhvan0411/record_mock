<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Support\Facades\Hash;

class ChangePassword extends Controller
{
    public function __construct()
    {
        return $this->middleware('api')->except('passwordResetProcess');
    }
    public function passwordResetProcess(UpdatePasswordRequest $request)
    {
        return $this->updatePasswordRow($request)->count() > 0 ? $this->resetPassword($request) : $this->tokenNotFoundError();
    }

    // Verify if token is valid
    private function updatePasswordRow($request)
    {
        return DB::table('password_reset_tokens')->where([
            'email' => $request->email,
            'token' => $request->resetToken
        ]);
    }

    // Token not found response  
    private function tokenNotFoundError()
    {
        return response()->json([
            'error' => 'Either your email or token is wrong.'
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    // Reset password
    private function resetPassword($request)
    {
        // find email
        $userData = User::whereEmail($request->email)->first();
        // update password
        $userData->update([
            'password' => Hash::make($request->password)
        ]);
        // remove verification data from db
        $this->updatePasswordRow($request)->delete();

        // reset password response
        return response()->json([
            'data' => 'Password has been updated.'
        ], Response::HTTP_CREATED);
    } 
}
