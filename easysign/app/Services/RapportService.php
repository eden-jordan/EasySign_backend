<?php

namespace App\Services;

use App\Models\Presence;
use App\Models\Personnel;
use App\Models\Horaire;
use Carbon\Carbon;

class RapportService
{
   public static function calculJournalier($orgId, $date)
{
    $horaire = Horaire::where('organisation_id', $orgId)->firstOrFail();

    $personnels = Personnel::where('organisation_id', $orgId)->get();
    $presences = Presence::whereDate('date', $date)
        ->whereHas('personnel', fn($q) => $q->where('organisation_id', $orgId))
        ->get();

    $totalPresent = $presences->count();
    $totalPersonnel = $personnels->count();
    $totalAbsents = $totalPersonnel - $totalPresent;

    $retards = 0;
    $retardsPause = 0;

    $personnelsData = [];

    foreach ($personnels as $p) {
        $pPresences = $presences->where('personnel_id', $p->id);
        $pPresent = $pPresences->count();
        $pAbsent = 1 - $pPresent; // 1 si absent, 0 si présent
        $pRetard = 0;
        $pRetardPause = 0;

        foreach ($pPresences as $presence) {
            if ($presence->arrivee && Carbon::parse($presence->arrivee)->gt(Carbon::parse($horaire->heure_arrivee))) {
                $pRetard++;
                $retards++;
            }
            if ($presence->pause_fin && Carbon::parse($presence->pause_fin)->gt(Carbon::parse($horaire->pause_fin))) {
                $pRetardPause++;
                $retardsPause++;
            }
        }

        $personnelsData[] = [
            'id' => $p->id,
            'nom' => $p->nom,
            'present' => $pPresent,
            'absent' => $pAbsent,
            'retard' => $pRetard,
            'retard_pause' => $pRetardPause,
        ];
    }

    return [
        'periode' => 'journalier',
        'date_debut' => $date,
        'date_fin' => $date,
        'totaux' => [
            'present' => $totalPresent,
            'absent' => $totalAbsents,
            'retard' => $retards,
            'retard_pause' => $retardsPause,
        ],
        'personnels' => $personnelsData,
    ];
}

}
