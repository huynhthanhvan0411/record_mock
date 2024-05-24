<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CheckIn extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'check_in';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'check_in',
    ];
    public function users()
    {
        return $this->belongsTo(User::class);
    }
}
