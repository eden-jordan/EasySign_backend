<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Organisation;
use App\Models\Horaire;

class OrganisationController extends Controller
{
    public function store(Request $request)
    {
        $org = Organisation::create([
            'nom'=>$request->nom,
            'adresse'=>$request->adresse,
            'created_by'=>auth()->id()
        ]);

        return response()->json($org);
    }

    public function addHoraire(Request $request)
    {
        $horaire = Horaire::create([
            'organisation_id'=>$request->organisation_id,
            'heure_arrivee'=>$request->heure_arrivee,
            'heure_depart'=>$request->heure_depart,
            'pause_debut'=>$request->pause_debut,
            'pause_fin'=>$request->pause_fin,
            'jours_travail'=>json_encode($request->jours_travail)
        ]);

        return response()->json($horaire);
    }
}

