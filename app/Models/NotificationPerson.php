<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotificationPerson extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'notifications_persons';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $fillable = [
        'notification_id',
        'user_id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    public function notifications()
    {
        return $this->belongsTo(Notification::class, 'notification_id');
    }
    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
