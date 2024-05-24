<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Profile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
class EmployeeController extends Controller
{
    public function __construct(){
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }
    public function login(){
        try{
            $user = User::where('email', request('email'))
                ->where('status', 1)
                ->where('role_id', 2)
                ->first();
            $credentials = request(['email', 'password']);
            if (! $token = auth()->attempt($credentials)) {
                return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
            }
            if(!$user)
                return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
            return $this->respondWithToken($token);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Error',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function logout()
    {
        try {
            auth()->logout();
            return response()->json(['message' => 'Successfully logged out'], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function me()
    {
        $user = auth()->user();

        try {
            $profile = Profile::where('user_id', $user->id)
            ->select(
                'users.name as Name',
                'users.email as Email',
                'positions.name as Position',
                'divisions.name as Division',
                'profiles.phone as Phone',
                DB::raw('DATE_FORMAT(profiles.birthday, "%d/%m/%Y") as Birthday'),
                DB::raw('CASE WHEN profiles.gender = 1 THEN "Nam" WHEN profiles.gender = 2 THEN "Nữ" ELSE "Khác" END as Gender'),
                'profiles.avatar as Avatar',
                'profiles.address as Address'
            )
            ->leftJoin('users', 'profiles.user_id', '=', 'users.id')
            ->leftJoin('positions', 'profiles.position_id', '=', 'positions.id')
            ->leftJoin('divisions', 'profiles.division_id', '=', 'divisions.id')
            ->first();
            if (!$profile) {
                return response()->json([
                    'success' => true,
                    'user' => [
                        'Name' => $user->name,
                        'Email' => $user->email,
                        'Message' => 'Profile information needs to be added.'
                    ]
                ], Response::HTTP_OK);
            }

            return response()->json([
                'success' => true,
                'user' => $profile
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function refresh()
    {
        return $this->respondWithToken(Auth::refresh());
    }
    public function detailEmployeeOthers(Request $request){
        $name = $request->name;

        try{
            $profile = Profile::select(
                    'users.name as Name',
                    'users.email as Email',
                    'positions.name as Position',
                    'divisions.name as Division',
                    'profiles.phone as Phone',
                    DB::raw('DATE_FORMAT(profiles.birthday, "%d/%m/%Y") as Birthday'),
                    DB::raw('CASE WHEN profiles.gender = 1 THEN "Nam" WHEN profiles.gender = 2 THEN "Nữ" ELSE "Khác" END as Gender'),
                    'profiles.avatar as Avatar',
                    'profiles.address as Address'
                )
                ->leftJoin('users', 'profiles.user_id', '=', 'users.id')
                ->leftJoin('positions', 'profiles.position_id', '=', 'positions.id')
                ->leftJoin('divisions', 'profiles.division_id', '=', 'divisions.id')
                ->where('users.name', 'like', '%'.$name.'%')
                ->get();
            if(is_null($profile)){
                return response()->json(['message' => 'Profile not found'], Response::HTTP_NOT_FOUND);
            }
            return response()->json(['sucess'=>true, ' this is detail user:' => $profile], Response::HTTP_OK);
        }catch(\Exception $e){
            return response()->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    public function changeInfo(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'position_id' => 'sometimes|integer|exists:positions,id',
            'division_id' => 'sometimes|integer|exists:divisions,id',
            'phone' => 'sometimes|string|max:15',
            'address' => 'sometimes|string|max:255',
            'birthday' => 'sometimes|date_format:Y-m-d',
            'gender' => 'sometimes|in:1,2,3',
            'avatar' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        DB::beginTransaction();
        try {
            // Update user information
            if ($request->has('name')) {
                $user->name = $request->name;
            }
            if ($request->has('email')) {
                $user->email = $request->email;
            }
            $user->save();

            // Update profile information
            $profile = Profile::firstOrNew(['user_id' => $user->id]);

            if ($request->has('phone')) {
                $profile->phone = $request->phone;
            }
            if ($request->has('address')) {
                $profile->address = $request->address;
            }
            if ($request->has('birthday')) {
                $profile->birthday = $request->birthday;
            }
            if ($request->has('gender')) {
                $profile->gender = $request->gender;
            }
            if ($request->hasFile('avatar')) {
                $avatar = $request->file('avatar');
                $avatarName = time() . '.' . $avatar->getClientOriginalExtension();
                $avatar->move(public_path('images'), $avatarName);
                $profile->avatar = $avatarName;
            }
            if ($request->has('position_id')) {
                $profile->position_id = $request->position_id;
            }
            if ($request->has('division_id')) {
                $profile->division_id = $request->division_id;
            }
            $profile->save();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Profile updated successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return response()->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
