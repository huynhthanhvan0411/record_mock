<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;
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

}
