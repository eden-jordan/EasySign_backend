<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;


class UserController extends Controller
{
    public function registerSuperadmin(Request $request)
    {
        try {

        $validated = $request->validate([
            'nom' => 'required',
            'prenom' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        $user = User::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'superadmin'
        ]);

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Compte créé. Vérifiez votre email.']);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Erreur lors de la création du compte.'], 500);
        }
    }

    public function verifyEmail(Request $request, $id, $hash)
    {
        try {

        $user = User::findOrFail($id);

        if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            abort(403);
        }

        $user->markEmailAsVerified();

        return "Email vérifié avec succès";
         } catch (\Throwable $th) {
            return response()->json(['message' => 'Erreur lors de la vérification de l\'email.'], 500);
        }
    }

    public function login(Request $request)
    {
        try {

        if (!Auth::attempt($request->only('email','password'))) {
            return response()->json(['message'=>'Identifiants invalides'], 401);
        }

        $user = Auth::user();

        if (!$user->hasVerifiedEmail()) {
            return response()->json(['message'=>'Veuillez vérifier votre email'], 403);
        }

        return response()->json([
            'token' => $user->createToken("API")->plainTextToken,
            'user' => $user
        ]);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Erreur lors de la connexion.'], 500);
        }
    }

    public function addAdmin(Request $request)
    {
        try {

        $validated = $request->validate([
            'nom'=>'required',
            'prenom'=>'required',
            'email'=>'required|email|unique:users',
            'password'=>'required',
            'organisation_id'=>'required'
        ]);

        $admin = User::create([
            'nom'=>$validated['nom'],
            'prenom'=>$validated['prenom'],
            'email'=>$validated['email'],
            'password'=>Hash::make($validated['password']),
            'role'=>'admin',
            'organisation_id'=>$validated['organisation_id']
        ]);

        $admin->sendEmailVerificationNotification();

        return response()->json(['message'=>'Admin créé avec succès']);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Erreur lors de la création de l\'admin.'], 500);
        }
    }
}
