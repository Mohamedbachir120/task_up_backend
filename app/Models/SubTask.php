<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Task;

class SubTask extends Model
{
    use HasFactory;
    protected $fillable = ["title","start_date","finished_at","end_date","status","description"];

    public  function task(){
        return $this->belongsTo(Task::class);
    }
}
