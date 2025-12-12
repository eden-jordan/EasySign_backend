<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rapport extends Model
{
    protected $fillable = [
        'organisation_id','date','total_present','total_absents',
        'total_retards','total_pause_retards'
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }
}

