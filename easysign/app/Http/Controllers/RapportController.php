<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Presence;
use App\Models\Personnel;
use App\Services\RapportService;
use Carbon\Carbon;

class RapportController extends Controller
{
    public function journalier(Request $request)
    {
        $date = $request->date ?? now()->toDateString();
        $orgId = auth()->user()->organisation_id;

        $data = RapportService::calculJournalier($orgId, $date);

        return response()->json($data);
    }

    public function mensuel(Request $request)
    {
        $mois = $request->mois ?? now()->month;
        $annee = $request->annee ?? now()->year;
        $orgId = auth()->user()->organisation_id;

        $start = Carbon::create($annee, $mois, 1);
        $end = $start->copy()->endOfMonth();

        $totaux = [
            'present' => 0,
            'absent' => 0,
            'retard' => 0,
            'retard_pause' => 0,
        ];

        $personnelsAggregate = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $jour = RapportService::calculJournalier($orgId, $date->toDateString());

            // Somme des totaux
            $totaux['present'] += $jour['totaux']['present'];
            $totaux['absent'] += $jour['totaux']['absent'];
            $totaux['retard'] += $jour['totaux']['retard'];
            $totaux['retard_pause'] += $jour['totaux']['retard_pause'];

            // Agrégation des personnels
            foreach ($jour['personnels'] as $p) {
                if (!isset($personnelsAggregate[$p['id']])) {
                    $personnelsAggregate[$p['id']] = $p;
                } else {
                    $personnelsAggregate[$p['id']]['present'] += $p['present'];
                    $personnelsAggregate[$p['id']]['absent'] += $p['absent'];
                    $personnelsAggregate[$p['id']]['retard'] += $p['retard'];
                    $personnelsAggregate[$p['id']]['retard_pause'] += $p['retard_pause'];
                }
            }
        }

        return response()->json([
            'periode' => 'mensuel',
            'date_debut' => $start->toDateString(),
            'date_fin' => $end->toDateString(),
            'totaux' => $totaux,
            'personnels' => array_values($personnelsAggregate),
        ]);
    }

    public function annuel(Request $request)
    {
        $annee = $request->annee ?? now()->year;
        $orgId = auth()->user()->organisation_id;

        $start = Carbon::create($annee, 1, 1);
        $end = Carbon::create($annee, 12, 31);

        $totaux = [
            'present' => 0,
            'absent' => 0,
            'retard' => 0,
            'retard_pause' => 0,
        ];

        $personnelsAggregate = [];

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $jour = RapportService::calculJournalier($orgId, $date->toDateString());

            // Somme des totaux
            $totaux['present'] += $jour['totaux']['present'];
            $totaux['absent'] += $jour['totaux']['absent'];
            $totaux['retard'] += $jour['totaux']['retard'];
            $totaux['retard_pause'] += $jour['totaux']['retard_pause'];

            // Agrégation des personnels
            foreach ($jour['personnels'] as $p) {
                if (!isset($personnelsAggregate[$p['id']])) {
                    $personnelsAggregate[$p['id']] = $p;
                } else {
                    $personnelsAggregate[$p['id']]['present'] += $p['present'];
                    $personnelsAggregate[$p['id']]['absent'] += $p['absent'];
                    $personnelsAggregate[$p['id']]['retard'] += $p['retard'];
                    $personnelsAggregate[$p['id']]['retard_pause'] += $p['retard_pause'];
                }
            }
        }

        return response()->json([
            'periode' => 'annuel',
            'date_debut' => $start->toDateString(),
            'date_fin' => $end->toDateString(),
            'totaux' => $totaux,
            'personnels' => array_values($personnelsAggregate),
        ]);
    }
}
