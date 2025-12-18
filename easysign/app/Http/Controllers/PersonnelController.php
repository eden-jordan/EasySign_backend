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
            'tel' => 'required',
        ]);

        // QR Code unique
        $qrCode = Str::uuid()->toString();

        $personnel = Personnel::create([
            'organisation_id' => $user->organisation_id,
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'] ?? null,
            'tel' => $validated['tel'],
            'matricule' => 'EMP-' . now()->format('Y') . '-' . rand(1000,9999),
            'qr_code' => $qrCode,
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
            'tel' => 'required'
        ]);

        $personnel->update($validated);

        return response()->json(['message' => 'Personnel mis à jour']);
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
