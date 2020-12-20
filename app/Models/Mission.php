<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mission extends Model
{
    use HasFactory;

    public function team() {
    	return $this->hasOne(Team::class);
    }

    public function soldier() {
    	return $this->belongsToMany(Soldier::class, 'soldier_missions');
    }
}
