<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Organisation;
use App\Models\Personnel;
use Illuminate\Support\Str;



class UserController extends Controller
{
    public function registerSuperadmin(Request $request)
{
    try {

        $validated = $request->validate([
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'email' => 'required|email|unique:users',
            'tel' => 'nullable|string',
            'password' => 'required|min:6',

            // organisation
            'organisation_nom' => 'required|string',
            'organisation_adresse' => 'required|string',
        ]);

        // Créer le user
        $user = User::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'],
            'tel' => $validated['tel'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'superadmin',
        ]);

        // Créer l'organisation liée
        $organisation = Organisation::create([
            'nom' => $validated['organisation_nom'],
            'adresse' => $validated['organisation_adresse'],
            'user_id' => $user->id, // ID DISPONIBLE ICI
        ]);

        // Lier l'utilisateur à l'organisation
        $user->update([
            'organisation_id' => $organisation->id
       ]);


        return response()->json([
            'message' => 'Compte et organisation créés avec succès',
            'user' => $user,
            'organisation' => $organisation,
        ], 201);

    } catch (\Throwable $th) {
        return response()->json([
            'message' => 'Erreur lors de la création',
            'error' => $th->getMessage()
        ], 500);
    }
}


    // public function verifyEmail(Request $request, $id, $hash)
    // {
    //     try {

    //         $user = User::findOrFail($id);

    //         if (!hash_equals(sha1($user->getEmailForVerification()), $hash)) {
    //             abort(403);
    //         }

    //         $user->markEmailAsVerified();

    //         return "Email vérifié avec succès";
    //     } catch (\Throwable $th) {
    //         return response()->json(['message' => 'Erreur lors de la vérification de l\'email.'], 500);
    //     }
    // }

    public function login(Request $request)
    {
        try {

        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Identifiants invalides'], 401);
        }

        $user = Auth::user();

        return response()->json([
            'token' => $user->createToken('API')->plainTextToken,
            'user' => $user
        ]);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Erreur lors de la connexion.'], 500);
        }
    }

    public function me(Request $request)
    {
         return response()->json([
        'id' => $request->user()->id,
        'nom' => $request->user()->nom,
        'prenom' => $request->user()->prenom,
        'email' => $request->user()->email,
        'tel' => $request->user()->tel,
        'role' => $request->user()->role,
        'organisation_id' => $request->user()->organisation_id,

    ]);

    }


    public function addAdmin(Request $request)
    {
        $user = auth()->user();

        if ($user->role !== 'superadmin') {
            abort(403, 'Action non autorisée');
        }

        $validated = $request->validate([
            'nom' => 'required',
            'prenom' => 'required',
            'email' => 'required|email|unique:users',
            'tel' => 'nullable|string',
            'password' => 'required|min:6'
        ]);

        $admin = User::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'],
            'tel' => $validated['tel'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
            'organisation_id' => $user->organisation_id
        ]);

        do {
            $qr_code = 'ORG' . $user->organisation_id . '-' . Str::upper(Str::random(8));
        } while (Personnel::where('qr_code', $qr_code)->exists());


        $personnel = Personnel::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'],
            'tel' => $validated['tel'] ?? null,
            'qr_code' => $qr_code,
            'organisation_id' => $user->organisation_id
        ]);

        // $admin->sendEmailVerificationNotification();

        return response()->json(['message' => 'Admin créé avec succès et ajouté au personnel', 'admin' => $admin], 201);
    }

    public function listAdmins()
    {
        $user = auth()->user();

        if ($user->role !== 'superadmin') {
            abort(403, 'Action non autorisée');
        }

        $admins = User::where('role', 'admin')
                       ->orWhere('role', 'superadmin')
                      ->where('organisation_id', $user->organisation_id)
                      ->get();

        return response()->json($admins);
    }

    public function updateAdmin(Request $request, $id)
    {
        $user = auth()->user();

        if ($user->role !== 'superadmin') {
            abort(403, 'Action non autorisée');
        }

        $admin = User::where('id', $id)->where('role', 'admin')->firstOrFail();

        $validated = $request->validate([
            'nom' => 'sometimes|required',
            'prenom' => 'sometimes|required',
            'email' => 'sometimes|required|email|unique:users,email,' . $admin->id,
            'tel' => 'nullable|string',
            'password' => 'sometimes|required|min:6'
        ]);

        $admin->update($validated);

        return response()->json(['message' => 'Admin mis à jour avec succès']);
    }

    public function deleteAdmin($id)
{
    $user = auth()->user();

    if ($user->role !== 'superadmin') {
        abort(403, 'Action non autorisée');
    }

    $admin = User::where('id', $id)->where('role', 'admin')->firstOrFail();

    // Vérifier si l'admin est utilisé dans organisations
    $isInOrganisation = Organisation::where('user_id', $admin->id)->exists();
    if ($isInOrganisation) {
        return response()->json([
            'message' => 'Vous ne pouvez pas supprimer cet administrateur car il est le createur de l\'organisation.'
        ], 400);
    }

    $admin->delete();

    return response()->json(['message' => 'Admin supprimé avec succès']);
}


    public function logout(Request $request)
    {
        $user = auth()->user();

        // Révoquer tous les tokens de l'utilisateur
        $user->tokens()->delete();

        return response()->json(['message' => 'Déconnexion réussie']);

    }

}
