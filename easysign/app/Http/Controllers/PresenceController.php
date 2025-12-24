<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presence;
use App\Models\Personnel;
use App\Models\Action_emargement;
use Carbon\Carbon;

class PresenceController extends Controller
{
    /**
     * Émargement via QR Code
     */
    public function emargement(Request $request)
{
    $user = auth()->user();

    if (!in_array($user->role, ['admin','superadmin'])) {
        return response()->json([
            'message' => 'Accès refusé'
        ], 403);
    }

    $validated = $request->validate([
        'qr_code' => 'required|exists:personnel,qr_code',
    ]);

    $personnel = Personnel::where('qr_code', $validated['qr_code'])
        ->where('organisation_id', $user->organisation_id)
        ->firstOrFail();

    $now = now();
    $today = $now->toDateString();

    $presence = Presence::firstOrCreate(
        [
            'personnel_id' => $personnel->id,
            'date' => $today
        ],
        [
            'statut' => 'Present'
        ]
    );



    if (!$presence->arrivee) {
        $action = 'arrivee';
        $statut = 'Present';

    } elseif (!$presence->pause_debut) {
        $action = 'pause_debut';
        $statut = 'En_pause';

    } elseif (!$presence->pause_fin) {
        $action = 'pause_fin';
        $statut = 'Present';

    } elseif (!$presence->depart) {
        $action = 'depart';
        $statut = 'Termine';
    } else {
        return response()->json([
            'message' => 'Toutes les actions sont déjà enregistrées'
        ], 409);
    }



    $presence->update([
        $action => $now,
        'statut' => $statut
    ]);

    Action_emargement::create([
        'presence_id' => $presence->id,
        'type_action' => $action,
        'timestamp' => $now,
        'scanned_by' => $user->id
    ]);

    return response()->json([
        'message' => 'Émargement enregistré',
        'action' => $action,
        'statut' => $statut,
        'personnel' => $personnel->nom . ' ' . $personnel->prenom,
        'heure' => $now->format('H:i')
    ]);
}


    /**
     * Présences du jour
     */
    public function today()
    {
        return Presence::whereDate('date', today())
            ->whereHas('personnel', function ($q) {
                $q->where('organisation_id', auth()->user()->organisation_id);
            })
            ->with('personnel')
            ->get();
    }

    /**
     * Historique d’un personnel
     */
    public function history($personnelId)
    {
         $presence = Presence::where('personnel_id', $personnelId)
        ->whereDate('date', today())
        ->with('actions') // récupère toutes les actions du jour
        ->first();

    if (!$presence) {
        return response()->json([]);
    }

    return $presence->actions()
        ->orderBy('timestamp', 'asc')
        ->get();
    }

     public function historyAll($personnelId)
    {
         $today = Carbon::today();

    $presences = Presence::where('personnel_id', $personnelId)
        ->whereHas('personnel', function ($q) {
            $q->where('organisation_id', auth()->user()->organisation_id);
        })
        ->with(['actions' => function ($q) {
            $q->orderBy('timestamp', 'asc'); // heures dans l’ordre
        }])
        ->orderBy('date', 'desc') // AUJOURD’HUI D’ABORD, PUIS LES DATES ANTÉRIEURES
        ->get();

    if ($presences->isEmpty()) {
        return response()->json([]);
    }

    $actions = $presences
        ->flatMap(function ($presence) {
            return $presence->actions;
        })
        ->values();

    return response()->json($actions);
}

    public function presentToday($personnelId)
    {
        $presence = Presence::where('personnel_id', $personnelId)
            ->whereDate('date', today())
            ->first();

        return response()->json([
            'present_today' => $presence !== null
        ]);
    }

    public function absentToday($personnelId)
    {
        $presence = Presence::where('personnel_id', $personnelId)
            ->whereDate('date', today())
            ->first();

        return response()->json([
            'absent_today' => $presence === null
        ]);
    }
}


