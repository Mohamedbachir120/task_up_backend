<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Objectif extends Model
{
    use HasFactory;

    protected $fillable = ["year","monthly","yearly","user_id"];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
