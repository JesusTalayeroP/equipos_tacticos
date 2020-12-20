<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{

	protected $hidden = ['created_at', 'updated_at'];

    use HasFactory;

    public function leader() {
    	return $this->hasOne(Soldier::class);

    }

    public function mission() {
    	return $this->belongsTo(Mission::class);
    }
}
