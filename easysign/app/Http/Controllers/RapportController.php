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

        $datesDuMois = collect();
        $start = Carbon::create($annee, $mois, 1);
        $end = $start->copy()->endOfMonth();

        // Calcul journalier pour chaque jour du mois
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $datesDuMois->push(RapportService::calculJournalier($orgId, $date->toDateString()));
        }

        // Somme des totaux
        $totaux = [
            'present' => $datesDuMois->sum(fn($d) => $d['totaux']['present']),
            'absent' => $datesDuMois->sum(fn($d) => $d['totaux']['absent']),
            'retard' => $datesDuMois->sum(fn($d) => $d['totaux']['retard']),
            'retard_pause' => $datesDuMois->sum(fn($d) => $d['totaux']['retard_pause']),
        ];

        return response()->json([
            'periode' => 'mensuel',
            'date_debut' => $start->toDateString(),
            'date_fin' => $end->toDateString(),
            'totaux' => $totaux,
            'personnels' => $datesDuMois->first()['personnels'] ?? [], // liste des personnels
        ]);
    }

    public function annuel(Request $request)
    {
        $annee = $request->annee ?? now()->year;
        $orgId = auth()->user()->organisation_id;

        $datesAnnee = collect();
        $start = Carbon::create($annee, 1, 1);
        $end = Carbon::create($annee, 12, 31);

        // Calcul journalier pour chaque jour de l'année
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $datesAnnee->push(RapportService::calculJournalier($orgId, $date->toDateString()));
        }

        // Somme des totaux
        $totaux = [
            'present' => $datesAnnee->sum(fn($d) => $d['totaux']['present']),
            'absent' => $datesAnnee->sum(fn($d) => $d['totaux']['absent']),
            'retard' => $datesAnnee->sum(fn($d) => $d['totaux']['retard']),
            'retard_pause' => $datesAnnee->sum(fn($d) => $d['totaux']['retard_pause']),
        ];

        return response()->json([
            'periode' => 'annuel',
            'date_debut' => $start->toDateString(),
            'date_fin' => $end->toDateString(),
            'totaux' => $totaux,
            'personnels' => $datesAnnee->first()['personnels'] ?? [], // liste des personnels
        ]);
    }
}
