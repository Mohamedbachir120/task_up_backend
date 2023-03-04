<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Departement;
use App\Models\Task;
class Project extends Model
{
    use HasFactory;
    protected $fillable = ["name","description","departement_id"];

    public function departement(){

        return $this->belongsTo(Departement::class);

    }
    public function tasks(){
        return $this->hasMany(Task::class,'project_id');

    }

}
