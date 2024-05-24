<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profile extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'profiles';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'phone',
        'address',
        'birthday',
        'gender',
        'avatar',
        'position_id',
        'division_id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    //user 
    public function users()
    {
        return $this->belongsTo(User::class);
    }
    // position 
    public function positions()
    {
        return $this->belongsTo(Position::class);
    }
    //division 
    public function divisions()
    {
        return $this->belongsTo(Division::class);
    }
    //format date 
    public function format_date($date)
    {
        return Carbon::parse($date)->format('d-m-Y');
    }

    //format gender 
    public function gender($gender){
        if($gender == 1){
            return 'Nam';
        }else if($gender == 2){
            return 'Nữ';
        }
        return 'Khác';
    }
}
