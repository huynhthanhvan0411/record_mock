<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'attendances';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'date','check_in','check_out','user_id', 'total_hours', 'late_minutes'
    ];
    public function users()
    {
        return $this->belongsTo(User::class);
    }
}
