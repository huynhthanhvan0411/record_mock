<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Notification;
use App\Models\NotificationPerson;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendNotificationEmailJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Jobs\SendMailJob;


class NotificationController extends Controller
{
    public function hi()
    {
        return Carbon::now();
    }

    public function check()
    {
        return response()->json(['message' => 'Notification scheduled successfully'], 200);
    }

    public function send(Request $request)
    {
        // Convert send_all to boolean if it exists
        if ($request->has('send_all')) {
            $request->merge(['send_all' => filter_var($request->send_all, FILTER_VALIDATE_BOOLEAN)]);
        }

        // Xác thực dữ liệu đầu vào
        $validator = Validator::make($request->all(), [
            'subject' => 'required|string',
            'message' => 'required|string',
            'send_all' => 'required|boolean',
            'user_ids' => 'required_if:send_all,false|array',
            'user_ids.*' => 'integer|exists:users,id'
        ]);

        // Kiểm tra nếu dữ liệu không hợp lệ
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // Tạo notification mới bằng cách sử dụng phương thức create
            $notification = Notification::create([
                'subject' => $request->subject,
                'message' => $request->message,
                'send_all' => $request->send_all,
                'scheduled_time' => Carbon::now()->addMinutes(2), // Thời gian gửi thông báo sau 2 phút
                'status' => 0, // Trạng thái ban đầu là chưa gửi
            ]);

            if (!$notification->send_all) {
                foreach ($request->user_ids as $userId) {
                    if (!$this->notificationAlreadySentToUser($userId, $notification->id)) {
                        NotificationPerson::create([
                            'notification_id' => $notification->id,
                            'user_id' => $userId,
                        ]);
                    }
                }
            }

            // Đặt job gửi email vào hàng đợi
            $delay = $notification->scheduled_time;
            $job = (new SendMailJob($notification))->delay($delay);
            dispatch($job);

            DB::commit();
            return response()->json(['message' => 'Notification scheduled successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to schedule notification', 'error' => $e->getMessage()], 500);
        }
    }

    private function calculateScheduledTime($scheduledTime)
    {
        $now = Carbon::now();
        [$hours, $minutes] = explode(':', $scheduledTime);

        if ($now->hour > $hours || ($now->hour == $hours && $now->minute > $minutes)) {
            return Carbon::tomorrow()->setHour($hours)->setMinute($minutes)->setSecond(0);
        } else {
            return Carbon::today()->setHour($hours)->setMinute($minutes)->setSecond(0);
        }
    }

    private function notificationAlreadySentToUser($userId, $notificationId)
    {
        return NotificationPerson::where('user_id', $userId)
            ->where('notification_id', $notificationId)
            ->exists();
    }
}
