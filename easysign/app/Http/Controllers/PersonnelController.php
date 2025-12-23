<?php

namespace App\Http\Controllers;

use App\Models\Personnel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PersonnelController extends Controller
{
    /**
     * Liste du personnel
     */
    public function index()
    {
        return Personnel::where('organisation_id', auth()->user()->organisation_id)
                        ->with(['presences' => function ($q) {
                             $q->whereDate('date', today());
                        }])
                        ->latest()
                        ->get();
    }

    /**
     * Création du personnel
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        if (!in_array($user->role, ['admin','superadmin'])) {
            abort(403);
        }

        $validated = $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'email' => 'nullable|email|unique:personnel',
            'tel' => 'nullable|string|unique:personnel',
        ]);

        // QR Code unique
        do {
            $qr_code = 'ORG' . $user->organisation_id . '-' . Str::upper(Str::random(8));
        } while (Personnel::where('qr_code', $qr_code)->exists());

        $personnel = Personnel::create([
            'organisation_id' => $user->organisation_id,
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'] ?? null,
            'tel' => $validated['tel'] ?? null,
            'qr_code' => $qr_code,
        ]);

        return response()->json([
            'message' => 'Personnel créé avec succès',
            'personnel' => $personnel
        ], 201);
    }

    /**
     * Détails d’un personnel
     */
    public function show($id)
    {
        $personnel = Personnel::where('organisation_id', auth()->user()->organisation_id)
                              ->findOrFail($id);

        return response()->json($personnel);
    }

    /**
     * Mise à jour
     */
    public function update(Request $request, $id)
    {
        $personnel = Personnel::where('organisation_id', auth()->user()->organisation_id)
                              ->findOrFail($id);

        $validated = $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'email' => 'nullable|email|unique:personnel,email,' . $personnel->id,
            'tel' => 'nullable|string|unique:personnel,tel,' . $personnel->id,
        ]);

        $personnel->update($validated);

        return response()->json(['message' => 'Personnel mis à jour', 'personnel' => $personnel]);
    }

    /**
     * Suppression (soft delete recommandé)
     */
    public function destroy($id)
    {
        $personnel = Personnel::where('organisation_id', auth()->user()->organisation_id)
                              ->findOrFail($id);

        $personnel->delete();

        return response()->json(['message' => 'Personnel supprimé']);
    }

    /**
     * Générer le QR Code image (optionnel)
     */
    public function generateQrImage($id)
    {
        $personnel = Personnel::where('organisation_id', auth()->user()->organisation_id)
                              ->findOrFail($id);

        $qr = QrCode::size(300)->generate($personnel->qr_code);

        return response($qr)->header('Content-Type', 'image/svg+xml');
    }
}
