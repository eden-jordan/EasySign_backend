<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Personnel extends Model
{
    protected $table = 'personnel';

    protected $fillable = [
        'organisation_id','nom','prenom','email','tel','matricule',
        'qr_code'
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function presences()
    {
        return $this->hasMany(Presence::class);
    }
}

