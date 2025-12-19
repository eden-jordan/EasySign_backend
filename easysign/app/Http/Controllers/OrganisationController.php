<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use App\Models\Horaire;
use Illuminate\Http\Request;

class OrganisationController extends Controller
{
    /**
     * Créer l'organisation (superadmin uniquement)
     */


    /**
     * Afficher l'organisation de l'utilisateur connecté
     */
    public function show()
    {
        return auth()->user()->organisation;
    }

    /**
     * Mise à jour organisation
     */
    public function update(Request $request)
    {
        $organisation = auth()->user()->organisation;

        if (!$organisation) {
            abort(404);
        }

        $validated = $request->validate([
            'nom' => 'required|string',
            'adresse' => 'required|string'
        ]);

        $organisation->update($validated);

        return response()->json(['message' => 'Organisation mise à jour']);
    }

    /**
     * Ajouter un horaire
     */
    public function addHoraire(Request $request)
    {
        $organisation = auth()->user()->organisation;

        if (!$organisation) {
            abort(403);
        }

        $validated = $request->validate([
            'heure_arrivee' => 'required',
            'heure_depart' => 'required',
            'heure_pause_debut' => 'required',
            'heure_pause_fin' => 'required',
            'jours_travail' => 'required|array'
        ]);

        $horaire = Horaire::create([
            'organisation_id' => $organisation->id,
            'heure_arrivee' => $validated['heure_arrivee'],
            'heure_depart' => $validated['heure_depart'],
            'heure_pause_debut' => $validated['heure_pause_debut'],
            'heure_pause_fin' => $validated['heure_pause_fin'],
            'jours_travail' => $validated['jours_travail']
        ]);

        return response()->json([
            'message' => 'Horaire ajouté',
            'horaire' => $horaire
        ], 201);
    }

    /**
     * Liste des horaires
     */
    public function horaires()
    {
        return auth()->user()->organisation->horaires;
    }

    /**
     * Supprimer un horaire
     */
    public function deleteHoraire($id)
    {
        $horaire = Horaire::where('organisation_id', auth()->user()->organisation_id)
                          ->findOrFail($id);

        $horaire->delete();

        return response()->json(['message' => 'Horaire supprimé']);
    }
}
