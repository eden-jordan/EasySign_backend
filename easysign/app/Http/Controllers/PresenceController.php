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
            abort(403);
        }

        $validated = $request->validate([
            'qr_code' => 'required|exists:personnel,qr_code',
            'action' => 'required|in:arrivee,pause_debut,pause_fin,depart'
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
            ]
        );

        // Empêcher double action
        if ($presence->{$validated['action']}) {
            return response()->json([
                'message' => 'Action déjà enregistrée'
            ], 409);
        }

        // Ordre logique des actions
        $ordre = [
            'arrivee' => null,
            'pause_debut' => 'arrivee',
            'pause_fin' => 'pause_debut',
            'depart' => 'pause_fin'
        ];

        if ($ordre[$validated['action']] &&
            !$presence->{$ordre[$validated['action']]}) {
            return response()->json([
                'message' => 'Action non autorisée (ordre incorrect)'
            ], 422);
        }

        // Enregistrement
        $presence->update([
            $validated['action'] => $now
        ]);

        Action_emargement::create([
            'presence_id' => $presence->id,
            'type_action' => $validated['action'],
            'timestamp' => $now,
            'scanned_by' => $user->id
        ]);

        return response()->json([
            'message' => 'Émargement enregistré',
            'action' => $validated['action'],
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
        return Presence::where('personnel_id', $personnelId)
            ->with('actions')
            ->orderBy('date','desc')
            ->get();
    }
}
