<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'message', 'data', 'image', 'type', 'notifiable_id', 'notifiable_type', 'read_at'];

    protected $hidden = ['created_at', 'updated_at', 'notifiable_id', 'notifiable_type', 'read_at', 'image'];

    public function notifiable()
    {
        return $this->morphTo();
    }

    public function getDataAttribute($value)
    {
        return json_decode($value, true);
    }


}
