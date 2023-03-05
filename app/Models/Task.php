<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Project;
use App\Models\SubTask;
use App\Models\ScheduledAlert;

class Task extends Model
{
    protected $fillable = ["title","finished_at","priority","project_id","dependance_id","start_date","end_date","status","description"];

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
    public function sub_tasks(){
        return $this->hasMany(SubTask::class);
    }

    public function scheduled_alerts(){

        return $this->hasMany(ScheduledAlert::class);
    }
}
