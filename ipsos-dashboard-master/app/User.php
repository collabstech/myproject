<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use SoftDeletes;

    const ROLE_ADMIN = 1;
    const ROLE_CLIENT = 2;
    const ROLE_MAIN_DEALER = 3;

    const STATUS_ACTIVE = 1;
    const STATUS_BLOCK = 2;

    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'status',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public static function roleLabel()
    {
        return [
            self::ROLE_ADMIN => 'Administrator',
            self::ROLE_CLIENT => 'Client',
            self::ROLE_MAIN_DEALER => 'Main Dealer'
        ];
    }

    public static function statusLabel()
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_BLOCK => 'Blocked',
        ];
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new Notifications\ResetPasswordNotification($token));
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function userProject()
    {
        return $this->hasMany(UserProject::class);
    }

    public function getCreatedByNameAttribute($value)
    {
        if (!$this->created_by) {
            return null;
        }
        if ($user = User::where('id', $this->created_by)->first()){
            return $user->name;
        }
    }

    public function getUpdatedByNameAttribute($value)
    {
        if (!$this->updated_by) {
            return null;
        }
        if ($user = User::where('id', $this->updated_by)->first()){
            return $user->name;
        }
    }
}
