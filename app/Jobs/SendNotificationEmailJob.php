<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Notification;
use App\Mail\NotificationMail;
use App\Models\NotificationPerson;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class SendNotificationEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $notification;

    /**
     * Create a new job instance.
     */
    public function __construct(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $users = $this->notification->send_all
            ? User::all()
            : User::whereIn('id', NotificationPerson::where('notification_id', $this->notification->id)->pluck('user_id'))->get();

        foreach ($users as $user) {
            Mail::to($user->email)->send(new NotificationMail($this->notification));
        }

        // Cập nhật trạng thái sau khi gửi
        $this->notification->status = 1;
        $this->notification->save();
    }
}
