<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Project;

class Task extends Model
{
    protected $fillable = ["title","start_date","end_date","status","description"];

    use HasFactory;

    public function project(){
        return $this->belongsTo(Project::class);
    }
    public function users(){
        return $this->belongsToMany(User::class);
    }
    public function dependance(){
        return $this->belongsTo(Task::class,'dependance_id');
    }
}
