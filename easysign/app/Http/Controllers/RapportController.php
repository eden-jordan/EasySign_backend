<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presence;
use App\Models\Personnel;
use App\Models\Rapport;

class RapportController extends Controller
{
    public function journalier()
    {
        $date = request('date', now()->toDateString());
        $orgId = auth()->user()->organisation_id;

        $presences = Presence::whereDate('date', $date)
                             ->whereHas('personnel', fn($q)=>$q->where('organisation_id',$orgId))
                             ->get();

        $rapport = Rapport::updateOrCreate(
            ['organisation_id'=>$orgId, 'date'=>$date],
            [
                'total_present' => $presences->count(),
                'total_absents' => Personnel::where('organisation_id',$orgId)->count() - $presences->count(),
                'total_retards' => $presences->where('statut','Retard')->count(),
                'total_pause_retards' => $presences->where('statut','Retard retour pause')->count()
            ]
        );

        return response()->json($rapport);
    }
}

