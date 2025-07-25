<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Services\CustomAddress;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'image',
        'gender',
        'address',
    ];
    protected $appends = ['image_url'];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at',
        'phone_verified_at',
        'gender',
        'created_at',
        'updated_at',
        'image',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function favorites()
    {
        return $this->hasMany(Favorite::class)->where('is_favorite', true)->with('product');
    }
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable');
    }
    public function deviceTokens()
    {
        return $this->hasMany(DeviceToken::class);
    }
    public function getDeviceTokens()
    {
        return $this->deviceTokens()->pluck('token')->toArray();
    }

    public function routeNotificationForFcm()
    {
        return $this->getDeviceTokens();
    }

    // attributes
    public function getImageUrlAttribute()
    {
        return $this->image ? asset($this->image) : null;
    }
    public function getAddressAttribute($value)
    {
        return $value ? CustomAddress::fromJson($value) : null;
    }
    public function setAddressAttribute($value)
    {
        if ($value instanceof CustomAddress) {
            $this->attributes['address'] = $this->attributes['address'] = json_encode($value->toArray());

        } elseif (is_array($value)) {
            $this->attributes['address'] = json_encode($value);
        }
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
