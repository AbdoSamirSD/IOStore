<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeviceToken extends Model
{
    use HasFactory;
    protected $fillable = ['token', 'device_type', 'user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


}
