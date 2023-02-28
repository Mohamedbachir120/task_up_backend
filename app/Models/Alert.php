<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
class Alert extends Model
{
    use HasFactory;
    protected $fillable = ["title","message","seen"];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
