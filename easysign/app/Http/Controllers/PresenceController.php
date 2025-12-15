<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presence;
use App\Models\Action_emargement;

class PresenceController extends Controller
{
    public function emargementBiometrique(Request $request)
    {
        try {

        $personnelId = $request->personnel_id;
        $now = now();

        $presence = Presence::firstOrCreate(
            ['personnel_id' => $personnelId, 'date' => $now->toDateString()]
        );

        if (!$presence->arrivee) {
            $presence->update(['arrivee' => $now]);
            $type = 'arrivee';

        } elseif (!$presence->pause_debut) {
            $presence->update(['pause_debut' => $now]);
            $type = 'pause_debut';

        } elseif (!$presence->pause_fin) {
            $presence->update(['pause_fin' => $now]);
            $type = 'pause_fin';

        } elseif (!$presence->depart) {
            $presence->update(['depart' => $now]);
            $type = 'depart';

        } else {
            return response()->json(['message'=>'Toutes les actions sont déjà enregistrées']);
        }

        Action_emargement::create([
            'presence_id' => $presence->id,
            'type_action' => $type,
            'timestamp' => $now
        ]);

        return response()->json([
            'message' => "Action enregistrée : $type",
            'presence' => $presence
        ]);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Erreur lors de l\'émargement biométrique.'], 500);
        }
    }
}
