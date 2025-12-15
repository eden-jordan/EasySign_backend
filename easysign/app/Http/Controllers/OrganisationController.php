<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organisation;
use App\Models\Horaire;

class OrganisationController extends Controller
{
    public function store(Request $request)
    {
        try {

        $org = Organisation::create([
            'nom'=>$request->nom,
            'adresse'=>$request->adresse,
            'created_by'=>auth()->id()
        ]);

        return response()->json($org);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Erreur lors de la création de l\'organisation.'], 500);
        }
    }

    public function addHoraire(Request $request)
    {
        try {

        $horaire = Horaire::create([
            'organisation_id'=>$request->organisation_id,
            'heure_arrivee'=>$request->heure_arrivee,
            'heure_depart'=>$request->heure_depart,
            'pause_debut'=>$request->pause_debut,
            'pause_fin'=>$request->pause_fin,
            'jours_travail'=>json_encode($request->jours_travail)
        ]);

        return response()->json($horaire);
         } catch (\Throwable $th) {
            return response()->json(['message' => 'Erreur lors de l\'ajout de l\'horaire.'], 500);
        }
    }
}

