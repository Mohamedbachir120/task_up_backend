<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class ScheduledAlert extends Model
{
    use HasFactory;

    protected $fillable = ["destination","send_time","task_id"];

    public function task(){
        return $this->belongsTo(Task::class);
    }
}
