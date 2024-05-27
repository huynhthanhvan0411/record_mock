<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CheckIn;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Profile;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Attendance;
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

    public function historyDaily(Request $request)
    {
        $user = Auth::user();
        try {

            $checkInDay = DB::table('check_in')
                ->where('user_id', $user->id) 
                ->get();

            return response()->json([
                'success' => true,
                'Welcome' => $user->name,
                'Check-in history' => $checkInDay
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
   public function checkIn(Request $request)
    {
        $user = Auth::user();
        $date = Carbon::now();
        $total_hours = 0;
        $late_minutes = 0;

        DB::beginTransaction();

        try {
            // Tạo bản ghi check-in mới
            $checkInRecord = CheckIn::create([
                'user_id' => $user->id,
                'check_in' => $date,
            ]);

            // Lấy tất cả các check-in của ngày hôm nay cho người dùng này
            $today = Carbon::today();
            $checkIns = CheckIn::where('user_id', $user->id)
                ->whereDate('check_in', $today)
                ->orderBy('check_in', 'asc')
                ->get();

            // Nếu có ít nhất một check-in
            if ($checkIns->count() > 0) {
                $firstCheckIn = $checkIns->first();
                $lastCheckIn = $checkIns->last();

                $checkInTime = Carbon::parse($firstCheckIn->check_in);
                $checkOutTime = Carbon::parse($lastCheckIn->check_in);

                // Tính số phút đi muộn
                $lateMinutes = max(0, $checkInTime->diffInMinutes(Carbon::createFromTime(8, 0), false));

                // Tính số giờ làm việc
                $morningStart = Carbon::createFromTime(8, 0); // Buổi sáng bắt đầu từ 8:00
                $morningEnd = Carbon::createFromTime(12, 0);   // Buổi sáng kết thúc vào 12:00
                $afternoonStart = Carbon::createFromTime(13, 30); // Buổi chiều bắt đầu từ 13:30
                $afternoonEnd = Carbon::createFromTime(17, 30);   // Buổi chiều kết thúc vào 17:30
                
                if ($checkInTime->lte($morningEnd) && $checkOutTime->gte($morningStart)) {
                    // Tính số giờ làm việc trong buổi sáng
                    $morningWorkHours = min($morningEnd, $checkOutTime)->diffInHours(max($morningStart, $checkInTime));
                    $total_hours += $morningWorkHours;
                }

                if ($checkInTime->lte($afternoonEnd) && $checkOutTime->gte($afternoonStart)) {
                    // Tính số giờ làm việc trong buổi chiều
                    $afternoonWorkHours = min($afternoonEnd, $checkOutTime)->diffInHours(max($afternoonStart, $checkInTime));
                    $total_hours += $afternoonWorkHours;
                }
            }
            $attendance = Attendance::where('user_id', $user->id)
                ->whereDate('date', $today)
                ->first();
            if ($attendance) {
                $attendance->check_in = $firstCheckIn->check_in;
                $attendance->check_out = $lastCheckIn->check_in;
                $attendance->total_hours = $total_hours;
                $attendance->late_minutes = $lateMinutes;
                $attendance->save();
            } else {
                Attendance::create([
                    'user_id' => $user->id,
                    'date' => $today,
                    'check_in' => $firstCheckIn->check_in,
                    'check_out' => $lastCheckIn->check_in,
                    'total_hours' => $total_hours,
                    'late_minutes' => $lateMinutes,
                ]);
            }
            DB::commit();
            return response()->json([
                'message' => 'Check-in success',
                'Welcome' => $user->name,
                'check_in' => $firstCheckIn->check_in,
                'check_out' => $lastCheckIn->check_in,
                'late_minutes' => $lateMinutes,
                'total_hours' => $total_hours,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return response()->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}

