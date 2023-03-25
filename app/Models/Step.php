<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Collaboration;
use App\Models\Departement;
class Step extends Model
{
    use HasFactory;
    protected $fillable = ['title','description','status','order','due_date','collaboration_id','departement_id','dependance_id'];

    public function departement(){
        return $this->belongsTo(Departement::class);
    }
    public function dependance(){
        return $this->belongsTo(Step::class);
    }
    public function collaboration(){
        
        return $this->belongsTo(Collaboration::class);

    }
    
}
