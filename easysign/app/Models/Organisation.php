<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organisation extends Model
{
    protected $fillable = ['nom','adresse','user_id'];

    public function superadmin()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admins()
    {
        return $this->hasMany(User::class);
    }

    public function personnels()
    {
        return $this->hasMany(Personnel::class);
    }

    public function horaires()
    {
        return $this->hasMany(Horaire::class);
    }

    public function rapports()
    {
        return $this->hasMany(Rapport::class);
    }
}

