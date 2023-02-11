<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Direction;
use App\Models\Project;

class Departement extends Model
{
    use HasFactory;
    protected $fillable = ["name"];

    public function users()
    {
        return $this->morphMany(User::class, 'structurable');
    }

    public function direction(){
        return $this->belongsTo(Direction::class);

    }
    public function projects(){
        return $this->hasMany(Project::class,'departement_id');
    }

}
