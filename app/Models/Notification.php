<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'notifications';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'subject',
        'message',
        'send_all',
        'scheduled_time',
        'status',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function notification_persons()
    {
        return $this->hasMany(NotificationPerson::class, 'notification_id');
    }
    
}
