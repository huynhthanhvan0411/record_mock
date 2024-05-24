<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\AuhtRequest;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Stmt\TryCatch;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'refresh', 'register']]);
    }

    public function register(AuhtRequest $request)
    {
        if (User::where('email', $request->email)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Email already exists'
            ], Response::HTTP_BAD_REQUEST);
        }
        DB::beginTransaction();
        try {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);

            $user->save();
            event(new Registered($user));
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'user' => $user
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login()
    {
        $credentials = request(['email', 'password']);
        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out'], Response::HTTP_OK);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(Auth::refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }

    public function checkEmail()
    {
        try {
            if (User::where('email', request('email'))->exists()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Email already exists'
                ], Response::HTTP_BAD_REQUEST);
            }
            return response()->json(['success' => false], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function createResetPasswordToken(Request $request)
    {
        try {
            $user = User::where('email', $request->email)->first();
            // check exist mail
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], Response::HTTP_NOT_FOUND);
            }
            $token = Password::createToken($user);

            // Send the token via plain email for testing
            Mail::raw('Here is your password reset token: ' . $token, function ($message) use ($user) {
                $message->to($user->email)
                    ->subject('Reset Password Notification');
            });
            // Return success response
            return response()->json([
                'success' => true,
                'message' => 'Reset password link sent to your email.',
                'token' => $token // Including token in response for testing purposes
            ], Response::HTTP_OK);
            $user->createToken('password-reset')->plainTextToken;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|string|confirmed|min:8',
        ]);
        try {
            // Check if the token is valid
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->forceFill([
                        'password' => Hash::make($password)
                    ])->save();

                    // Here you can add code to invalidate all previous tokens if you are using JWT or Passport
                }
            );

            // Check the status and return the appropriate response
            if ($status == Password::PASSWORD_RESET) {
                return response()->json([
                    'success' => true,
                    'message' => 'Password reset successfully.'
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid token or email.'
                ], Response::HTTP_BAD_REQUEST);
            }
        } catch (\Exception $e) {
            // Log error and return response
            Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Unable to reset password.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
