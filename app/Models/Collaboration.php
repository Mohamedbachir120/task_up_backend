<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Invitation;
use App\Models\Step;
use App\Models\User;


class Collaboration extends Model
{
    use HasFactory;
    protected $fillable = ['created_by','topic','description'];

    public function invitations(){
        return $this->hasMany(Invitation::class);
    }
    public function steps(){
        return $this->hasMany(Step::class);
    }
    public function created_by(){
        return $this->belongsTo(User::class,'created_by');
    }
}
