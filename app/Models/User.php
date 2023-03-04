<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Task; 
use App\Models\Role;
use App\Models\Alert;
use App\Models\Document;

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
        "structurable_type",
        "structurable_id"
    ];
    public function structurable()
    {
        return $this->morphTo();
    }
    public function tasks(){
        return $this->belongsToMany(Task::class);
    }
    public function role(){
        return $this->belongsTo(Role::class);
    }

    public function alerts(){
        return $this->hasMany(Alert::class);
    }
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function documents(){
        return $this->hasMany(Document::class);
    }
}
