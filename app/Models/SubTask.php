<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Task;

class SubTask extends Model
{
    use HasFactory;
    protected $fillable = ["title","status","task_id"];

    public  function task(){
        return $this->belongsTo(Task::class);
    }
}
