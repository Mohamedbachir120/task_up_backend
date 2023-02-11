<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Departement;

class Direction extends Model
{
    use HasFactory;
    protected $fillable = ["name"];
    public function users()
    {
        return $this->morphMany(User::class, 'structurable');
    }
    public function departements()
    {
        return $this->hasMany(Departement::class,'direction_id');
    }
}
