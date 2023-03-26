<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Direction;
use App\Models\Project;
use App\Models\Invitation;
use App\Models\Step;
use App\Models\Collaboration;


class Departement extends Model
{
    use HasFactory;
    protected $fillable = ["name","direction_id"];

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

    public function invitations(){
        return $this->hasMany(Invitation::class);
    }
    public function steps(){
        return $this->hasMany(Step::class);
    }
    public function collaborations(){
        return $this->belongsToMany(Collaboration::class);
    }
}
