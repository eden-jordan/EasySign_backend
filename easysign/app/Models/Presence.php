<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presence extends Model
{
    protected $fillable = [
        'personnel_id','date','arrivee','depart','pause_debut','pause_fin','statut'
    ];

    public function personnel()
    {
        return $this->belongsTo(Personnel::class);
    }

    public function actions()
    {
        return $this->hasMany(Action_emargement::class);
    }
}

