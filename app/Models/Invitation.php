<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Departement;
use App\Models\Collaboration;
class Invitation extends Model
{
    use HasFactory;
    protected $fillable = ["status","collaboration_id","departement_id"];
    public function departement(){
        return $this->belongsTo(Departement::class);

    }
    public function collaboration(){
        return $this->belongsTo(Collaboration::class);
        
    }
}
