<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horaire extends Model
{
    use HasFactory;

    protected $table = 'horaires';

    protected $fillable = [
        'organisation_id',
        'heure_arrivee',
        'heure_depart',
        'heure_pause_debut',
        'heure_pause_fin',
        'jours_travail',
    ];

    protected $casts = [
        'jours_travail' => 'array',
        'heure_arrivee' => 'datetime:H:i',
        'heure_depart' => 'datetime:H:i',
        'heure_pause_debut' => 'datetime:H:i',
        'heure_pause_fin' => 'datetime:H:i',
    ];

    /* =====================
       RELATIONS
    ====================== */

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function personnels()
    {
        return $this->belongsToMany(Personnel::class, 'personnel_horaire')
                    ->withPivot('date_debut', 'date_fin')
                    ->withTimestamps();
    }
}
