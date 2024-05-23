<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;
    use HasFactory;
    protected $table = 'positions';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'name', 'status'
    ];
    public function profiles()
    {
        return $this->hasMany(Profile::class);
    }
}
